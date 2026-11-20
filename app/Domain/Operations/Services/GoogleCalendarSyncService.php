<?php

declare(strict_types=1);

namespace App\Domain\Operations\Services;

use App\Domain\Operations\Models\CalendarEvent;
use App\Domain\Operations\Models\GoogleCalendarAccount;
use App\Models\Business;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleCalendarSyncService
{
    private const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USERINFO_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';
    private const GOOGLE_CALENDAR_API_BASE = 'https://www.googleapis.com/calendar/v3';

    /**
     * Get Google OAuth2 authorization URL.
     */
    public function getAuthUrl(Business $business, ?string $redirectUri = null): string
    {
        $clientId = (string) config('services.google.client_id', '');
        $redirect = $redirectUri ?? route('operations.calendar.google.callback');

        $scopes = [
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ];

        $state = base64_encode(json_encode([
            'business_id' => $business->id,
            'user_id' => Context::user()?->id,
            'ts' => time(),
        ]));

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirect,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return self::GOOGLE_AUTH_URL . '?' . $params;
    }

    /**
     * Exchange auth code for tokens and save/update the GoogleCalendarAccount.
     */
    public function handleOAuthCallback(string $code, Business $business, ?string $redirectUri = null): GoogleCalendarAccount
    {
        $redirect = $redirectUri ?? route('operations.calendar.google.callback');

        $response = Http::asForm()->post(self::GOOGLE_TOKEN_URL, [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => $redirect,
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful()) {
            Log::error('Google Calendar OAuth token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Gagal menghubungkan Google Calendar: Token exchange failed.');
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'] ?? '';
        $refreshToken = $tokenData['refresh_token'] ?? null;
        $expiresIn = (int) ($tokenData['expires_in'] ?? 3600);
        $expiresAt = now()->addSeconds($expiresIn);

        // Fetch user profile info
        $userResponse = Http::withToken($accessToken)->get(self::GOOGLE_USERINFO_URL);
        $userData = $userResponse->successful() ? $userResponse->json() : [];

        $googleId = (string) ($userData['id'] ?? 'google_' . uniqid());
        $email = (string) ($userData['email'] ?? 'calendar@cooca.id');
        $name = (string) ($userData['name'] ?? 'Google Calendar');
        $avatar = (string) ($userData['picture'] ?? null);

        // Find or create account
        $userId = Context::user()?->id;
        $account = null;
        if ($userId) {
            $account = GoogleCalendarAccount::withoutGlobalScopes()
                ->where('business_id', $business->id)
                ->where('user_id', $userId)
                ->first();
        }

        if (! $account) {
            $account = GoogleCalendarAccount::withoutGlobalScopes()
                ->where('business_id', $business->id)
                ->where('google_id', $googleId)
                ->first();
        }

        if (! $account) {
            $account = new GoogleCalendarAccount();
            $account->id = (string) Str::uuid();
            $account->business_id = $business->id;
            $account->google_id = $googleId;
        }

        $account->user_id = $userId;
        $account->email = $email;
        $account->name = $name;
        $account->avatar_url = $avatar;
        $account->access_token = $accessToken;
        if (! empty($refreshToken)) {
            $account->refresh_token = $refreshToken;
        }
        $account->token_expires_at = $expiresAt;
        $account->is_active = true;
        $account->auto_sync = true;
        $account->last_synced_at = now();
        $account->save();

        return $account;
    }

    /**
     * Refresh expired access token.
     */
    public function refreshAccessToken(GoogleCalendarAccount $account): bool
    {
        $refreshToken = $account->refresh_token;
        if (empty($refreshToken)) {
            Log::warning('No refresh token available for Google Calendar account', ['account_id' => $account->id]);
            return false;
        }

        $response = Http::asForm()->post(self::GOOGLE_TOKEN_URL, [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $account->access_token = $data['access_token'];
            $expiresIn = (int) ($data['expires_in'] ?? 3600);
            $account->token_expires_at = now()->addSeconds($expiresIn);
            if (! empty($data['refresh_token'])) {
                $account->refresh_token = $data['refresh_token'];
            }
            $account->save();
            return true;
        }

        Log::error('Google Calendar token refresh failed', [
            'account_id' => $account->id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return false;
    }

    /**
     * Ensure valid access token.
     */
    public function getValidToken(GoogleCalendarAccount $account): ?string
    {
        if ($account->isTokenExpired()) {
            $refreshed = $this->refreshAccessToken($account);
            if (! $refreshed) {
                return null;
            }
        }

        return $account->access_token;
    }

    /**
     * Push a local CalendarEvent to Google Calendar.
     */
    public function pushEvent(CalendarEvent $event): bool
    {
        $account = $event->googleAccount;
        if (! $account && $event->google_calendar_account_id) {
            $account = GoogleCalendarAccount::withoutGlobalScopes()->find($event->google_calendar_account_id);
        }
        if (! $account && $event->created_by_user_id) {
            $account = GoogleCalendarAccount::withoutGlobalScopes()
                ->where('business_id', $event->business_id)
                ->where('user_id', $event->created_by_user_id)
                ->where('is_active', true)
                ->first();
        }
        if (! $account) {
            $account = GoogleCalendarAccount::withoutGlobalScopes()
                ->where('business_id', $event->business_id)
                ->where('is_active', true)
                ->first();
        }

        if (! $account) {
            return false;
        }

        $token = $this->getValidToken($account);
        if (! $token) {
            $event->sync_status = 'sync_error';
            $event->sync_error_message = 'Token Google Calendar kadaluarsa & gagal di-refresh.';
            $event->saveQuietly();
            return false;
        }

        $calendarId = urlencode($account->calendar_id ?: 'primary');

        // Prepare Google event payload
        $payload = [
            'summary' => $event->title,
            'description' => $event->description ?? '',
            'location' => $event->location ?? '',
        ];

        if ($event->is_all_day) {
            $payload['start'] = ['date' => $event->start_time->format('Y-m-d')];
            $payload['end'] = ['date' => $event->end_time->format('Y-m-d')];
        } else {
            $tz = config('app.timezone', 'Asia/Jakarta');
            $payload['start'] = [
                'dateTime' => $event->start_time->toRfc3339String(),
                'timeZone' => $tz,
            ];
            $payload['end'] = [
                'dateTime' => $event->end_time->toRfc3339String(),
                'timeZone' => $tz,
            ];
        }

        try {
            if ($event->google_event_id) {
                // Update existing Google Event
                $url = self::GOOGLE_CALENDAR_API_BASE . "/calendars/{$calendarId}/events/{$event->google_event_id}";
                $response = Http::withToken($token)->patch($url, $payload);
            } else {
                // Create new Google Event
                $url = self::GOOGLE_CALENDAR_API_BASE . "/calendars/{$calendarId}/events";
                $response = Http::withToken($token)->post($url, $payload);
            }

            if ($response->successful()) {
                $resData = $response->json();
                $event->google_calendar_account_id = $account->id;
                $event->google_event_id = (string) ($resData['id'] ?? $event->google_event_id);
                $event->google_calendar_id = $account->calendar_id;
                $event->google_etag = (string) ($resData['etag'] ?? null);
                $event->google_html_link = (string) ($resData['htmlLink'] ?? null);
                $event->last_synced_at = now();
                $event->sync_status = 'synced';
                $event->sync_error_message = null;
                $event->saveQuietly();
                return true;
            }

            $event->sync_status = 'sync_error';
            $event->sync_error_message = 'Google API error: ' . $response->body();
            $event->saveQuietly();
            return false;
        } catch (\Throwable $e) {
            Log::error('Exception in GoogleCalendar pushEvent', ['error' => $e->getMessage()]);
            $event->sync_status = 'sync_error';
            $event->sync_error_message = $e->getMessage();
            $event->saveQuietly();
            return false;
        }
    }

    /**
     * Delete remote event from Google Calendar.
     */
    public function deleteRemoteEvent(CalendarEvent $event): bool
    {
        if (! $event->google_event_id) {
            return true;
        }

        $account = $event->googleAccount;
        if (! $account && $event->google_calendar_account_id) {
            $account = GoogleCalendarAccount::withoutGlobalScopes()->find($event->google_calendar_account_id);
        }
        if (! $account && $event->created_by_user_id) {
            $account = GoogleCalendarAccount::withoutGlobalScopes()
                ->where('business_id', $event->business_id)
                ->where('user_id', $event->created_by_user_id)
                ->where('is_active', true)
                ->first();
        }
        if (! $account) {
            $account = GoogleCalendarAccount::withoutGlobalScopes()
                ->where('business_id', $event->business_id)
                ->where('is_active', true)
                ->first();
        }

        if (! $account) {
            return false;
        }

        $token = $this->getValidToken($account);
        if (! $token) {
            return false;
        }

        $calendarId = urlencode($account->calendar_id ?: 'primary');
        $url = self::GOOGLE_CALENDAR_API_BASE . "/calendars/{$calendarId}/events/{$event->google_event_id}";

        try {
            $response = Http::withToken($token)->delete($url);
            return $response->successful() || $response->status() === 404;
        } catch (\Throwable $e) {
            Log::error('Exception deleting Google Calendar remote event', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Pull remote events from Google Calendar and sync to local database.
     */
    public function pullEvents(GoogleCalendarAccount $account, ?Carbon $timeMin = null, ?Carbon $timeMax = null): int
    {
        $token = $this->getValidToken($account);
        if (! $token) {
            return 0;
        }

        $calendarId = urlencode($account->calendar_id ?: 'primary');
        $url = self::GOOGLE_CALENDAR_API_BASE . "/calendars/{$calendarId}/events";

        $timeMin = $timeMin ?? now()->subMonths(1);
        $timeMax = $timeMax ?? now()->addMonths(3);

        $params = [
            'timeMin' => $timeMin->toRfc3339String(),
            'timeMax' => $timeMax->toRfc3339String(),
            'singleEvents' => 'true',
            'orderBy' => 'startTime',
            'maxResults' => 250,
        ];

        try {
            $response = Http::withToken($token)->get($url, $params);
            if (! $response->successful()) {
                Log::error('Google Calendar pullEvents failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return 0;
            }

            $items = $response->json('items') ?? [];
            $syncedCount = 0;

            foreach ($items as $item) {
                if (($item['status'] ?? '') === 'cancelled') {
                    CalendarEvent::withoutGlobalScopes()
                        ->where('business_id', $account->business_id)
                        ->where('google_event_id', $item['id'])
                        ->delete();
                    continue;
                }

                $googleEventId = (string) $item['id'];
                $summary = (string) ($item['summary'] ?? '(Tanpa Judul)');
                $description = (string) ($item['description'] ?? null);
                $location = (string) ($item['location'] ?? null);
                $etag = (string) ($item['etag'] ?? null);
                $htmlLink = (string) ($item['htmlLink'] ?? null);

                $isAllDay = isset($item['start']['date']);
                if ($isAllDay) {
                    $start = Carbon::parse($item['start']['date'])->startOfDay();
                    $end = isset($item['end']['date'])
                        ? Carbon::parse($item['end']['date'])->subDay()->endOfDay()
                        : $start->copy()->endOfDay();
                } else {
                    $start = Carbon::parse($item['start']['dateTime'] ?? now());
                    $end = Carbon::parse($item['end']['dateTime'] ?? $start->copy()->addHour());
                }

                // Check loop prevention with etag
                $event = CalendarEvent::withoutGlobalScopes()
                    ->where('business_id', $account->business_id)
                    ->where('google_event_id', $googleEventId)
                    ->first();

                if ($event) {
                    if ($event->google_etag === $etag) {
                        // Unchanged, skip loop
                        continue;
                    }
                } else {
                    $event = new CalendarEvent();
                    $event->id = (string) Str::uuid();
                    $event->business_id = $account->business_id;
                    $event->google_event_id = $googleEventId;
                }

                $event->google_calendar_account_id = $account->id;
                if ($account->user_id) {
                    $event->created_by_user_id = $account->user_id;
                }
                $event->title = $summary;
                $event->description = $description;
                $event->location = $location;
                $event->start_time = $start;
                $event->end_time = $end;
                $event->is_all_day = $isAllDay;
                $event->google_etag = $etag;
                $event->google_html_link = $htmlLink;
                $event->last_synced_at = now();
                $event->sync_status = 'synced';
                $event->saveQuietly();

                $syncedCount++;
            }

            $account->last_synced_at = now();
            $account->save();

            return $syncedCount;
        } catch (\Throwable $e) {
            Log::error('Exception in GoogleCalendar pullEvents', ['error' => $e->getMessage()]);
            return 0;
        }
    }
}

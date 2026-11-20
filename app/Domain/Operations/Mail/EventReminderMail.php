<?php

declare(strict_types=1);

namespace App\Domain\Operations\Mail;

use App\Domain\Operations\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CalendarEvent $event
    ) {}

    public function build(): self
    {
        $subject = 'Pengingat Kegiatan: ' . $this->event->title;
        $icsContent = $this->generateIcs();

        return $this->subject($subject)
            ->html($this->renderHtml())
            ->attachData($icsContent, 'invite.ics', [
                'mime' => 'text/calendar; charset=UTF-8; method=REQUEST',
            ]);
    }

    private function renderHtml(): string
    {
        $startStr = $this->event->start_time->isoFormat('dddd, D MMMM Y HH:mm');
        $endStr = $this->event->end_time->isoFormat('HH:mm');
        $location = $this->event->location ? "<p><strong>Lokasi:</strong> {$this->event->location}</p>" : '';
        $meeting = $this->event->meeting_link ? "<p><strong>Link Meeting:</strong> <a href=\"{$this->event->meeting_link}\">{$this->event->meeting_link}</a></p>" : '';
        $desc = $this->event->description ? "<p style=\"color: #4B5563; margin-top: 12px;\">{$this->event->description}</p>" : '';

        return <<<HTML
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #E5E7EB; border-radius: 12px; background-color: #FFFFFF;">
            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                <span style="font-size: 20px; font-weight: 700; color: #007AFF;">COOCA</span>
                <span style="margin-left: 8px; font-size: 14px; color: #6B7280;">| Pengingat Jadwal Kegiatan</span>
            </div>
            <h2 style="color: #111827; margin: 0 0 16px 0; font-size: 18px;">{$this->event->title}</h2>
            <div style="background-color: #F9FAFB; padding: 16px; border-radius: 8px; font-size: 14px; line-height: 1.6;">
                <p style="margin: 0 0 8px 0;"><strong>Waktu:</strong> {$startStr} - {$endStr}</p>
                {$location}
                {$meeting}
                {$desc}
            </div>
            <p style="margin-top: 24px; font-size: 12px; color: #9CA3AF;">Email ini dikirim otomatis oleh sistem operasional COOCA.</p>
        </div>
HTML;
    }

    private function generateIcs(): string
    {
        $dtStart = $this->event->start_time->utc()->format('Ymd\THis\Z');
        $dtEnd = $this->event->end_time->utc()->format('Ymd\THis\Z');
        $now = now()->utc()->format('Ymd\THis\Z');
        $summary = addcslashes($this->event->title, "\,;");
        $desc = addcslashes($this->event->description ?? '', "\,;");
        $loc = addcslashes($this->event->location ?? '', "\,;");

        return <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//COOCA//Operasional Calendar//ID
METHOD:REQUEST
BEGIN:VEVENT
UID:event-{$this->event->id}@cooca.id
DTSTAMP:{$now}
DTSTART:{$dtStart}
DTEND:{$dtEnd}
SUMMARY:{$summary}
DESCRIPTION:{$desc}
LOCATION:{$loc}
STATUS:CONFIRMED
END:VEVENT
END:VCALENDAR
ICS;
    }
}

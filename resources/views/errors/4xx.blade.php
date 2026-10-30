@php
    $status = (int) ($exception?->getStatusCode() ?? 400);
    $content = match ($status) {
        401 => ['title' => __('errors.401_title'), 'message' => __('errors.401_message'), 'homeLabel' => __('errors.btn_login')],
        403 => ['title' => __('errors.403_title'), 'message' => __('errors.403_message'), 'homeLabel' => __('errors.btn_dashboard')],
        404 => ['title' => __('errors.404_title'), 'message' => __('errors.404_message'), 'homeLabel' => __('errors.btn_home')],
        419 => ['title' => __('errors.419_title'), 'message' => __('errors.419_message'), 'homeLabel' => __('errors.btn_reload')],
        429 => ['title' => __('errors.429_title'), 'message' => __('errors.429_message'), 'homeLabel' => __('errors.btn_retry')],
        default => ['title' => __('errors.400_title'), 'message' => __('errors.400_message'), 'homeLabel' => __('errors.btn_home')],
    };
    $homeUrl = $status === 401 ? route('login') : (Route::has('dashboard') ? route('dashboard') : url('/'));
    if ($status === 419) $homeUrl = url()->current();
    if ($status === 429) $homeUrl = url()->current();
    $reference = substr(sha1(request()->method().'|'.request()->path().'|'.now()->timestamp), 0, 10);
@endphp

@extends('errors.layout', [
    'code' => $status,
    'title' => $content['title'],
    'message' => $content['message'],
    'homeUrl' => $homeUrl,
    'homeLabel' => $content['homeLabel'],
    'reference' => $reference,
])

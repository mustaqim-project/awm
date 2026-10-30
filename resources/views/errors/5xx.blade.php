@php
    $status = (int) ($exception?->getStatusCode() ?? 500);
    $content = match ($status) {
        503 => ['title' => __('errors.503_title'), 'message' => __('errors.503_message'), 'homeLabel' => __('errors.btn_reload')],
        default => ['title' => __('errors.500_title'), 'message' => __('errors.500_message'), 'homeLabel' => __('errors.btn_home')],
    };
    $homeUrl = $status === 503 ? url()->current() : (Route::has('dashboard') ? route('dashboard') : url('/'));
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

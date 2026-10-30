@php
    $status = 403;
    $title = __('errors.403_title');
    $message = __('errors.403_message');
    $homeLabel = Route::has('dashboard') ? __('errors.btn_dashboard') : __('errors.btn_home');
    $homeUrl = Route::has('dashboard') ? route('dashboard') : route('landing');
    $reference = substr(sha1(request()->method().'|'.request()->path().'|'.now()->timestamp), 0, 10);
@endphp

@extends('errors.layout', [
    'code' => $status,
    'title' => $title,
    'message' => $message,
    'homeUrl' => $homeUrl,
    'homeLabel' => $homeLabel,
    'reference' => $reference,
])

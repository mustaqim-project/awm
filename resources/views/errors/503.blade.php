@php
    $status = 503;
    $title = __('errors.503_title');
    $message = __('errors.503_message');
    $homeLabel = __('errors.btn_reload');
    $homeUrl = url()->current();
    $reference = substr(sha1(request()->method() . '|' . request()->path() . '|' . now()->timestamp), 0, 10);
@endphp

@extends('errors.layout', [
    'code' => $status,
    'title' => $title,
    'message' => $message,
    'homeUrl' => $homeUrl,
    'homeLabel' => $homeLabel,
    'reference' => $reference,
])

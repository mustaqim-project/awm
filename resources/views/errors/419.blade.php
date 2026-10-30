@php
    $status = 419;
    $title = __('errors.419_title');
    $message = __('errors.419_message');
    $homeLabel = __('errors.btn_reload');
    $homeUrl = url()->previous() ?: route('landing');
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

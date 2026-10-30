@php
    $status = 404;
    $title = __('errors.404_title');
    $message = __('errors.404_message');
    $homeLabel = __('errors.btn_home');
    $homeUrl = route('landing');
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

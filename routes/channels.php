<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Website Public Live Chat Channel
Broadcast::channel('livechat.{sessionToken}', function ($user = null, $sessionToken = null) {
    return true;
});

// Admin Omnichannel Inbox Channel
Broadcast::channel('admin.inbox', function ($user = null) {
    return true;
});


<?php

namespace App\Http\Controllers;

use App\Models\Notification;

abstract class Controller
{

    // * Create a notification for a user.
    protected function createNotification($userId, $title, $message)
    {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
        ]);
    }
}

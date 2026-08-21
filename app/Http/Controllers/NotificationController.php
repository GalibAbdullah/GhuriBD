<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display the authenticated user's notification inbox.
     */
    public function index(): View
    {
        $notifications = request()->user()
            ->notifications()
            ->latest()
            ->paginate(25);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read and redirect to its target page.
     */
    public function redirectTo(DatabaseNotification $notification): RedirectResponse
    {
        // Users can only interact with their own notifications.
        abort_unless($notification->notifiable_id === request()->user()->getAuthIdentifier(), 404);

        $notification->markAsRead();

        $actionUrl = $notification->data['action_url'] ?? null;

        return $actionUrl
            ? redirect()->to($actionUrl)
            : redirect()->route('notifications.index');
    }

    /**
     * Mark a single notification as read without redirecting.
     */
    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === request()->user()->getAuthIdentifier(), 404);

        $notification->markAsRead();

        return back();
    }

    /**
     * Mark all of the authenticated user's notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        request()->user()->unreadNotifications->each->markAsRead();

        return back();
    }
}
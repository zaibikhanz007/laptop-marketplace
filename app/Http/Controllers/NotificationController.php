<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $record->markAsRead();

        $url = $record->data['url'] ?? route('notifications.index');
        $base = parse_url(url('/'));
        $target = is_string($url) ? parse_url($url) : false;
        $isRelativePath = is_string($url)
            && str_starts_with($url, '/')
            && ! str_starts_with($url, '//')
            && ! str_starts_with($url, '/\\');
        $isSameOrigin = is_array($target)
            && isset($target['host'])
            && strtolower($target['host']) === strtolower($base['host'] ?? '')
            && ($target['scheme'] ?? null) === ($base['scheme'] ?? null)
            && ($target['port'] ?? null) === ($base['port'] ?? null);

        if (! $isRelativePath && ! $isSameOrigin) {
            $url = route('notifications.index');
        }

        return redirect()->to($url);
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return redirect()->route('notifications.index')->with('success', 'All notifications marked as read.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Semua notifikasi, yang belum dibaca ditampilkan paling atas.
     */
    public function index(): View
    {
        $notifications = AdminNotification::orderByRaw('read_at IS NOT NULL, created_at DESC')->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca dan redirect ke link tujuan.
     */
    public function markRead(Request $request, AdminNotification $notification): RedirectResponse|JsonResponse
    {
        if ($notification->isUnread()) {
            $notification->update(['read_at' => now()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return $notification->link
            ? redirect($notification->link)
            : back();
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        AdminNotification::whereNull('read_at')->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}

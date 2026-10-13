<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\BranchContext;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    private const LIMIT = 20;

    public function index(): JsonResponse
    {
        $branch = BranchContext::current();

        if (! $branch) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        NotificationService::sync($branch);

        $unreadCount = AppNotification::query()->where('branch_id', $branch->id)->whereNull('read_at')->count();

        $notifications = AppNotification::query()
            ->where('branch_id', $branch->id)
            ->latest()
            ->limit(self::LIMIT)
            ->get();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications->map(fn (AppNotification $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'url' => $n->url,
                'read' => $n->isRead(),
                'created_at' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function markRead(AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->branch_id === BranchContext::id(), 404);

        if (! $notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }

        return redirect($notification->url ?? route('dashboard'));
    }

    public function markAllRead(): JsonResponse
    {
        $branchId = BranchContext::id();

        if ($branchId) {
            AppNotification::query()
                ->where('branch_id', $branchId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json(['status' => 'ok']);
    }
}

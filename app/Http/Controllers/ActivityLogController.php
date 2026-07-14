<?php

namespace App\Http\Controllers;

use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    private const ADMIN_GLOBAL_LAST_SEEN_KEY = 'activity.admin.last_seen.';

    public function recent(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->query('limit', 20), 1), 50);
        $user = $request->user();
        $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('Admin');

        $query = UserActivityLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit($limit);

        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        $items = $query->get(['id', 'user_id', 'action_label', 'method', 'route_name', 'path', 'created_at', 'read_at']);

        return response()->json([
            'items' => $items->map(function (UserActivityLog $log) {
                return [
                    'id' => $log->id,
                    'actor_name' => $log->user?->name,
                    'label' => $log->action_label,
                    'method' => $log->method,
                    'path' => $log->path,
                    'unread' => $log->read_at === null,
                    'created_at' => $log->created_at->toIso8601String(),
                    'created_at_human' => $log->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('Admin');

        if ($isAdmin) {
            $lastSeenAt = Cache::get(self::ADMIN_GLOBAL_LAST_SEEN_KEY.$user->id);

            $count = UserActivityLog::query()
                ->when($lastSeenAt, fn ($q) => $q->where('created_at', '>', $lastSeenAt))
                ->count();
        } else {
            $count = UserActivityLog::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();
        }

        return response()->json([
            'count' => $count,
            'display' => $count > 99 ? '99+' : (string) $count,
        ]);
    }

    public function markSeen(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('Admin');

        if ($isAdmin) {
            Cache::put(self::ADMIN_GLOBAL_LAST_SEEN_KEY.$user->id, now(), now()->addDays(30));
            $updated = 0;
        } else {
            $updated = UserActivityLog::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'ok' => true,
            'marked' => $updated,
        ]);
    }
}

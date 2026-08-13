<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = UserActivityLog::with(['user:id,username,email,first_name,last_name', 'branch:id,name'])
            ->where('tenant_id', auth()->user()->tenant_id);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('modules')) {
            $modules = array_filter(array_map('trim', explode(',', $request->modules)));
            if (!empty($modules)) {
                $query->whereIn('module', $modules);
            }
        }
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('action', 'ilike', "%{$s}%")
                  ->orWhere('reference', 'ilike', "%{$s}%")
                  ->orWhere('module', 'ilike', "%{$s}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderByDesc('created_at')->paginate($request->input('per_page', 50));

        return response()->json(['data' => $logs]);
    }

    public function summary(Request $request): JsonResponse
    {
        $logs = UserActivityLog::where('tenant_id', auth()->user()->tenant_id)
            ->selectRaw("module, COUNT(*) as count, MAX(created_at) as last_access")
            ->groupBy('module')
            ->orderByDesc('count')
            ->get();

        return response()->json(['data' => $logs]);
    }

    public function userActivity(string $userId): JsonResponse
    {
        $logs = UserActivityLog::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json(['data' => $logs]);
    }
}

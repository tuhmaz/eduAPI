<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Models\SecurityLog;
use App\Models\TrustedIp;
use App\Exports\SecurityLogsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SecurityLogController extends Controller
{
    public function index()
    {
        $logs = SecurityLog::with('user')
            ->recent()
            ->paginate(15);

        return view('dashboard.security.logs.index', compact('logs'));
    }

    public function show(SecurityLog $log)
    {
        return view('dashboard.security.logs.show', compact('log'));
    }

    public function resolve(SecurityLog $log, Request $request)
    {
        $request->validate([
            'resolution_notes' => 'required|string|max:1000'
        ]);

        $log->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
            'resolution_notes' => $request->resolution_notes
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة السجل بنجاح'
            ]);
        }

        return redirect()->route('security.logs.index')
            ->with('success', 'تم تحديث حالة السجل بنجاح');
    }

    public function filter(Request $request)
    {
        $request->validate([
            'severity' => 'nullable|in:info,warning,danger',
            'event_type' => 'nullable|in:login_failed,suspicious_activity,blocked_access',
            'ip_address' => 'nullable|ip',
            'unresolved' => 'nullable|boolean'
        ]);

        $query = SecurityLog::with('user');

        if ($request->filled('severity')) {
            $query->bySeverity($request->severity);
        }

        if ($request->filled('event_type')) {
            $query->byEventType($request->event_type);
        }

        if ($request->filled('ip_address')) {
            $query->byIpAddress($request->ip_address);
        }

        if ($request->boolean('unresolved')) {
            $query->unresolved();
        }

        $logs = $query->recent()->paginate(15);

        return view('dashboard.security.logs.index', compact('logs'));
    }

    public function export(Request $request)
    {
        $query = SecurityLog::query();

        // Apply filters if they exist
        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return Excel::download(new SecurityLogsExport($query), 'security_logs.xlsx');
    }

    public function destroy(SecurityLog $log)
    {
        $log->delete();
        return redirect()->route('logs.index.logs')->with('success', __('Security log deleted successfully'));
    }

    /**
     * حذف مجموعة من السجلات المحددة
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'ids' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صالحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ids = explode(',', $request->ids);
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم تحديد أي سجلات للحذف'
                ], 422);
            }

            $logsToDelete = SecurityLog::whereIn('id', $ids)->get();
            if ($logsToDelete->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم العثور على السجلات المحددة'
                ], 404);
            }

            $deleted = SecurityLog::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف السجلات المحددة بنجاح',
                'deleted_count' => $deleted
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in bulk destroy', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف السجلات: ' . $e->getMessage()
            ], 500);
        }
    }

    public function blockIp(SecurityLog $log)
    {
        // Add IP to blocked list
        $blockedIp = new BlockedIp();
        $blockedIp->ip_address = $log->ip_address;
        $blockedIp->reason = 'Blocked from security log #' . $log->id;
        $blockedIp->blocked_at = now();
        $blockedIp->blocked_by = auth()->id();
        $blockedIp->save();

        return redirect()->back()->with('success', 'IP has been blocked successfully.');
    }

    public function markTrusted(SecurityLog $log)
    {
        // Add IP to trusted list
        $trustedIp = new TrustedIp();
        $trustedIp->ip_address = $log->ip_address;
        $trustedIp->reason = 'Marked as trusted from security log #' . $log->id;
        $trustedIp->added_at = now();
        $trustedIp->added_by = auth()->id();
        $trustedIp->save();

        return redirect()->back()->with('success', 'IP has been marked as trusted.');
    }
}

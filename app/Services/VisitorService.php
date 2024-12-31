<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\VisitorTracking;
use Illuminate\Support\Facades\Log;

class VisitorService
{
    public function getVisitorStats()
    {
        try {
            $activeUsers = Cache::get('active_users', []);
            $activeGuests = Cache::get('active_guests', []);
            $pageViews = Cache::get('page_views', 0);

            $stats = [
                'today' => VisitorTracking::whereDate('visited_at', now()->toDateString())->count(),
                'total' => VisitorTracking::count(),
                'users' => count($activeUsers),
                'guests' => count($activeGuests),
                'total_online' => count($activeUsers) + count($activeGuests),
                'page_views' => $pageViews,
                'browsers' => $this->getBrowserStats(),
                'devices' => $this->getDeviceStats(),
            ];

            return $stats;
        } catch (\Exception $e) {
            Log::error('خطأ في جمع إحصائيات الزوار: ' . $e->getMessage());
            return [];
        }
    }

    public function getVisitorLocations()
    {
        try {
            return VisitorTracking::select('country')
                ->whereNotNull('country')
                ->groupBy('country')
                ->selectRaw('count(*) as count, country')
                ->get()
                ->map(function ($item) {
                    return [
                        'country' => $item->country,
                        'count' => $item->count,
                        'percentage' => round(($item->count / VisitorTracking::count()) * 100, 2)
                    ];
                });
        } catch (\Exception $e) {
            Log::error('خطأ في جمع مواقع الزوار: ' . $e->getMessage());
            return [];
        }
    }

    private function getBrowserStats()
    {
        // جمع إحصائيات المتصفحات
        return [];
    }

    private function getDeviceStats()
    {
        // جمع إحصائيات الأجهزة
        return [];
    }
}

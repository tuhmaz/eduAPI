<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function index()
    {
        // Get system performance metrics
        $metrics = [
            'response_time' => $this->getAverageResponseTime(),
            'memory_usage' => $this->getMemoryUsage(),
            'database_stats' => $this->getDatabaseStats(),
            'cache_stats' => $this->getCacheStats()
        ];

        return response()->json($metrics);
    }

    private function getAverageResponseTime()
    {
        // Placeholder for response time calculation
        return [
            'average' => rand(50, 200), // milliseconds
            'peak' => rand(200, 500),
            'minimum' => rand(10, 50)
        ];
    }

    private function getMemoryUsage()
    {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit')
        ];
    }

    private function getDatabaseStats()
    {
        // Get some basic database statistics
        try {
            $stats = [
                'connections' => DB::connection()->select('show status where variable_name = "Threads_connected"')[0]->Value ?? 0,
                'uptime' => DB::connection()->select('show status where variable_name = "Uptime"')[0]->Value ?? 0,
            ];
        } catch (\Exception $e) {
            $stats = [
                'connections' => 0,
                'uptime' => 0,
                'error' => 'Could not retrieve database statistics'
            ];
        }

        return $stats;
    }

    private function getCacheStats()
    {
        // Placeholder for cache statistics
        return [
            'hits' => rand(1000, 5000),
            'misses' => rand(100, 500),
            'size' => rand(1024, 10240) // KB
        ];
    }
}

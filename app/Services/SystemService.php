<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemService
{
    public function getSystemStats()
    {
        try {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = ini_get('memory_limit');

            $stats = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_os' => PHP_OS,
                'memory' => [
                    'usage' => $this->formatBytes($memoryUsage),
                    'limit' => $this->formatBytes($this->convertToBytes($memoryLimit)),
                ],
                'disk' => $this->getDiskUsage(),
            ];

            return $stats;
        } catch (\Exception $e) {
            Log::error('خطأ في جمع إحصائيات النظام: ' . $e->getMessage());
            return [];
        }
    }

    private function getDiskUsage()
    {
        try {
            $diskPath = base_path();
            $total = disk_total_space($diskPath);
            $free = disk_free_space($diskPath);

            return [
                'total' => $this->formatBytes($total),
                'free' => $this->formatBytes($free),
                'used' => $this->formatBytes($total - $free),
            ];
        } catch (\Exception $e) {
            Log::error('خطأ في حساب مساحة القرص: ' . $e->getMessage());
            return [];
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = $bytes ? floor(log($bytes, 1024)) : 0;
        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }

    private function convertToBytes($value)
    {
        $unit = strtolower(substr($value, -1));
        $value = (int)$value;
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        return $value;
    }
}

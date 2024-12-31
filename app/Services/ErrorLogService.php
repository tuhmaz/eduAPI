<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ErrorLogService
{
    public function getRecentErrors()
    {
        try {
            $logPath = storage_path('logs/laravel.log');

            if (!File::exists($logPath)) {
                return [];
            }

            $logs = collect(File::lines($logPath))
                ->filter(fn($line) => str_contains($line, '[ERROR]'))
                ->take(10);

            return $logs->map(function ($line) {
                preg_match('/\[(.*?)\] .*ERROR: (.*?)(\{|$)/', $line, $matches);
                return [
                    'timestamp' => $matches[1] ?? '',
                    'message' => trim($matches[2] ?? ''),
                ];
            });
        } catch (\Exception $e) {
            Log::error('خطأ في قراءة السجلات: ' . $e->getMessage());
            return [];
        }
    }
}

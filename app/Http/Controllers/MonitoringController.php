<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\UserAgentService;
use Illuminate\Support\Facades\File;
use App\Models\VisitorTracking;
use App\Models\DatabaseMetrics;

class MonitoringController extends Controller
{
    protected $userAgent;

    public function __construct(UserAgentService $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function index()
    {
        return view('dashboard.monitoring');
    }

    public function getStats()
    {
        try {
            // تحديث نشاط المستخدم أولاً
            $this->updateUserActivity();

            // جمع الإحصائيات
            $stats = [
                'visitors' => $this->getVisitorStats(),
                'system' => $this->getSystemStats(),
                'locations' => $this->getVisitorLocations(),
                'errors' => $this->getErrorLogs(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('خطأ في الحصول على الإحصائيات: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'حدث خطأ أثناء جلب البيانات',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getVisitorStats()
    {
        try {
            // تنظيف الجلسات القديمة
            $this->cleanOldSessions();

            // الحصول على البيانات من الكاش
            $activeUsers = Cache::get('active_users', []);
            $activeGuests = Cache::get('active_guests', []);
            $pageViews = Cache::get('page_views', 0);

            // تحليل المتصفحات
            $browsers = [];
            foreach (array_merge($activeUsers, $activeGuests) as $session) {
                if (isset($session['user_agent'])) {
                    $browser = $this->userAgent->parse($session['user_agent'])->browser();
                    $browsers[$browser] = ($browsers[$browser] ?? 0) + 1;
                }
            }

            return [
                'users' => count($activeUsers),
                'guests' => count($activeGuests),
                'total' => count($activeUsers) + count($activeGuests),
                'pageViews' => $pageViews,
                'browsers' => $browsers
            ];
        } catch (\Exception $e) {
            Log::error('خطأ في getVisitorStats: ' . $e->getMessage());
            return [
                'users' => 0,
                'guests' => 0,
                'total' => 0,
                'pageViews' => 0,
                'browsers' => []
            ];
        }
    }

    private function getSystemStats()
    {
        try {
            return [
                'cpu_usage' => $this->getCpuUsage(),
                'memory_usage' => $this->getMemoryUsage(),
                'cache_status' => Cache::get('cache_status', 'متصل'),
                'last_update' => now()->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            Log::error('خطأ في getSystemStats: ' . $e->getMessage());
            return [
                'cpu_usage' => 0,
                'memory_usage' => 0,
                'cache_status' => 'غير متصل',
                'last_update' => now()->format('Y-m-d H:i:s')
            ];
        }
    }

    private function getCpuUsage()
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = "wmic cpu get loadpercentage";
                $output = shell_exec($cmd);
                if (preg_match("/\d+/", $output, $matches)) {
                    return (int)$matches[0];
                }
            } else {
                $load = sys_getloadavg();
                return (int)($load[0] * 100 / processor_count());
            }
        } catch (\Exception $e) {
            Log::error('خطأ في getCpuUsage: ' . $e->getMessage());
        }
        return 0;
    }

    private function getMemoryUsage()
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = "wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value";
                $output = shell_exec($cmd);
                
                preg_match("/FreePhysicalMemory=(\d+)/", $output, $free);
                preg_match("/TotalVisibleMemorySize=(\d+)/", $output, $total);
                
                if (isset($free[1]) && isset($total[1])) {
                    $used = $total[1] - $free[1];
                    return round(($used / $total[1]) * 100);
                }
            } else {
                $free = shell_exec('free');
                $free = (string)trim($free);
                $free_arr = explode("\n", $free);
                $mem = explode(" ", $free_arr[1]);
                $mem = array_filter($mem);
                $mem = array_merge($mem);
                return round($mem[2]/$mem[1]*100);
            }
        } catch (\Exception $e) {
            Log::error('خطأ في getMemoryUsage: ' . $e->getMessage());
        }
        return 0;
    }

    private function getVisitorLocations()
    {
        try {
            $activeUsers = Cache::get('active_users', []);
            $activeGuests = Cache::get('active_guests', []);
            $locations = [];

            foreach (array_merge($activeUsers, $activeGuests) as $session) {
                $ip = $session['ip_address'] ?? '127.0.0.1';
                $location = $this->getLocationFromIP($ip);
                $locations[$location] = ($locations[$location] ?? 0) + 1;
            }

            return $locations;
        } catch (\Exception $e) {
            Log::error('خطأ في getVisitorLocations: ' . $e->getMessage());
            return ['غير معروف' => 1];
        }
    }

    private function getLocationFromIP($ip)
    {
        try {
            // التحقق من صحة IP
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return 'غير معروف';
            }
            
            // تجنب الـ IP الداخلية
            if (in_array($ip, ['127.0.0.1', '::1']) || 
                preg_match('/^(192\.168\.|169\.254\.|10\.|172\.(1[6-9]|2\d|3[01]))/', $ip)) {
                return 'شبكة محلية';
            }
            
            // في الإنتاج، استخدم خدمة GeoIP مثل MaxMind
            return 'المملكة العربية السعودية';
        } catch (\Exception $e) {
            Log::error('خطأ في getLocationFromIP: ' . $e->getMessage());
            return 'غير معروف';
        }
    }

    private function updateUserActivity()
    {
        try {
            $sessionId = Session::getId();
            $userId = auth()->id();
            $userAgent = request()->header('User-Agent');
            $ipAddress = request()->ip();

            $activeUsers = Cache::get('active_users', []);
            $activeGuests = Cache::get('active_guests', []);

            $currentTime = now();
            $activity = [
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'last_activity' => $currentTime->timestamp,
            ];

            if ($userId) {
                $activeUsers[$sessionId] = $activity;
                Cache::put('active_users', $activeUsers, now()->addMinutes(5));
            } else {
                $activeGuests[$sessionId] = $activity;
                Cache::put('active_guests', $activeGuests, now()->addMinutes(5));
            }

            // تحديث عداد مشاهدات الصفحة
            $pageViews = Cache::get('page_views', 0);
            Cache::put('page_views', $pageViews + 1, now()->addDay());
        } catch (\Exception $e) {
            Log::error('خطأ في updateUserActivity: ' . $e->getMessage());
        }
    }

    private function cleanOldSessions()
    {
        try {
            $activeUsers = Cache::get('active_users', []);
            $activeGuests = Cache::get('active_guests', []);
            $currentTime = now()->timestamp;
            $timeout = 5 * 60; // 5 minutes in seconds

            foreach ($activeUsers as $sessionId => $data) {
                if (!isset($data['last_activity']) || ($currentTime - $data['last_activity']) > $timeout) {
                    unset($activeUsers[$sessionId]);
                }
            }

            foreach ($activeGuests as $sessionId => $data) {
                if (!isset($data['last_activity']) || ($currentTime - $data['last_activity']) > $timeout) {
                    unset($activeGuests[$sessionId]);
                }
            }

            Cache::put('active_users', $activeUsers, now()->addMinutes(5));
            Cache::put('active_guests', $activeGuests, now()->addMinutes(5));
        } catch (\Exception $e) {
            Log::error('خطأ في cleanOldSessions: ' . $e->getMessage());
        }
    }

    private function getErrorLogs()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            
            if (!File::exists($logPath)) {
                return [
                    'count' => 0,
                    'recent' => [],
                    'error' => 'ملف السجلات غير موجود'
                ];
            }

            // قراءة آخر 100 سطر من ملف السجلات
            $logs = array_filter(file($logPath), function($line) {
                return strpos($line, '.ERROR') !== false;
            });

            // أخذ آخر 10 أخطاء
            $recentErrors = array_slice(array_reverse($logs), 0, 10);
            
            $formattedErrors = [];
            foreach ($recentErrors as $error) {
                // استخراج التاريخ والرسالة
                if (preg_match('/\[(.*?)\].*ERROR: (.*?)(\{|$)/', $error, $matches)) {
                    $formattedErrors[] = [
                        'timestamp' => $matches[1],
                        'message' => trim($matches[2]),
                        'full_message' => $error
                    ];
                }
            }

            return [
                'count' => count($logs),
                'recent' => $formattedErrors,
                'last_updated' => now()->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            Log::error('خطأ في قراءة سجلات الأخطاء: ' . $e->getMessage());
            return [
                'count' => 0,
                'recent' => [],
                'error' => 'حدث خطأ أثناء قراءة السجلات: ' . $e->getMessage()
            ];
        }
    }

    public function clearCache()
    {
        try {
            Cache::flush();
            return response()->json([
                'success' => true, 
                'message' => 'تم تنظيف ذاكرة التخزين المؤقت بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في تنظيف الكاش: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تنظيف ذاكرة التخزين المؤقت'
            ], 500);
        }
    }

    public function dashboard()
    {
        // Get online visitors count (active in last 5 minutes)
        $onlineVisitors = VisitorTracking::where('last_activity', '>=', now()->subMinutes(5))->count();
        
        // Get online registered users
        $onlineUsers = VisitorTracking::where('last_activity', '>=', now()->subMinutes(5))
            ->whereNotNull('user_id')
            ->count();
            
        // Get page distribution from page_visits
        $pageDistribution = DB::table('page_visits')
            ->join('visitors_tracking', 'page_visits.visitor_id', '=', 'visitors_tracking.id')
            ->where('visitors_tracking.last_activity', '>=', now()->subMinutes(5))
            ->select('page_visits.page_url as current_page', DB::raw('count(*) as count'))
            ->groupBy('page_visits.page_url')
            ->get();
            
        // Get country distribution
        $countryDistribution = VisitorTracking::where('last_activity', '>=', now()->subMinutes(5))
            ->whereNotNull('country')
            ->select('country', DB::raw('count(*) as count'))
            ->groupBy('country')
            ->get();
            
        // Get database metrics
        $dbMetrics = $this->getDatabaseMetrics();
        
        return view('monitoring.dashboard', compact(
            'onlineVisitors',
            'onlineUsers',
            'pageDistribution',
            'countryDistribution',
            'dbMetrics'
        ));
    }
    
    private function getDatabaseMetrics()
    {
        try {
            // Get active connections
            $activeConnections = DB::select("SHOW STATUS LIKE 'Threads_connected'")[0]->Value ?? 0;
            
            // Get query execution time metrics
            $queryTime = DB::select("SHOW STATUS LIKE 'Com_select'")[0]->Value ?? 0;
            $updateTime = DB::select("SHOW STATUS LIKE 'Com_update'")[0]->Value ?? 0;
            $insertTime = DB::select("SHOW STATUS LIKE 'Com_insert'")[0]->Value ?? 0;
            $deleteTime = DB::select("SHOW STATUS LIKE 'Com_delete'")[0]->Value ?? 0;
            
            // Total queries executed
            $totalQueries = $queryTime + $updateTime + $insertTime + $deleteTime;
            
            // Get query execution time
            $queryTimeSum = DB::select("SHOW STATUS LIKE 'Handler_read_rnd_next'")[0]->Value ?? 0;
            
            // Calculate average query time (in milliseconds)
            $queryTimeAvg = $totalQueries > 0 ? number_format(($queryTimeSum / $totalQueries) * 1000, 2) : 0;
            
            return [
                'active_connections' => $activeConnections,
                'total_queries' => $totalQueries,
                'query_time_avg' => $queryTimeAvg,
                'details' => [
                    'selects' => $queryTime,
                    'updates' => $updateTime,
                    'inserts' => $insertTime,
                    'deletes' => $deleteTime
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting database metrics: ' . $e->getMessage());
            return [
                'active_connections' => 0,
                'total_queries' => 0,
                'query_time_avg' => 0,
                'details' => [
                    'selects' => 0,
                    'updates' => 0,
                    'inserts' => 0,
                    'deletes' => 0
                ]
            ];
        }
    }
    
    public function getRealtimeData()
    {
        try {
            return response()->json([
                'online_visitors' => VisitorTracking::where('last_activity', '>=', now()->subMinutes(5))->count(),
                'online_users' => VisitorTracking::where('last_activity', '>=', now()->subMinutes(5))
                    ->whereNotNull('user_id')
                    ->count(),
                'db_metrics' => $this->getDatabaseMetrics()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting realtime data: ' . $e->getMessage());
            return response()->json([
                'online_visitors' => 0,
                'online_users' => 0,
                'db_metrics' => [
                    'active_connections' => 0,
                    'total_queries' => 0,
                    'query_time_avg' => 0,
                    'details' => [
                        'selects' => 0,
                        'updates' => 0,
                        'inserts' => 0,
                        'deletes' => 0
                    ]
                ]
            ]);
        }
    }
}

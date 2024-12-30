@php
$pageConfigs = [
    'myLayout' => 'vertical',
    'contentLayout' => 'compact',
    'menuCollapsed' => false,
    'hasCustomizer' => false
];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'لوحة المراقبة')

@section('vendor-style')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .apexcharts-canvas {
            margin: 0 auto;
        }
        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .loading-indicator {
            text-align: center;
            padding: 1rem;
        }
        .stat-card {
            border-radius: 0.5rem;
            border: none;
        }
        .stat-card .card-body {
            padding: 1.5rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #5e72e4;
        }
        .stat-label {
            color: #8898aa;
            font-size: 0.875rem;
            margin-bottom: 0;
        }
        .chart-container {
            height: 350px;
            margin-top: 1rem;
        }
    </style>
@endsection

@section('vendor-script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">لوحة المراقبة</h5>
                <div class="d-flex align-items-center">
                    <span id="last-update" class="text-muted ms-3"></span>
                    <button id="refresh-stats" class="btn btn-primary btn-sm ms-2">
                        <i class="bx bx-refresh me-1"></i>
                        تحديث
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Visitor Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h6 class="stat-label">المستخدمين المتصلين</h6>
                                <div class="stat-value" id="online-users">0</div>
                                <div class="progress progress-sm mt-3">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" id="online-users-progress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h6 class="stat-label">الزوار المتصلين</h6>
                                <div class="stat-value" id="online-visitors">0</div>
                                <div class="progress progress-sm mt-3">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 0%" id="online-visitors-progress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h6 class="stat-label">اتصالات قاعدة البيانات</h6>
                                <div class="stat-value" id="db-connections">0</div>
                                <div class="progress progress-sm mt-3">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="db-connections-progress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h6 class="stat-label">متوسط وقت الاستعلام</h6>
                                <div class="stat-value" id="query-time">0 ms</div>
                                <div class="progress progress-sm mt-3">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" id="query-time-progress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title mb-4">توزيع الصفحات</h6>
                                <div id="pageDistributionChart" class="chart-container"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title mb-4">توزيع الدول</h6>
                                <div id="countryDistributionChart" class="chart-container"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Stats -->
                <div class="row g-4 mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title mb-4">إحصائيات قاعدة البيانات</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="stat-label">عمليات التحديد</div>
                                        <div id="db-selects" class="h4">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-label">عمليات التحديث</div>
                                        <div id="db-updates" class="h4">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-label">عمليات الإدراج</div>
                                        <div id="db-inserts" class="h4">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-label">عمليات الحذف</div>
                                        <div id="db-deletes" class="h4">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    const pageDistChart = new ApexCharts(document.getElementById('pageDistributionChart'), {
        chart: {
            type: 'pie',
            height: 350
        },
        series: [],
        labels: [],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    });

    const countryDistChart = new ApexCharts(document.getElementById('countryDistributionChart'), {
        chart: {
            type: 'pie',
            height: 350
        },
        series: [],
        labels: [],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    });

    pageDistChart.render();
    countryDistChart.render();

    // Update stats function
    function updateStats() {
        fetch('/monitoring/realtime-data')
            .then(response => response.json())
            .then(data => {
                // Update visitor counts
                document.getElementById('online-users').textContent = data.online_users;
                document.getElementById('online-visitors').textContent = data.online_visitors;
                document.getElementById('db-connections').textContent = data.db_metrics.active_connections;
                document.getElementById('query-time').textContent = data.db_metrics.query_time_avg + ' ms';

                // Update progress bars
                const maxUsers = Math.max(data.online_users * 2, 1);
                const maxVisitors = Math.max(data.online_visitors * 2, 1);
                const maxConnections = Math.max(data.db_metrics.active_connections * 2, 1);
                const maxQueryTime = Math.max(parseFloat(data.db_metrics.query_time_avg) * 2, 1);

                document.getElementById('online-users-progress').style.width = (data.online_users / maxUsers * 100) + '%';
                document.getElementById('online-visitors-progress').style.width = (data.online_visitors / maxVisitors * 100) + '%';
                document.getElementById('db-connections-progress').style.width = (data.db_metrics.active_connections / maxConnections * 100) + '%';
                document.getElementById('query-time-progress').style.width = (parseFloat(data.db_metrics.query_time_avg) / maxQueryTime * 100) + '%';

                // Update database metrics
                if (data.db_metrics.details) {
                    document.getElementById('db-selects').textContent = data.db_metrics.details.selects;
                    document.getElementById('db-updates').textContent = data.db_metrics.details.updates;
                    document.getElementById('db-inserts').textContent = data.db_metrics.details.inserts;
                    document.getElementById('db-deletes').textContent = data.db_metrics.details.deletes;
                }

                // Update charts
                if (data.page_distribution) {
                    const pageData = data.page_distribution;
                    pageDistChart.updateOptions({
                        series: pageData.map(item => item.count),
                        labels: pageData.map(item => item.current_page)
                    });
                }

                if (data.country_distribution) {
                    const countryData = data.country_distribution;
                    countryDistChart.updateOptions({
                        series: countryData.map(item => item.count),
                        labels: countryData.map(item => item.country || 'Unknown')
                    });
                }

                // Update last update time
                const now = new Date();
                document.getElementById('last-update').textContent = 'آخر تحديث: ' + 
                    now.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            })
            .catch(error => {
                console.error('Error fetching realtime data:', error);
            });
    }

    // Initial update
    updateStats();

    // Set up auto-refresh
    setInterval(updateStats, 5000);

    // Manual refresh button
    document.getElementById('refresh-stats').addEventListener('click', function() {
        this.disabled = true;
        const icon = this.querySelector('i');
        icon.classList.add('bx-spin');
        
        updateStats();
        
        setTimeout(() => {
            this.disabled = false;
            icon.classList.remove('bx-spin');
        }, 1000);
    });
});
</script>
@endsection

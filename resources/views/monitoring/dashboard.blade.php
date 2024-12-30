@php
$pageConfigs = [
    'myLayout' => 'vertical',
    'contentLayout' => 'compact',
    'menuCollapsed' => false,
    'hasCustomizer' => false
];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'لوحة المراقبة')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('page-script')
<script>
    // Initialize charts with data
    const pageData = @json($pageDistribution);
    const countryData = @json($countryDistribution);

    // Page Distribution Chart
    new Chart(document.getElementById('pageDistributionChart'), {
        type: 'pie',
        data: {
            labels: pageData.map(item => item.current_page),
            datasets: [{
                data: pageData.map(item => item.count),
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                ]
            }]
        }
    });

    // Country Distribution Chart
    new Chart(document.getElementById('countryDistributionChart'), {
        type: 'pie',
        data: {
            labels: countryData.map(item => item.country || 'Unknown'),
            datasets: [{
                data: countryData.map(item => item.count),
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                ]
            }]
        }
    });

    // Real-time updates
    setInterval(() => {
        fetch('/monitoring/realtime-data')
            .then(response => response.json())
            .then(data => {
                // Update visitor counts
                document.getElementById('online-visitors').textContent = data.online_visitors;
                document.getElementById('online-users').textContent = data.online_users;
                
                // Update database metrics
                document.getElementById('db-connections').textContent = data.db_metrics.active_connections;
                document.getElementById('query-time').textContent = data.db_metrics.query_time_avg + ' ms';
                
                // Update detailed database metrics
                if (data.db_metrics.details) {
                    document.getElementById('db-selects').textContent = 'تحديد: ' + data.db_metrics.details.selects;
                    document.getElementById('db-updates').textContent = 'تحديث: ' + data.db_metrics.details.updates;
                    document.getElementById('db-inserts').textContent = 'إدراج: ' + data.db_metrics.details.inserts;
                    document.getElementById('db-deletes').textContent = 'حذف: ' + data.db_metrics.details.deletes;
                }
            })
            .catch(error => console.error('Error fetching realtime data:', error));
    }, 5000);
</script>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Online Visitors Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                الزوار المتواجدون حالياً</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="online-visitors">{{ $onlineVisitors }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Online Users Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                الأعضاء المتواجدون حالياً</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="online-users">{{ $onlineUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Connections Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                إحصائيات قاعدة البيانات</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <div id="db-connections">{{ $dbMetrics['active_connections'] }}</div>
                                <div class="small mt-2">
                                    <div id="db-selects" class="text-muted">تحديد: {{ $dbMetrics['details']['selects'] ?? 0 }}</div>
                                    <div id="db-updates" class="text-muted">تحديث: {{ $dbMetrics['details']['updates'] ?? 0 }}</div>
                                    <div id="db-inserts" class="text-muted">إدراج: {{ $dbMetrics['details']['inserts'] ?? 0 }}</div>
                                    <div id="db-deletes" class="text-muted">حذف: {{ $dbMetrics['details']['deletes'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Query Time Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                متوسط وقت الاستعلام</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="query-time">
                                {{ number_format($dbMetrics['query_time_avg'], 2) }} ms
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Page Distribution Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">توزيع الزوار على الصفحات</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="pageDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Country Distribution Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">توزيع الزوار حسب البلد</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="countryDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

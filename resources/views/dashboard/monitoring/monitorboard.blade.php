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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/monitoring.css'])
@endsection

@section('vendor-script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js" defer></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">لوحة المراقبة</h5>
                <button id="refresh-stats" class="btn btn-primary btn-sm">تحديث</button>
            </div>
            <div class="card-body">
                <!-- إحصائيات الزوار -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <h6>زوار اليوم</h6>
                            <p id="today-visitors" class="stat-value">0</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <h6>زوار الشهر</h6>
                            <p id="month-visitors" class="stat-value">0</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <h6>زوار العام</h6>
                            <p id="year-visitors" class="stat-value">0</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card">
                            <h6>إجمالي الزوار</h6>
                            <p id="total-visitors" class="stat-value">0</p>
                        </div>
                    </div>
                </div>

                <!-- إحصائيات النظام -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-lg-4">
                        <div class="stat-card">
                            <h6>إصدار PHP</h6>
                            <p id="php-version" class="stat-value">-</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="stat-card">
                            <h6>خادم الويب</h6>
                            <p id="web-server" class="stat-value">-</p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="stat-card">
                            <h6>استخدام الذاكرة</h6>
                            <p id="memory-usage" class="stat-value">-</p>
                        </div>
                    </div>
                </div>

                <!-- سجلات الأخطاء -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">سجلات الأخطاء</div>
                            <div class="card-body">
                                <div id="error-logs">
                                    <!-- سيتم ملء الأخطاء هنا -->
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
@vite(['resources/js/monitoring.js'])
@endsection

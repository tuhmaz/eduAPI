@php
$configData = Helper::appClasses();
use Illuminate\Support\Facades\Auth;
@endphp

@extends('layouts.layoutMaster')

@section('title', __('Dashboard'))

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/swiper/swiper-bundle.js') }}"></script>
@endsection

@section('page-style')
<style>
    .dashboard-welcome {
        background: linear-gradient(135deg, var(--bs-primary) 0%, #6610f2 100%);
        border-radius: 1rem;
        overflow: hidden;
        position: relative;
    }

    .welcome-bg {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 40%;
        background: url('{{ asset("assets/img/backgrounds/dashboard-welcome.svg") }}') no-repeat center right;
        background-size: cover;
        opacity: 0.1;
    }

    .stat-card {
        transition: all 0.3s ease;
        border: none;
        background: linear-gradient(145deg, #fff 0%, #f8f9fa 100%);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        font-size: 2.5rem;
        background: linear-gradient(45deg, var(--bs-primary), var(--bs-info));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        opacity: 0.8;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(45deg, var(--bs-primary), var(--bs-info));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .country-card {
        border: none;
        background: linear-gradient(145deg, #fff 0%, #f8f9fa 100%);
        border-radius: 1rem;
        transition: all 0.3s ease;
    }

    .country-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .country-flag {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .latest-item {
        transition: all 0.3s ease;
        border: none;
        margin-bottom: 0.5rem;
        border-radius: 0.5rem;
    }

    .latest-item:hover {
        background-color: var(--bs-light);
        transform: translateX(5px);
    }

    .latest-item:last-child {
        margin-bottom: 0;
    }

    .card-header {
        background: transparent;
        border-bottom: none;
    }

    .progress-bar {
        background: linear-gradient(45deg, var(--bs-primary), var(--bs-info));
    }

    .swiper {
        width: 100%;
        padding: 1rem;
    }

    .swiper-slide {
        background: transparent;
    }

    .growth-badge {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 600;
    }

    .growth-badge.positive {
        background: rgba(40, 199, 111, 0.1);
        color: #28c76f;
    }

    .growth-badge.negative {
        background: rgba(234, 84, 85, 0.1);
        color: #ea5455;
    }

    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .quick-stat-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .quick-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .quick-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #fff;
    }

    .quick-stat-info h4 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .quick-stat-info p {
        margin: 0;
        color: #6c757d;
    }

    .chart-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1.5rem;
        height: 100%;
    }

    .activity-timeline {
        position: relative;
        padding-left: 1.5rem;
    }

    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--bs-light);
    }

    .activity-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .activity-item::before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--bs-primary);
        border: 2px solid #fff;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Welcome Section -->
    <div class="dashboard-welcome mb-4">
        <div class="welcome-bg"></div>
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-12 col-md-8">
                    <div class="d-flex align-items-center mb-3">
                              @php
                          $defaultAvatar = 'assets/img/avatars/1.png';
                           @endphp
                        <div class="avatar avatar-xl me-3">
                        <img src="{{ Auth::user() && Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : asset($defaultAvatar) }}" alt="Avatar" class="rounded-circle">
                        </div>
                        <div>
                            <h2 class="mb-1 text-white">{{ __('Welcome back, :name!', ['name' => Auth::user()->name]) }}</h2>
                            <p class="mb-0 text-white-50">{{ __('Here\'s what\'s happening with your platform today.') }}</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-auto">
                            <a href="{{ route('articles.create') }}" class="btn btn-light">
                                <i class="ri-add-line me-1"></i> {{ __('New Article') }}
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('news.create') }}" class="btn btn-light">
                                <i class="ri-newspaper-line me-1"></i> {{ __('Post News') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="quick-stats">
        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: linear-gradient(45deg, #7367f0, #9e95f5);">
                <i class="ri-user-3-line"></i>
            </div>
            <div class="quick-stat-info">
                <h4>{{ number_format($usersCount) }}</h4>
                <p>{{ __('Total Users') }}</p>
                <div class="growth-badge positive">
                    <i class="ri-arrow-up-line"></i> 12.5%
                </div>
            </div>
        </div>

        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: linear-gradient(45deg, #28c76f, #48da89);">
                <i class="ri-article-line"></i>
            </div>
            <div class="quick-stat-info">
                <h4>{{ number_format($articlesCount) }}</h4>
                <p>{{ __('Total Articles') }}</p>
                <div class="growth-badge positive">
                    <i class="ri-arrow-up-line"></i> 8.2%
                </div>
            </div>
        </div>

        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: linear-gradient(45deg, #ff9f43, #ffb976);">
                <i class="ri-newspaper-line"></i>
            </div>
            <div class="quick-stat-info">
                <h4>{{ number_format($newsCount) }}</h4>
                <p>{{ __('Total News') }}</p>
                <div class="growth-badge positive">
                    <i class="ri-arrow-up-line"></i> 15.7%
                </div>
            </div>
        </div>

        <div class="quick-stat-card">
            <div class="quick-stat-icon" style="background: linear-gradient(45deg, #ea5455, #f08182);">
                <i class="ri-shield-user-line"></i>
            </div>
            <div class="quick-stat-info">
                <h4>{{ number_format($adminsCount) }}</h4>
                <p>{{ __('Total Admins') }}</p>
                <div class="growth-badge positive">
                    <i class="ri-arrow-up-line"></i> 5.3%
                </div>
            </div>
        </div>
    </div>

    <!-- Content Overview -->
    <div class="row g-4 mb-4">
        <!-- Country Statistics -->
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Content by Country') }}</h5>
                    <div class="dropdown">
                        <button class="btn btn-link p-0" data-bs-toggle="dropdown">
                            <i class="ri-more-2-fill"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">{{ __('View Details') }}</a></li>
                            <li><a class="dropdown-item" href="#">{{ __('Download Report') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="swiper country-stats-swiper">
                        <div class="swiper-wrapper">
                            @foreach (['saudi' => 'Saudi Arabia', 'egypt' => 'Egypt', 'palestine' => 'Palestine', 'jordan' => 'Jordan'] as $key => $countryName)
                            <div class="swiper-slide">
                                <div class="country-card p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="{{ asset('assets/img/flags/' . $key . '.svg') }}" class="country-flag me-2" alt="{{ $countryName }}">
                                        <h6 class="mb-0">{{ $countryName }}</h6>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">{{ __('Articles') }}</small>
                                                <h4 class="mb-0">{{ number_format($subdomainArticlesCount[$key]) }}</h4>
                                                <div class="progress mt-2" style="height: 4px;">
                                                    <div class="progress-bar" style="width: 65%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">{{ __('News') }}</small>
                                                <h4 class="mb-0">{{ number_format($subdomainNewsCount[$key]) }}</h4>
                                                <div class="progress mt-2" style="height: 4px;">
                                                    <div class="progress-bar" style="width: 75%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Recent Activity') }}</h5>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        <div class="activity-item">
                            <h6 class="mb-1">{{ __('New Article Published') }}</h6>
                            <p class="mb-0 text-muted small">{{ __('Article "Understanding AI" was published') }}</p>
                            <small class="text-muted">30 min ago</small>
                        </div>
                        <div class="activity-item">
                            <h6 class="mb-1">{{ __('User Registration') }}</h6>
                            <p class="mb-0 text-muted small">{{ __('5 new users registered') }}</p>
                            <small class="text-muted">1 hour ago</small>
                        </div>
                        <div class="activity-item">
                            <h6 class="mb-1">{{ __('System Update') }}</h6>
                            <p class="mb-0 text-muted small">{{ __('System updated to version 2.0') }}</p>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Content -->
    <div class="row g-4">
        <!-- Latest Articles -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-article-line me-2"></i>{{ __('Latest Articles') }}
                    </h5>
                    <button class="btn btn-primary btn-sm">
                        {{ __('View All') }}
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($latestArticles as $article)
                        <div class="latest-item p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('frontend.articles.show', ['database' => config('database.default'), 'article' => $article->id]) }}" class="text-body">
                                            {{ $article->title }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="ri-user-3-line me-1"></i>{{ $article->author->name }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="mb-1">
                                        <span class="badge bg-label-primary">
                                            <i class="ri-eye-line me-1"></i>{{ number_format(rand(100, 1000)) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="ri-time-line me-1"></i>{{ $article->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest News -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-newspaper-line me-2"></i>{{ __('Latest News') }}
                    </h5>
                    <button class="btn btn-primary btn-sm">
                        {{ __('View All') }}
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($latestNews as $news)
                        <div class="latest-item p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('frontend.news.show', ['database' => config('database.default'), 'id' => $news->id]) }}" class="text-body">
                                            {{ $news->title }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="ri-user-3-line me-1"></i>{{ $news->author->name }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="mb-1">
                                        <span class="badge bg-label-primary">
                                            <i class="ri-eye-line me-1"></i>{{ number_format(rand(100, 1000)) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="ri-time-line me-1"></i>{{ $news->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
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
    // Initialize Swiper
    new Swiper('.country-stats-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        },
        breakpoints: {
            640: {
                slidesPerView: 2
            },
            1024: {
                slidesPerView: 3
            }
        },
        autoplay: {
            delay: 3000,
            disableOnInteraction: false
        }
    });

    // Add smooth scrolling and hover effects
    const items = document.querySelectorAll('.latest-item, .quick-stat-card, .country-card');
    items.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = item.classList.contains('latest-item') ? 'translateX(5px)' : 'translateY(-5px)';
        });
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translate(0)';
        });
    });
});
</script>
@endsection

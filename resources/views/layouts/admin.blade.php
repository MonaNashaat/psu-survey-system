<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'لوحة التحكم' }}</title>
    <link rel="icon" type="image/x-icon" href="images/system-logo.png">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body>
    <div class="admin-overlay" id="adminOverlay"></div>

    <div class="admin-app">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-logos">
                    <div class="sidebar-logo-box">
                        <img src="{{ asset('images/university-logo.png') }}" alt="University Logo">
                    </div>

                    <div class="sidebar-logo-box">
                        <img src="{{ asset('images/system-logo.png') }}" alt="System Logo">
                    </div>
                </div>

                <div class="sidebar-brand-text">
                    <h2>{{ config('app.name', 'منصة الاستبيانات') }}</h2>
                    <p>لوحة الإدارة والتحكم</p>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title">الرئيسية</div>

                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.home') ? 'active' : '' }}">
                    <span>لوحة المؤشرات</span>
                </a>

                <a href="{{ route('admin.surveys.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}">
                    <span>الاستبيانات</span>
                </a>
                @unless(auth()->user()->isPresidencyAdmin())
                    <a href="{{ route('admin.templates.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.templates.*') ? 'active' : '' }}">
                        <span>قوالب الاستبيانات</span>
                    </a>
                @endunless
            </div>
            @unless(auth()->user()->isPresidencyAdmin())
            <div class="sidebar-section">
                <div class="sidebar-section-title">الهيكل الأكاديمي</div>

                <a href="{{ route('admin.academic.faculties.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.academic.faculties.*') ? 'active' : '' }}">
                    <span>الكليات</span>
                </a>

                <a href="{{ route('admin.academic.departments.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.academic.departments.*') ? 'active' : '' }}">
                    <span>الأقسام</span>
                </a>

                <a href="{{ route('admin.academic.courses.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.academic.courses.*') ? 'active' : '' }}">
                    <span>المقررات</span>
                </a>

                <a href="{{ route('admin.academic.offerings.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.academic.offerings.*') ? 'active' : '' }}">
                    <span>المواد المسجلة</span>
                </a>
                @if(auth()->user()->isUniversityAdmin())
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">إدارة المستخدمين</a>
                @endif
            </div>
            @endunless
            <div class="sidebar-section sidebar-bottom">
                <div class="sidebar-section-title">الحساب</div>

                @auth
                    <div class="sidebar-user-card">
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-email">{{ auth()->user()->email }}</div>
                    </div>
                @endauth

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-link sidebar-link-button">
                        <span>تسجيل الخروج</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle">☰</button>

                    <div class="topbar-title-wrap">
                        <h1>{{ $pageTitle ?? 'لوحة التحكم' }}</h1>
                        <p>{{ $pageSubtitle ?? 'إدارة النظام ومتابعة البيانات' }}</p>
                    </div>
                </div>

                <div class="topbar-right">
                    @auth
                        <div class="topbar-user-badge">
                            {{ auth()->user()->name }}
                        </div>
                    @endauth
                </div>
            </header>

            <section class="admin-content">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin:0; padding-right:18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('adminOverlay');
        const toggle = document.getElementById('sidebarToggle');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }

        if (toggle) {
            toggle.addEventListener('click', openSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }
    </script>

    @stack('scripts')
</body>
</html>
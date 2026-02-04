<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Trello Analytics</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- CSS Inline (sem dependência do Vite/npm) -->
    <style>
        {!! file_get_contents(resource_path('css/app.css')) !!}
    </style>
</head>

<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <span class="logo-icon">📊</span>
                    <span class="logo-text">Trello Analytics</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}"
                    class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('cards.index') }}"
                    class="nav-item {{ request()->routeIs('cards.*') ? 'active' : '' }}">
                    <span class="nav-icon">📋</span>
                    <span>Cards</span>
                </a>
                <a href="{{ route('analytics') }}"
                    class="nav-item {{ request()->routeIs('analytics') ? 'active' : '' }}">
                    <span class="nav-icon">📈</span>
                    <span>Analytics</span>
                </a>
                <a href="{{ route('import.index') }}"
                    class="nav-item {{ request()->routeIs('import.*') ? 'active' : '' }}">
                    <span class="nav-icon">📁</span>
                    <span>Importar</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <span class="version">v1.0.0 Laravel Edition</span>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="page-header">
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                <div class="header-actions">
                    @yield('header-actions')
                </div>
            </header>

            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>

</html>
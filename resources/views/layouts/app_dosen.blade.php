<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Dosen - Sistem Absensi</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap & Font Awesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Custom CSS --}}
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
</head>

<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar text-white">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <h4>POLMAN BABEL</h4>
                    <button id="toggleSidebar" class="toggle-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
            <ul class="nav nav-pills flex-column mb-auto px-2">
                <li class="nav-item fade-in" style="animation-delay: 0.1s;">
                    <a href="{{ route('dashboard_dosen.dashboard') }}"
                        class="nav-link {{ Request::routeIs('dashboard_dosen.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item fade-in" style="animation-delay: 0.2s;">
                    <a href="{{ route('dashboard_dosen.profil') }}"
                        class="nav-link {{ Request::routeIs('dashboard_dosen.profil') ? 'active' : '' }}">
                        <i class="fas fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>

                <hr class="bg-white opacity-25 my-2">

                <li class="nav-item fade-in" style="animation-delay: 0.3s;">
                    <a href="{{ route('dashboard_dosen.absensi') }}"
                        class="nav-link {{ Request::routeIs('dashboard_dosen.absensi') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span>Data Absensi</span>
                    </a>
                </li>
            </ul>

            <!-- Logout -->
            <div class="mt-auto px-3 pb-4 fade-in" style="animation-delay: 0.4s;">
                <hr class="bg-white opacity-25">
                <a href="#" class="nav-link text-white"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Page Content -->
        <div id="content">
            <div class="header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button id="mobileToggle" class="btn btn-outline-primary me-3 d-lg-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="user-profile">
                        <div class="user-avatar">D</div>
                        <div class="user-info">
                            <h6>Dosen</h6>
                        </div>
                    </div>
                </div>
                <img src="{{ asset('images/logoPolman.png') }}" alt="Logo" class="logo">
            </div>

            <div class="container-fluid py-4">
                @yield('content')
            </div>
        </div>

        <!-- Sidebar overlay for mobile -->
        <div class="sidebar-overlay"></div>
    </div>

    <script>
        // Sidebar toggle logic (sama seperti admin)
        document.getElementById('toggleSidebar').addEventListener('click', function () {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth <= 992) {
                sidebar.classList.toggle('show');
                document.querySelector('.sidebar-overlay').style.display =
                    sidebar.classList.contains('show') ? 'block' : 'none';
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });

        document.getElementById('mobileToggle').addEventListener('click', function () {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
            document.querySelector('.sidebar-overlay').style.display =
                sidebar.classList.contains('show') ? 'block' : 'none';
        });

        document.querySelector('.sidebar-overlay').addEventListener('click', function () {
            document.getElementById('sidebar').classList.remove('show');
            this.style.display = 'none';
        });

        function handleResize() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');

            if (window.innerWidth <= 992) {
                sidebar.classList.remove('collapsed');
                if (!sidebar.classList.contains('show')) {
                    overlay.style.display = 'none';
                }
            } else {
                sidebar.classList.remove('show');
                overlay.style.display = 'none';
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();
    </script>

    @stack('scripts')
</body>

</html>

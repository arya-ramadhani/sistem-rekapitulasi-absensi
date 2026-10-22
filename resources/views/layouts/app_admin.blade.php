<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Sistem Absensi</title>
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
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Pengguna -->
                <li class="nav-item fade-in" style="animation-delay: 0.2s;">
                    <a href="{{ route('admin.pengguna.index') }}"
                        class="nav-link {{ Request::routeIs('admin.pengguna.*') ? 'active' : '' }}">
                        <i class="fas fa-users-cog"></i>
                        <span>Data Pengguna</span>
                    </a>
                </li>

                <hr class="bg-white opacity-25 my-2">

                <li class="nav-item fade-in" style="animation-delay: 0.3s;">
                    <a href="{{ route('admin.absensi.index') }}"
                        class="nav-link {{ Request::routeIs('admin.absensi.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span>Absensi</span>
                    </a>
                </li>

                {{-- <li class="nav-item fade-in" style="animation-delay: 0.4s;">
                    <a href="{{ route('admin.dosen.index') }}"
                        class="nav-link {{ Request::routeIs('admin.dosen.*') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i>
                        <span>Kompensasi</span>
                    </a>
                </li> --}}

                <hr class="bg-white opacity-25 my-2">

                <li class="nav-item fade-in" style="animation-delay: 0.5s;">
                    <a href="{{ route('admin.dosen.index') }}"
                        class="nav-link {{ Request::routeIs('admin.dosen.*') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Data Dosen</span>
                    </a>
                </li>

                <li class="nav-item fade-in" style="animation-delay: 0.6s;">
                    <a href="{{ route('admin.prodi.index') }}"
                        class="nav-link {{ Request::routeIs('admin.prodi.*') ? 'active' : '' }}">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Data Prodi</span>
                    </a>
                </li>

                <li class="nav-item fade-in" style="animation-delay: 0.7s;">
                    <a href="{{ route('admin.matkul.index') }}"
                        class="nav-link {{ Request::routeIs('admin.matkul.*') ? 'active' : '' }}">
                        <i class="fas fa-book-open"></i>
                        <span>Mata Kuliah</span>
                    </a>
                </li>

                <li class="nav-item fade-in" style="animation-delay: 0.8s;">
                    <a href="{{ route('admin.kelas.index') }}"
                        class="nav-link {{ Request::routeIs('admin.kelas.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Data Kelas</span>
                    </a>
                </li>
            </ul>

            <!-- Logout Link -->
            <div class="mt-auto px-3 pb-4 fade-in" style="animation-delay: 0.9s;">
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
                    <!-- Tombol toggle untuk mobile -->
                    <button id="mobileToggle" class="btn btn-outline-primary me-3 d-lg-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="user-profile">
                        <div class="user-avatar">
                            A
                        </div>
                        <div class="user-info">
                            <h6>Administrator</h6>
                        </div>
                    </div>
                </div>
                <img src="{{ asset('images/logoPolman.png') }}" alt="Logo" class="logo">
            </div>

            <div class="container-fluid py-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        // Toggle sidebar untuk semua ukuran layar
document.getElementById('toggleSidebar').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth <= 992) {
        sidebar.classList.toggle('show');
        document.querySelector('.sidebar-overlay').style.display = 
            sidebar.classList.contains('show') ? 'block' : 'none';
    } else {
        sidebar.classList.toggle('collapsed');
    }
    closeAllDropdowns();
});

// Toggle khusus untuk mobile
document.getElementById('mobileToggle').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('show');
    document.querySelector('.sidebar-overlay').style.display = 
        sidebar.classList.contains('show') ? 'block' : 'none';
    closeAllDropdowns();
});

// Overlay click handler
document.querySelector('.sidebar-overlay').addEventListener('click', function() {
    document.getElementById('sidebar').classList.remove('show');
    this.style.display = 'none';
});

// Fungsi handleResize yang diperbarui
function handleResize() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (window.innerWidth <= 992) {
        // Mobile view
        sidebar.classList.remove('collapsed');
        if (!sidebar.classList.contains('show')) {
            overlay.style.display = 'none';
        }
    } else {
        // Desktop view
        sidebar.classList.remove('show');
        overlay.style.display = 'none';
    }
    
    closeAllDropdowns();
}

        // Auto handle sidebar on resize
        function handleResize() {
            const sidebar = document.getElementById('sidebar');

            if (window.innerWidth <= 992) {
                // Mobile view
                sidebar.classList.remove('collapsed');
                sidebar.classList.remove('show');
            } else {
                // Desktop view
                sidebar.classList.remove('show');
            }

            // Close all dropdowns on resize
            closeAllDropdowns();
        }

        // Initial check
        handleResize();

        // Add event listener
        window.addEventListener('resize', handleResize);

        // Add active class to parent dropdown when child is active
        document.querySelectorAll('.dropdown-item.active').forEach(item => {
            item.closest('.dropdown').querySelector('.dropdown-toggle').classList.add('active');
        });

        // Enhanced dropdown handling
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');

            // Function to close other dropdowns except the specified one
            function closeOtherDropdowns(currentDropdown) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    if (menu !== currentDropdown) {
                        menu.style.display = 'none';
                        const toggle = menu.previousElementSibling;
                        if (toggle && toggle.classList.contains('dropdown-toggle')) {
                            toggle.classList.remove('show');
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    }
                });
            }

            // Custom dropdown toggle for collapsed sidebar
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    // For mobile or when sidebar is expanded, let Bootstrap handle it
                    if (window.innerWidth <= 992 || !sidebar.classList.contains('collapsed')) {
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();

                    const dropdownMenu = this.nextElementSibling;
                    const parentItem = this.closest('.dropdown');

                    if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                        const isVisible = dropdownMenu.style.display === 'block';

                        // Close all other dropdowns first
                        closeOtherDropdowns(dropdownMenu);

                        // Toggle current dropdown
                        if (!isVisible) {
                            // Position the dropdown correctly
                            const rect = parentItem.getBoundingClientRect();
                            dropdownMenu.style.top = `${rect.top}px`;
                            dropdownMenu.style.left = `${rect.right + 5}px`;

                            dropdownMenu.style.display = 'block';
                            this.classList.add('show');
                            this.setAttribute('aria-expanded', 'true');
                        } else {
                            dropdownMenu.style.display = 'none';
                            this.classList.remove('show');
                            this.setAttribute('aria-expanded', 'false');
                        }
                    }
                });
            });

            // Handle hover for collapsed sidebar dropdowns on desktop
            if (window.innerWidth > 992) {
                document.querySelectorAll('.nav-item.dropdown').forEach(item => {
                    item.addEventListener('mouseenter', function() {
                        if (sidebar.classList.contains('collapsed')) {
                            const dropdownMenu = this.querySelector('.dropdown-menu');
                            if (dropdownMenu) {
                                // Close all other dropdowns first
                                closeOtherDropdowns(dropdownMenu);

                                // Position and show current dropdown
                                const rect = this.getBoundingClientRect();
                                dropdownMenu.style.top = `${rect.top}px`;
                                dropdownMenu.style.left = `${rect.right + 5}px`;

                                dropdownMenu.style.display = 'block';
                                this.querySelector('.dropdown-toggle').classList.add('show');
                                this.querySelector('.dropdown-toggle').setAttribute('aria-expanded',
                                    'true');
                            }
                        }
                    });

                    item.addEventListener('mouseleave', function(e) {
                        if (sidebar.classList.contains('collapsed')) {
                            // Check if mouse is leaving to outside the sidebar
                            if (!sidebar.contains(e.relatedTarget)) {
                                const dropdownMenu = this.querySelector('.dropdown-menu');
                                if (dropdownMenu) {
                                    dropdownMenu.style.display = 'none';
                                    this.querySelector('.dropdown-toggle').classList.remove('show');
                                    this.querySelector('.dropdown-toggle').setAttribute(
                                        'aria-expanded', 'false');
                                }
                            }
                        }
                    });
                });
            }

            // Close dropdowns when clicking outside (for both collapsed and expanded)
            document.addEventListener('click', function(e) {
                // If clicked outside sidebar, close all dropdowns
                if (!e.target.closest('#sidebar')) {
                    closeAllDropdowns();
                    return;
                }

                // If clicked on a nav-link that's not a dropdown toggle, close all dropdowns
                if (e.target.closest('.nav-link') && !e.target.closest('.dropdown-toggle')) {
                    closeAllDropdowns();
                }
            });
        });

        // Prevent dropdown from closing when clicking inside it
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>

    @stack('scripts')
</body>

</html>

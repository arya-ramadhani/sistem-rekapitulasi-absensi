    @extends('layouts.app_admin')

    @section('content')
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card animate__animated animate__fadeIn">
                    <div class="welcome-content">
                        <h2 class="mb-3">
                            <i class="fas fa-chart-line me-2"></i>
                            Selamat Datang di Sistem Rekapitulasi Absensi
                        </h2>
                        <p class="lead mb-0">Dashboard untuk mengelola data administrasi</p>
                    </div>
                    <div class="welcome-decoration">
                        <div class="decoration-circle circle-1"></div>
                        <div class="decoration-circle circle-2"></div>
                        <div class="decoration-circle circle-3"></div>
                    </div>
                </div>
            </div>
        </div>

        @if (isset($error))
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Statistics Cards - First Row -->
        <div class="row g-4 mb-4">
            <!-- Total Dosen -->
            <div class="col-lg-4 col-md-6">
                <div class="stat-card stat-card-green animate__animated animate__fadeInUp"
                    onclick="window.location.href='{{ route('admin.dosen.index') }}'" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon-container">
                        <div class="stat-icon floating">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number count-up" data-target="{{ $totalDosen ?? 0 }}">0</h3>
                        <p class="stat-label">TOTAL DOSEN</p>
                        <span class="stat-sublabel">DOSEN TERDAFTAR</span>
                    </div>
                    <div class="stat-wave"></div>
                </div>
            </div>

            <!-- Total Prodi -->
            <div class="col-lg-4 col-md-6">
                <div class="stat-card stat-card-red animate__animated animate__fadeInUp"
                    onclick="window.location.href='{{ route('admin.prodi.index') }}'" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon-container">
                        <div class="stat-icon floating">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number count-up" data-target="{{ $totalProdi ?? 0 }}">0</h3>
                        <p class="stat-label">PROGRAM STUDI</p>
                        <span class="stat-sublabel">PRODI AKTIF</span>
                    </div>
                    <div class="stat-wave"></div>
                </div>
            </div>

            <!-- Total Mata Kuliah -->
            <div class="col-lg-4 col-md-6">
                <div class="stat-card stat-card-blue animate__animated animate__fadeInUp"
                    onclick="window.location.href='{{ route('admin.matkul.index') }}'" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-icon-container">
                        <div class="stat-icon floating">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number count-up" data-target="{{ $totalMatkul ?? 0 }}">0</h3>
                        <p class="stat-label">MATA KULIAH</p>
                        <span class="stat-sublabel">MATKUL TERSEDIA</span>
                    </div>
                    <div class="stat-wave"></div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards - Second Row -->
        <div class="row g-4 mb-4">
            <!-- Total Kelas -->
            <div class="col-lg-4 col-md-6">
                <div class="stat-card stat-card-orange animate__animated animate__fadeInUp"
                    onclick="window.location.href='{{ route('admin.kelas.index') }}'" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon-container">
                        <div class="stat-icon floating">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number count-up" data-target="{{ $totalKelas ?? 0 }}">0</h3>
                        <p class="stat-label">TOTAL KELAS</p>
                        <span class="stat-sublabel">KELAS AKTIF</span>
                    </div>
                    <div class="stat-wave"></div>
                </div>
            </div>

            <!-- Total Mahasiswa -->
            <div class="col-lg-4 col-md-6">
                <div class="stat-card stat-card-purple animate__animated animate__fadeInUp" data-aos="fade-up"
                    data-aos-delay="200">
                    <div class="stat-icon-container">
                        <div class="stat-icon floating">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number count-up" data-target="{{ $totalMahasiswa ?? 0 }}">0</h3>
                        <p class="stat-label">MAHASISWA</p>
                        <span class="stat-sublabel">MAHASISWA AKTIF</span>
                    </div>
                    <div class="stat-wave"></div>
                </div>
            </div>

            <!-- Total Absensi -->
            <div class="col-lg-4 col-md-6">
                <div class="stat-card stat-card-indigo animate__animated animate__fadeInUp"
                    onclick="window.location.href='{{ route('admin.absensi.index') }}'" data-aos="fade-up"
                    data-aos-delay="300">
                    <div class="stat-icon-container">
                        <div class="stat-icon floating">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number count-up" data-target="{{ $totalAbsensi ?? 0 }}">0</h3>
                        <p class="stat-label">TOTAL ABSENSI</p>
                        <span class="stat-sublabel">DATA TERSIMPAN</span>
                    </div>
                    <div class="stat-wave"></div>
                </div>
            </div>
        </div>

        <style>
            /* Base Styles */
            :root {
                --green: #2ecc71;
                --red: #e74c3c;
                --blue: #3498db;
                --orange: #f39c12;
                --purple: #9b59b6;
                --indigo: #5d6bc6;
                --dark: #2c3e50;
                --light: #ecf0f1;
            }

            /* Welcome Card */
            .welcome-card {
                background: linear-gradient(135deg, #3498db, #2c3e50);
                color: white;
                border-radius: 15px;
                padding: 35px;
                position: relative;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(52, 152, 219, 0.3);
            }

            .welcome-content {
                position: relative;
                z-index: 2;
            }

            .welcome-decoration {
                position: absolute;
                top: 0;
                right: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
            }

            .decoration-circle {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.1);
            }

            .circle-1 {
                width: 150px;
                height: 150px;
                top: -50px;
                right: -50px;
            }

            .circle-2 {
                width: 100px;
                height: 100px;
                bottom: -30px;
                right: 20px;
            }

            .circle-3 {
                width: 60px;
                height: 60px;
                top: 20px;
                right: 100px;
            }

            /* Stat Cards */
            .stat-card {
                border-radius: 15px;
                padding: 15px;
                color: white;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
                height: 100%;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                display: flex;
                flex-direction: column;
            }

            .stat-card:hover {
                transform: translateY(-5px) !important;
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            }

            .stat-card-green {
                background: linear-gradient(135deg, var(--green), #27ae60);
            }

            .stat-card-red {
                background: linear-gradient(135deg, var(--red), #c0392b);
            }

            .stat-card-blue {
                background: linear-gradient(135deg, var(--blue), #2980b9);
            }

            .stat-card-orange {
                background: linear-gradient(135deg, var(--orange), #d35400);
            }

            .stat-card-purple {
                background: linear-gradient(135deg, var(--purple), #8e44ad);
            }

            .stat-card-indigo {
                background: linear-gradient(135deg, var(--indigo), #4a5bbf);
            }

            .stat-icon-container {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 10px;
            }

            .stat-icon {
                font-size: 3.5rem;
                opacity: 0.8;
                display: inline-block;
                transition: all 0.3s ease;
            }

            .floating {
                animation: floating 3s ease-in-out infinite;
            }

            .stat-content {
                position: relative;
                z-index: 2;
                margin-top: -35px;
                /* Move numbers up */
            }

            .stat-number {
                font-size: 3.5rem;
                /* Increased from 2.8rem */
                font-weight: 800;
                /* More bold */
                margin-bottom: 10px;
                line-height: 1.1;
                /* Better spacing */
                position: relative;
                display: inline-block;
                padding-bottom: 5px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                /* Subtle shadow for depth */
            }

            .stat-number::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 60px;
                height: 3px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 3px;
            }

            .stat-label {
                font-size: 1.1rem;
                font-weight: 600;
                margin-bottom: 5px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .stat-sublabel {
                font-size: 0.85rem;
                opacity: 0.9;
                display: block;
            }

            .stat-wave {
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 20px;
                background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" fill="%23000000"></path><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" fill="%23000000"></path><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="%23000000"></path></svg>');
                background-size: cover;
                background-repeat: no-repeat;
                opacity: 0.1;
            }

            /* Animations */
            @keyframes floating {
                0% {
                    transform: translateY(0px);
                }

                50% {
                    transform: translateY(-10px);
                }

                100% {
                    transform: translateY(0px);
                }
            }

            /* Responsive Adjustments */
            @media (max-width: 768px) {
                .stat-card {
                    padding: 20px;
                }

                .stat-number {
                    font-size: 2.5rem;
                }

                .stat-icon {
                    font-size: 2.8rem;
                }
            }

            @media (max-width: 576px) {
                .stat-icon {
                    font-size: 2.2rem;
                }

                .stat-number {
                    font-size: 2rem;
                }
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
        <script>
            // Initialize AOS (Animate On Scroll)
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true
            });

            // Add click effect for stat cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });

            // Count-up animation for statistics
            function animateCountUp() {
                const counters = document.querySelectorAll('.count-up');
                const speed = 200;

                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    const increment = target / speed;

                    if (count < target) {
                        counter.innerText = Math.ceil(count + increment);
                        setTimeout(animateCountUp, 1);
                    } else {
                        counter.innerText = target;
                    }
                });
            }

            // Start count-up animation when page loads
            window.addEventListener('load', animateCountUp);

            // Auto refresh statistics every 5 minutes
            setInterval(() => {
                window.location.reload();
            }, 300000);
        </script>
    @endsection

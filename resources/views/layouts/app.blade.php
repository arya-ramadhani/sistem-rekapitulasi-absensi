<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sistem Rekapitulasi Absensi - POLMAN BABEL</title>

    <!-- Font & Ikon -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #2e59d9;
            --secondary: #f8f9fc;
            --text: #5a5c69;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0674c2 0%, #0674c2 50%, #0674c2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Background Segitiga */
        .bg-triangles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            opacity: 0.2;
            background-image: url('{{ asset("images/motif_segitiga_blur.png") }}');
            background-size: 500px;
        }

        /* Segitiga Mengambang */
        .floating-triangles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            overflow: hidden;
        }

        .floating-triangles div {
            position: absolute;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.15);
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(0) translateX(0) rotate(0deg); }
            50% { transform: translateY(-20px) translateX(10px) rotate(180deg); }
            100% { transform: translateY(0) translateX(0) rotate(360deg); }
        }
    </style>
</head>

<body>
    <!-- Elemen Background -->
    <div class="bg-triangles"></div>
    <div class="floating-triangles" id="floatingTriangles"></div>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Membuat segitiga mengambang
        function createFloatingTriangles() {
            const container = document.getElementById('floatingTriangles');
            const jumlah = 20;
            
            for (let i = 0; i < jumlah; i++) {
                const segitiga = document.createElement('div');
                segitiga.style.width = `${Math.random() * 15 + 10}px`;
                segitiga.style.height = segitiga.style.width;
                segitiga.style.left = `${Math.random() * 100}%`;
                segitiga.style.top = `${Math.random() * 100}%`;
                segitiga.style.opacity = Math.random() * 0.3 + 0.1;
                segitiga.style.animationDuration = `${Math.random() * 15 + 10}s`;
                segitiga.style.animationDelay = `${Math.random() * 5}s`;
                container.appendChild(segitiga);
            }
        }

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            createFloatingTriangles();
        });
    </script>
</body>
</html>
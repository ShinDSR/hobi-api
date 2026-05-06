<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Hobi API' }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; color: #0d6efd; }
    </style>
</head>
<body>
    <div x-data="authHandler" x-init="checkAuth()">
        <!-- Navbar -->
        <nav x-show="isAuthenticated" class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4" x-cloak>
            <div class="container">
                <a class="navbar-brand" href="#">HobiApp</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" :class="window.location.pathname === '/dashboard' ? 'active fw-bold' : ''" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="window.location.pathname.startsWith('/users') ? 'active fw-bold' : ''" href="{{ route('users.index') }}">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="window.location.pathname.startsWith('/hobbies') ? 'active fw-bold' : ''" href="{{ route('hobbies.index') }}">Your Hobbies</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <span class="navbar-text me-3" x-text="user?.name"></span>
                        <button @click="logout" class="btn btn-outline-danger btn-sm">Logout</button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="container">
            {{ $slot }}
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Set axios base headers
        const token = localStorage.getItem('token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('authHandler', () => ({
                isAuthenticated: false,
                user: null,
                token: localStorage.getItem('token'),

                checkAuth() {
                    if (this.token) {
                        this.isAuthenticated = true;
                        this.user = JSON.parse(localStorage.getItem('user'));
                        if (window.location.pathname === '/login') {
                            window.location.href = '/dashboard';
                        }
                    } else {
                        this.isAuthenticated = false;
                        if (window.location.pathname !== '/login' && window.location.pathname !== '/') {
                            window.location.href = '/login';
                        }
                    }
                },

                logout() {
                    axios.post('/api/auth/logout').finally(() => {
                        localStorage.removeItem('token');
                        localStorage.removeItem('user');
                        window.location.href = '/login';
                    });
                }
            }))
        })
    </script>
</body>
</html>

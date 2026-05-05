<x-app-layout title="Login">
    <div class="row justify-content-center align-items-center min-vh-100" x-data="loginHandler">
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4 fw-bold">Sign In</h2>
                    <form @submit.prevent="submit">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input x-model="form.email" type="email" class="form-control" id="email" required placeholder="name@example.com">
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input x-model="form.password" type="password" class="form-control" id="password" required placeholder="********">
                        </div>

                        <div x-show="error" class="alert alert-danger py-2" x-text="error" x-cloak></div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" :disabled="loading">
                            <span x-show="loading" class="spinner-border spinner-border-sm me-2"></span>
                            Sign In
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('loginHandler', () => ({
                form: { email: '', password: '' },
                loading: false,
                error: '',
                
                formatError(err) {
                    if (typeof err === 'string') return err;
                    if (typeof err === 'object' && err !== null) {
                        return Object.values(err).flat().join('<br>');
                    }
                    return 'An unexpected error occurred.';
                },

                async submit() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const response = await axios.post('/api/auth/login', this.form);
                        localStorage.setItem('token', response.data.data.access_token);
                        localStorage.setItem('user', JSON.stringify(response.data.data.user));
                        window.location.href = '/dashboard';
                    } catch (e) {
                        const msg = e.response?.data?.errors || 'Login failed.';
                        this.error = typeof msg === 'string' ? msg : Object.values(msg).flat()[0];
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Error',
                            html: this.formatError(msg)
                        });
                    } finally {
                        this.loading = false;
                    }
                }
            }))
        })
    </script>
</x-app-layout>

<x-app-layout title="Dashboard">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h3 mb-3 fw-bold">Welcome to HobiApp Dashboard</h1>
            <p class="text-muted">You are logged in as <span class="fw-bold text-primary" x-text="user?.name"></span>.</p>
            
            <div class="row g-4 mt-3">
            <div class="col-md-6" x-show="user?.is_admin" x-cloak>
                <div class="card h-100 border-0 bg-primary bg-opacity-10">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-primary">User Management</h5>
                        <p class="card-text text-secondary">Manage system users, create new accounts, and update profiles.</p>
                        <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">Go to Users &rarr;</a>
                    </div>
                </div>
            </div>
                
                <div class="col-md-6">
                    <div class="card h-100 border-0 bg-success bg-opacity-10">
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-success">Hobby Your Management</h5>
                            <p class="card-text text-secondary">Manage your hobbies and track your interests.</p>
                            <a href="{{ route('hobbies.index') }}" class="btn btn-success btn-sm">Go to Your Hobbies &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

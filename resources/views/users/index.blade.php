<x-app-layout title="User Management">
    <div x-data="userManager" x-init="fetchUsers()">
        <div class="d-md-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 fw-bold">Users</h1>
                <p class="text-muted small">Manage all users and their roles.</p>
            </div>
            <button @click="openModal()" class="btn btn-primary">Add User</button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="user in users" :key="user.id">
                            <tr>
                                <td class="px-4 fw-medium" x-text="user.name"></td>
                                <td x-text="user.email"></td>
                                <td>
                                    <span class="badge" :class="user.is_admin ? 'bg-info text-dark' : 'bg-light text-dark'" x-text="user.is_admin ? 'Admin' : 'User'"></span>
                                </td>
                                <td class="text-end px-4">
                                    <button @click="openHobbyModal(user)" class="btn btn-link btn-sm text-success text-decoration-none">Hobbies</button>
                                    <button @click="openModal(user)" class="btn btn-link btn-sm text-decoration-none">Edit</button>
                                    <button @click="deleteUser(user.id)" class="btn btn-link btn-sm text-danger text-decoration-none">Delete</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <span class="text-muted small">Showing <span x-text="users.length"></span> users</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="page === 1 ? 'disabled' : ''">
                            <button class="page-link" @click="prevPage()">Previous</button>
                        </li>
                        <li class="page-item" :class="page === lastPage ? 'disabled' : ''">
                            <button class="page-link" @click="nextPage()">Next</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- User Modal -->
        <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true" x-ref="userModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" x-text="editingUser ? 'Edit User' : 'Add User'"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="saveUser">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input x-model="form.name" type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input x-model="form.email" type="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input x-model="form.password" type="password" class="form-control" :required="!editingUser" placeholder="********">
                                <div class="form-text text-muted small" x-show="editingUser">Leave blank to keep current password.</div>
                            </div>
                            <div class="mb-3 form-check">
                                <input x-model="form.is_admin" type="checkbox" class="form-check-input" id="checkAdmin">
                                <label class="form-check-label" for="checkAdmin">Administrator Role</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                <span x-show="loading" class="spinner-border spinner-border-sm me-1"></span>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- User Hobby Modal -->
        <div class="modal fade" id="userHobbyModal" tabindex="-1" aria-hidden="true" x-ref="userHobbyModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Manage Hobbies for <span class="text-primary" x-text="selectedUser?.name"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Add Hobby Form -->
                        <form @submit.prevent="addUserHobby" class="mb-4">
                            <div class="input-group">
                                <input x-model="newHobbyName" type="text" class="form-control" placeholder="New hobby name..." required>
                                <button class="btn btn-success" type="submit" :disabled="hobbyLoading">
                                    <span x-show="hobbyLoading" class="spinner-border spinner-border-sm me-1"></span>
                                    Add Hobby
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th class="px-3">Hobby Name</th>
                                        <th class="text-end px-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="hobby in userHobbies" :key="hobby.id">
                                        <tr>
                                            <td class="px-3">
                                                <input x-show="editingHobbyId === hobby.id" x-model="editingHobbyName" type="text" class="form-control form-control-sm" @keyup.enter="updateUserHobby(hobby.id)" @keyup.escape="editingHobbyId = null">
                                                <span x-show="editingHobbyId !== hobby.id" x-text="hobby.name"></span>
                                            </td>
                                            <td class="text-end px-3">
                                                <div x-show="editingHobbyId !== hobby.id">
                                                    <button @click="startEditHobby(hobby)" class="btn btn-link btn-sm text-decoration-none">Edit</button>
                                                    <button @click="deleteUserHobby(hobby.id)" class="btn btn-link btn-sm text-danger text-decoration-none">Delete</button>
                                                </div>
                                                <div x-show="editingHobbyId === hobby.id">
                                                    <button @click="updateUserHobby(hobby.id)" class="btn btn-link btn-sm text-primary text-decoration-none fw-bold">Save</button>
                                                    <button @click="editingHobbyId = null" class="btn btn-link btn-sm text-muted text-decoration-none">Cancel</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="userHobbies.length === 0">
                                        <td colspan="2" class="text-center py-4 text-muted small">No hobbies found for this user.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userManager', () => ({
                users: [], page: 1, lastPage: 1, modal: null,
                editingUser: null, loading: false,
                form: { name: '', email: '', password: '', is_admin: false },

                // User Hobby State
                hobbyModal: null,
                selectedUser: null,
                userHobbies: [],
                newHobbyName: '',
                hobbyLoading: false,
                editingHobbyId: null,
                editingHobbyName: '',

                init() {
                    const user = JSON.parse(localStorage.getItem('user'));
                    if (!user?.is_admin) {
                        window.location.href = '/dashboard';
                        return;
                    }

                    this.modal = new bootstrap.Modal(this.$refs.userModal);
                    this.hobbyModal = new bootstrap.Modal(this.$refs.userHobbyModal);
                },

                formatError(err) {
                    if (typeof err === 'string') return err;
                    if (typeof err === 'object' && err !== null) {
                        return Object.values(err).flat().join('<br>');
                    }
                    return 'An unexpected error occurred.';
                },

                async fetchUsers() {
                    try {
                        const res = await axios.get(`/api/users?page=${this.page}`);
                        this.users = res.data.data;
                        this.lastPage = res.data.meta.last_page;
                    } catch (e) {
                        console.error('Failed to fetch users', e);
                    }
                },
                nextPage() { if (this.page < this.lastPage) { this.page++; this.fetchUsers(); } },
                prevPage() { if (this.page > 1) { this.page--; this.fetchUsers(); } },
                openModal(user = null) {
                    this.editingUser = user;
                    this.form = user ? { name: user.name, email: user.email, is_admin: user.is_admin, password: '' } : { name: '', email: '', password: '', is_admin: false };
                    this.modal.show();
                },
                async saveUser() {
                    this.loading = true;
                    try {
                        const payload = { ...this.form };
                        if (this.editingUser && !payload.password) delete payload.password;

                        if (this.editingUser) {
                            await axios.put(`/api/users/${this.editingUser.id}`, payload);
                            Swal.fire('Success', 'User updated successfully', 'success');
                        } else {
                            await axios.post('/api/users', payload);
                            Swal.fire('Success', 'User created successfully', 'success');
                        }
                        this.modal.hide();
                        this.fetchUsers();
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: this.formatError(e.response?.data?.errors || 'Operation failed')
                        });
                    } finally { this.loading = false; }
                },
                async deleteUser(id) {
                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (result.isConfirmed) {
                        try {
                            await axios.delete(`/api/users/${id}`);
                            Swal.fire('Deleted!', 'User has been deleted.', 'success');
                            this.fetchUsers();
                        } catch (e) {
                            Swal.fire('Error', 'Failed to delete user', 'error');
                        }
                    }
                },

                // User Hobby Methods
                async openHobbyModal(user) {
                    this.selectedUser = user;
                    this.userHobbies = [];
                    this.newHobbyName = '';
                    this.editingHobbyId = null;
                    await this.fetchUserHobbies();
                    this.hobbyModal.show();
                },
                async fetchUserHobbies() {
                    try {
                        const res = await axios.get(`/api/users/${this.selectedUser.id}/hobbies`);
                        this.userHobbies = res.data.data;
                    } catch (e) {
                        console.error('Failed to fetch hobbies', e);
                    }
                },
                async addUserHobby() {
                    this.hobbyLoading = true;
                    try {
                        await axios.post(`/api/users/${this.selectedUser.id}/hobbies`, { name: this.newHobbyName });
                        this.newHobbyName = '';
                        await this.fetchUserHobbies();
                        Swal.fire({ title: 'Added!', text: 'Hobby added', icon: 'success', timer: 1500, showConfirmButton: false });
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: this.formatError(e.response?.data?.errors || 'Failed to add hobby')
                        });
                    } finally { this.hobbyLoading = false; }
                },
                startEditHobby(hobby) {
                    this.editingHobbyId = hobby.id;
                    this.editingHobbyName = hobby.name;
                },
                async updateUserHobby(hobbyId) {
                    try {
                        await axios.put(`/api/users/${this.selectedUser.id}/hobbies/${hobbyId}`, { name: this.editingHobbyName });
                        this.editingHobbyId = null;
                        await this.fetchUserHobbies();
                        Swal.fire({ title: 'Updated!', text: 'Hobby updated', icon: 'success', timer: 1500, showConfirmButton: false });
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: this.formatError(e.response?.data?.errors || 'Failed to update hobby')
                        });
                    }
                },
                async deleteUserHobby(hobbyId) {
                    const result = await Swal.fire({
                        title: 'Delete Hobby?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes'
                    });

                    if (result.isConfirmed) {
                        try {
                            await axios.delete(`/api/users/${this.selectedUser.id}/hobbies/${hobbyId}`);
                            await this.fetchUserHobbies();
                            Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1000, showConfirmButton: false });
                        } catch (e) {
                            Swal.fire('Error', 'Failed to delete hobby', 'error');
                        }
                    }
                }
            }))
        })
    </script>
</x-app-layout>

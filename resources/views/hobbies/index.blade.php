<x-app-layout title="My Hobbies">
    <div x-data="hobbyManager" x-init="fetchHobbies()">
        <div class="d-md-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 fw-bold">My Hobbies</h1>
                <p class="text-muted small">Manage your personal hobbies here.</p>
            </div>
            <button @click="openModal()" class="btn btn-success">Add New Hobby</button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Hobby Name</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="hobby in hobbies" :key="hobby.id">
                            <tr>
                                <td class="px-4 fw-medium" x-text="hobby.name"></td>
                                <td class="text-end px-4">
                                    <button @click="openModal(hobby)" class="btn btn-link btn-sm text-decoration-none">Edit</button>
                                    <button @click="deleteHobby(hobby.id)" class="btn btn-link btn-sm text-danger text-decoration-none">Delete</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="hobbies.length === 0">
                            <td colspan="2" class="text-center py-4 text-muted">You haven't added any hobbies yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center" x-show="lastPage > 1">
                <span class="text-muted small">Showing <span x-text="hobbies.length"></span> items</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="page === 1 ? 'disabled' : ''"><button class="page-link" @click="prevPage()">Prev</button></li>
                        <li class="page-item" :class="page === lastPage ? 'disabled' : ''"><button class="page-link" @click="nextPage()">Next</button></li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="hobbyModal" tabindex="-1" aria-hidden="true" x-ref="hobbyModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" x-text="editingHobby ? 'Edit Hobby' : 'Add Hobby'"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="saveHobby">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Hobby Name</label>
                                <input x-model="form.name" type="text" class="form-control" required placeholder="What is your hobby?">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success" :disabled="loading">
                                <span x-show="loading" class="spinner-border spinner-border-sm me-1"></span>
                                Save Hobby
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('hobbyManager', () => ({
                hobbies: [], page: 1, lastPage: 1, modal: null,
                editingHobby: null, loading: false,
                form: { name: '' },

                init() { 
                    this.modal = new bootstrap.Modal(this.$refs.hobbyModal); 
                },

                formatError(err) {
                    if (typeof err === 'string') return err;
                    if (typeof err === 'object' && err !== null) {
                        return Object.values(err).flat().join('<br>');
                    }
                    return 'An unexpected error occurred.';
                },

                async fetchHobbies() {
                    try {
                        const res = await axios.get(`/api/hobbies?page=${this.page}`);
                        this.hobbies = res.data.data;
                        this.lastPage = res.data.meta.last_page;
                    } catch (e) {
                        console.error('Failed to fetch hobbies', e);
                    }
                },
                nextPage() { if (this.page < this.lastPage) { this.page++; this.fetchHobbies(); } },
                prevPage() { if (this.page > 1) { this.page--; this.fetchHobbies(); } },
                openModal(hobby = null) {
                    this.editingHobby = hobby;
                    this.form = hobby ? { name: hobby.name } : { name: '' };
                    this.modal.show();
                },
                async saveHobby() {
                    this.loading = true;
                    try {
                        const payload = { name: this.form.name };
                        
                        if (this.editingHobby) {
                            await axios.put(`/api/hobbies/${this.editingHobby.id}`, payload);
                            Swal.fire({ title: 'Updated!', text: 'Hobby updated successfully', icon: 'success', timer: 1500, showConfirmButton: false });
                        } else {
                            await axios.post('/api/hobbies', payload);
                            Swal.fire({ title: 'Created!', text: 'Hobby created successfully', icon: 'success', timer: 1500, showConfirmButton: false });
                        }
                        this.modal.hide();
                        this.fetchHobbies();
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: this.formatError(e.response?.data?.errors || 'Operation failed')
                        });
                    } finally { this.loading = false; }
                },
                async deleteHobby(id) {
                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: "This will remove this hobby from your profile.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        confirmButtonColor: '#d33'
                    });

                    if (result.isConfirmed) {
                        try {
                            await axios.delete(`/api/hobbies/${id}`);
                            Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1000, showConfirmButton: false });
                            this.fetchHobbies();
                        } catch (e) {
                            Swal.fire('Error', 'Failed to delete hobby', 'error');
                        }
                    }
                }
            }))
        })
    </script>
</x-app-layout>

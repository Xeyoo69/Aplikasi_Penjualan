<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pengguna')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $role = 'kasir';

    public string $password = '';

    public bool $isActive = true;

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('create', User::class);
        $this->resetForm();
        $this->role = UserRole::Kasir->value;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::query()->findOrFail($id);
        Gate::authorize('update', $user);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->isActive = $user->is_active;
        $this->password = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $user = $this->editingId ? User::query()->findOrFail($this->editingId) : null;

        $user
            ? Gate::authorize('update', $user)
            : Gate::authorize('create', User::class);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => $user
                ? ['nullable', 'string', 'min:8', 'max:100']
                : ['required', 'string', 'min:8', 'max:100'],
            'isActive' => ['boolean'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'role.required' => 'Pilih peran pengguna.',
            'password.required' => 'Kata sandi wajib diisi untuk akun baru.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.max' => 'Kata sandi maksimal 100 karakter.',
        ]);

        if ($user && $user->id === auth()->id()) {
            if ($data['role'] !== UserRole::Admin->value) {
                $this->addError('role', 'Anda tidak dapat menurunkan peran akun sendiri.');

                return;
            }

            if (! $data['isActive']) {
                $this->addError('isActive', 'Anda tidak dapat menonaktifkan akun sendiri.');

                return;
            }
        }

        $payload = [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'role' => $data['role'],
            'is_active' => (bool) $data['isActive'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        if ($user) {
            $user->update($payload);
            $this->dispatch('toast', message: 'Data pengguna berhasil diperbarui.', type: 'success');
        } else {
            User::query()->create($payload);
            $this->dispatch('toast', message: 'Pengguna berhasil ditambahkan.', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $user = User::query()->findOrFail($id);
        Gate::authorize('update', $user);

        if ($user->id === auth()->id()) {
            $this->dispatch('toast', message: 'Akun sendiri tidak dapat dinonaktifkan.', type: 'error');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        $this->dispatch(
            'toast',
            message: $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.',
            type: 'success'
        );
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->role = UserRole::Kasir->value;
        $this->password = '';
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.users.user-index', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }
}

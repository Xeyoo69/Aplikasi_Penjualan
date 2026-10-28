<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pelanggan')]
class CustomerIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Customer::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('create', Customer::class);
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $customer = Customer::query()->findOrFail($id);
        Gate::authorize('update', $customer);

        $this->editingId = $customer->id;
        $this->name = $customer->name;
        $this->phone = (string) $customer->phone;
        $this->address = (string) $customer->address;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $customer = $this->editingId ? Customer::query()->findOrFail($this->editingId) : null;

        $customer
            ? Gate::authorize('update', $customer)
            : Gate::authorize('create', Customer::class);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'name.max' => 'Nama pelanggan maksimal 255 karakter.',
            'phone.max' => 'Telepon maksimal 30 karakter.',
            'address.max' => 'Alamat maksimal 500 karakter.',
        ]);

        $payload = [
            'name' => $data['name'],
            'phone' => $data['phone'] ?: null,
            'address' => $data['address'] ?: null,
        ];

        if ($customer) {
            $customer->update($payload);
            $this->dispatch('toast', message: 'Data pelanggan berhasil diperbarui.', type: 'success');
        } else {
            Customer::query()->create($payload);
            $this->dispatch('toast', message: 'Pelanggan berhasil ditambahkan.', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $customer = Customer::query()->findOrFail($id);
        Gate::authorize('delete', $customer);

        $customer->delete();
        $this->dispatch('toast', message: 'Pelanggan berhasil dihapus.', type: 'success');
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
        $this->phone = '';
        $this->address = '';
        $this->resetValidation();
    }

    public function render()
    {
        $customers = Customer::query()
            ->withCount('sales')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.customers.customer-index', [
            'customers' => $customers,
        ]);
    }
}

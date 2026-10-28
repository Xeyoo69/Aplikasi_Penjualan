<?php

namespace App\Livewire\Sales;

use App\Actions\VoidSaleAction;
use App\Models\Sale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detail Transaksi')]
class SaleShow extends Component
{
    public Sale $sale;

    public bool $showVoidModal = false;

    public string $voidReason = '';

    public function mount(Sale $sale): void
    {
        Gate::authorize('view', $sale);

        $this->sale = $sale->load(['items', 'user', 'customer', 'voidedBy']);
    }

    public function openVoidModal(): void
    {
        Gate::authorize('void', $this->sale);
        $this->voidReason = '';
        $this->resetValidation();
        $this->showVoidModal = true;
    }

    public function voidSale(VoidSaleAction $voidSale): void
    {
        Gate::authorize('void', $this->sale);

        $this->validate([
            'voidReason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'voidReason.required' => 'Alasan void wajib diisi.',
            'voidReason.min' => 'Alasan void minimal 5 karakter.',
            'voidReason.max' => 'Alasan void maksimal 500 karakter.',
        ]);

        try {
            $this->sale = $voidSale->execute($this->sale, auth()->user(), $this->voidReason);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0] ?? 'Terjadi kesalahan.');
            }

            return;
        } catch (AuthorizationException $exception) {
            $this->dispatch('toast', message: $exception->getMessage(), type: 'error');

            return;
        }

        $this->showVoidModal = false;
        $this->voidReason = '';
        $this->dispatch('toast', message: 'Transaksi di-void dan stok sudah dikembalikan.', type: 'success');
    }

    public function render()
    {
        Gate::authorize('view', $this->sale);

        return view('livewire.sales.sale-show');
    }
}

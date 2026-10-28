<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pengaturan Toko')]
class SettingForm extends Component
{
    public string $storeName = '';

    public string $address = '';

    public string $phone = '';

    public $taxPercent = 0;

    public string $receiptFooter = '';

    public function mount(): void
    {
        $setting = Setting::current();
        Gate::authorize('update', $setting);

        $this->storeName = $setting->store_name;
        $this->address = (string) $setting->address;
        $this->phone = (string) $setting->phone;
        $this->taxPercent = (float) $setting->tax_percent;
        $this->receiptFooter = (string) $setting->receipt_footer;
    }

    public function save(): void
    {
        $setting = Setting::current();
        Gate::authorize('update', $setting);

        $data = $this->validate([
            'storeName' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'taxPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'receiptFooter' => ['nullable', 'string', 'max:255'],
        ], [
            'storeName.required' => 'Nama toko wajib diisi.',
            'storeName.max' => 'Nama toko maksimal 100 karakter.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'phone.max' => 'Telepon maksimal 30 karakter.',
            'taxPercent.required' => 'Persen pajak wajib diisi (isi 0 jika tidak ada pajak).',
            'taxPercent.numeric' => 'Persen pajak harus berupa angka.',
            'taxPercent.min' => 'Persen pajak tidak boleh negatif.',
            'taxPercent.max' => 'Persen pajak maksimal 100.',
            'receiptFooter.max' => 'Footer struk maksimal 255 karakter.',
        ]);

        $setting->update([
            'store_name' => $data['storeName'],
            'address' => $data['address'] ?: null,
            'phone' => $data['phone'] ?: null,
            'tax_percent' => (float) $data['taxPercent'],
            'receipt_footer' => $data['receiptFooter'] ?: null,
        ]);

        $this->dispatch('toast', message: 'Pengaturan toko berhasil disimpan.', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.setting-form');
    }
}

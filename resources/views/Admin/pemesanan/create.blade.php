@extends('Admin.layout')

@section('title', 'Buat Pemesanan Barang ke Pemasok')
@section('page-title', 'Buat Catatan Pemesanan Baru ke Pemasok (Purchase Order)')
@section('page-subtitle', 'Sesuai Diagram Alir 3: Pilih nama pemasok, tanggal pemesanan, barang yang ingin dipesan, dan jumlah pesanan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <a href="{{ route('admin.pemesanan.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Daftar Pemesanan</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-error-container border border-error text-on-error-container text-xs shadow-xs space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">error</span>
                <span>Terjadi kesalahan pengisian data pesanan:</span>
            </div>
            <ul class="list-disc list-inside pl-2 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.pemesanan.store') }}" method="POST" class="bg-surface-container-lowest p-6 sm:p-8 rounded-2xl border border-surface-container shadow-xs space-y-8">
        @csrf

        <!-- SECTION 1: PEMASOK & TANGGAL (Sesuai Diagram 3) -->
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-surface-container">
                <span class="material-symbols-outlined text-primary text-xl">handshake</span>
                <h3 class="text-sm font-bold text-on-surface">1. Informasi Pemasok & Lokasi Tujuan Barang Masuk</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Nama Pemasok -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Pilih Pemasok (Supplier) <span class="text-error">*</span></label>
                    <select name="supplier_id" required class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface">
                        <option value="">-- Pilih Pemasok --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }} ({{ $sup->contact_person }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Pesan -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Tanggal Pemesanan <span class="text-error">*</span></label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface" />
                </div>

                <!-- Lokasi Tujuan Penerimaan -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-on-surface">Tujuan Stok Masuk <span class="text-error">*</span></label>
                    <select name="target_location_id" required class="w-full h-11 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('target_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} ({{ ucfirst($loc->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 2: BARANG YANG INGIN DIPESAN & JUMLAH (Sesuai Diagram 3) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-surface-container">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-xl">shopping_cart</span>
                    <h3 class="text-sm font-bold text-on-surface">2. Barang yang Ingin Dipesan & Jumlah (Item PO)</h3>
                </div>
                <button type="button" onclick="addItemRow()" class="px-3 py-1.5 rounded-lg bg-secondary-container text-on-secondary-container text-xs font-semibold hover:opacity-90 flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-xs">add</span>
                    <span>Tambah Baris Produk</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="items-table">
                    <thead>
                        <tr class="bg-surface-container-low/80 text-outline font-semibold border-b border-surface-container">
                            <th class="py-2.5 px-3">Produk Baju Bayi <span class="text-error">*</span></th>
                            <th class="py-2.5 px-3 w-36">Jumlah Pesan <span class="text-error">*</span></th>
                            <th class="py-2.5 px-3 w-40">Harga Beli Modal (Rp) <span class="text-error">*</span></th>
                            <th class="py-2.5 px-3 w-16 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-low" id="items-body">
                        <!-- Baris 1 Default -->
                        <tr class="item-row">
                            <td class="py-2.5 px-3">
                                <select name="items[0][product_id]" required onchange="updatePrice(this, 0)" class="w-full h-10 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container outline-none text-on-surface product-select">
                                    <option value="">-- Pilih Produk Baju Bayi --</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" data-cost="{{ (int)$prod->cost_price }}">
                                            {{ $prod->name }} ({{ $prod->variants ?? $prod->category }}) - SKU: {{ $prod->sku }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="number" name="items[0][quantity]" value="10" min="1" required class="w-full h-10 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container text-center font-bold text-on-surface outline-none" />
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="number" name="items[0][unit_cost]" id="cost-0" value="40000" min="0" required class="w-full h-10 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container text-right font-semibold text-on-surface outline-none" />
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <button type="button" onclick="removeRow(this)" class="w-8 h-8 rounded-lg bg-surface-container text-outline hover:text-error hover:bg-error-container transition-all flex items-center justify-center cursor-pointer mx-auto">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 3: CATATAN TAMBAHAN -->
        <div class="space-y-2">
            <label class="text-xs font-semibold text-on-surface">Catatan Pemesanan untuk Pemasok</label>
            <textarea name="notes" rows="2" placeholder="Catatan khusus nomor warna kain, kemasan polybag, instruksi ekspedisi..." class="w-full p-3 text-xs bg-surface-container-low rounded-xl border border-surface-container focus:ring-2 focus:ring-primary/40 outline-none text-on-surface">{{ old('notes') }}</textarea>
        </div>

        <!-- SUBMIT BUTTONS -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container">
            <a href="{{ route('admin.pemesanan.index') }}" class="px-5 py-2.5 rounded-xl bg-surface-container text-on-surface text-xs font-semibold hover:bg-surface-container-high transition-all">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-semibold hover:bg-surface-tint transition-all shadow-xs flex items-center gap-2 cursor-pointer">
                <span class="material-symbols-outlined text-sm">send</span>
                <span>Simpan Pesanan ke Pemasok</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let rowIndex = 1;
    const productsData = @json($products);

    function addItemRow() {
        let optionsHtml = '<option value="">-- Pilih Produk Baju Bayi --</option>';
        productsData.forEach(p => {
            optionsHtml += `<option value="${p.id}" data-cost="${parseInt(p.cost_price || 0)}">${p.name} (${p.variants || p.category}) - SKU: ${p.sku}</option>`;
        });

        const newRow = `
            <tr class="item-row">
                <td class="py-2.5 px-3">
                    <select name="items[${rowIndex}][product_id]" required onchange="updatePrice(this, ${rowIndex})" class="w-full h-10 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container outline-none text-on-surface product-select">
                        ${optionsHtml}
                    </select>
                </td>
                <td class="py-2.5 px-3">
                    <input type="number" name="items[${rowIndex}][quantity]" value="10" min="1" required class="w-full h-10 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container text-center font-bold text-on-surface outline-none" />
                </td>
                <td class="py-2.5 px-3">
                    <input type="number" name="items[${rowIndex}][unit_cost]" id="cost-${rowIndex}" value="40000" min="0" required class="w-full h-10 px-3 text-xs bg-surface-container-low rounded-xl border border-surface-container text-right font-semibold text-on-surface outline-none" />
                </td>
                <td class="py-2.5 px-3 text-center">
                    <button type="button" onclick="removeRow(this)" class="w-8 h-8 rounded-lg bg-surface-container text-outline hover:text-error hover:bg-error-container transition-all flex items-center justify-center cursor-pointer mx-auto">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </td>
            </tr>
        `;
        document.getElementById('items-body').insertAdjacentHTML('beforeend', newRow);
        rowIndex++;
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            btn.closest('tr').remove();
        } else {
            alert('Minimal harus ada 1 barang dalam pesanan!');
        }
    }

    function updatePrice(selectElem, index) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const cost = selectedOption.getAttribute('data-cost');
        if (cost) {
            const costInput = document.getElementById('cost-' + index);
            if (costInput) {
                costInput.value = cost;
            }
        }
    }
</script>
@endsection

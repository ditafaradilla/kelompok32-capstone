<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /** Riwayat transaksi + filter + ringkasan. */
    public function index(Request $request)
    {
        $request->validate([
            'from'     => 'nullable|date',
            'to'       => 'nullable|date|after_or_equal:from',
            'type'     => 'nullable|in:income,expense',
            'status'   => 'nullable|in:lunas,belum_lunas',
            'category' => 'nullable|string|max:60',
            'q'        => 'nullable|string|max:100',
        ]);

        // Default periode: bulan berjalan
        $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->input('to') ?: now()->endOfMonth()->toDateString();

        $base = Transaction::query()
            ->whereBetween('transaction_date', [$from, $to])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('payment_status', $request->input('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%' . $request->input('q') . '%';
                $q->where(function ($w) use ($keyword) {
                    $w->where('description', 'like', $keyword)
                      ->orWhere('reference', 'like', $keyword)
                      ->orWhere('event_name', 'like', $keyword);
                });
            });

        $summary = Transaction::summarize($base);

        $transactions = (clone $base)
            ->with('creator:id,name')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('Admin.keuangan.index', [
            'transactions'  => $transactions,
            'summary'       => $summary,
            'from'          => $from,
            'to'            => $to,
            'allCategories' => array_merge(Transaction::INCOME_CATEGORIES, Transaction::EXPENSE_CATEGORIES),
        ]);
    }

    public function create()
    {
        $transaction = new Transaction([
            'transaction_date' => now()->toDateString(),
            'type'             => Transaction::TYPE_INCOME,
            'payment_status'   => Transaction::STATUS_PAID,
        ]);

        return view('Admin.keuangan.form', $this->formData($transaction));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;

        Transaction::create($data);

        return redirect()->route('admin.keuangan.index')
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    public function edit(Transaction $transaction)
    {
        return view('Admin.keuangan.form', $this->formData($transaction));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $transaction->update($this->validateData($request));

        return redirect()->route('admin.keuangan.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    /** Tombol cepat: ubah status belum lunas -> lunas. */
    public function markPaid(Transaction $transaction)
    {
        $transaction->update(['payment_status' => Transaction::STATUS_PAID]);

        return back()->with('success', 'Transaksi ditandai lunas.');
    }

    /** Soft delete: data tidak hilang permanen dari database (jejak audit). */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    // ------------------------------------------------------------------

    private function formData(Transaction $transaction): array
    {
        return [
            'transaction' => $transaction,
            'categories'  => [
                Transaction::TYPE_INCOME  => Transaction::INCOME_CATEGORIES,
                Transaction::TYPE_EXPENSE => Transaction::EXPENSE_CATEGORIES,
            ],
            'methods'     => Transaction::PAYMENT_METHODS,
        ];
    }

    private function validateData(Request $request): array
    {
        // Pemilik toko boleh mengetik "1.500.000" -> dibersihkan menjadi 1500000
        $request->merge([
            'amount' => preg_replace('/\D/', '', (string) $request->input('amount')),
        ]);

        return $request->validate([
            'transaction_date' => 'required|date|before_or_equal:today',
            'type'             => ['required', Rule::in([Transaction::TYPE_INCOME, Transaction::TYPE_EXPENSE])],
            'category'         => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if (! in_array($value, Transaction::categoriesFor((string) $request->input('type')), true)) {
                        $fail('Kategori tidak sesuai dengan jenis transaksi.');
                    }
                },
            ],
            'amount'           => 'required|integer|min:1|max:999999999999',
            'payment_status'   => ['required', Rule::in([Transaction::STATUS_PAID, Transaction::STATUS_UNPAID])],
            'payment_method'   => ['nullable', Rule::in(Transaction::PAYMENT_METHODS)],
            'reference'        => 'nullable|string|max:100',
            'event_name'       => 'nullable|string|max:100',
            'description'      => 'nullable|string|max:1000',
        ], [
            'required'         => ':attribute wajib diisi.',
            'date'             => ':attribute bukan tanggal yang valid.',
            'before_or_equal'  => 'Tanggal transaksi tidak boleh melebihi hari ini.',
            'in'               => ':attribute tidak valid.',
            'integer'          => ':attribute harus berupa angka.',
            'min'              => ':attribute minimal :min.',
            'max'              => ':attribute terlalu besar / terlalu panjang (maksimal :max).',
        ], [
            'transaction_date' => 'Tanggal',
            'type'             => 'Jenis transaksi',
            'category'         => 'Kategori',
            'amount'           => 'Nominal',
            'payment_status'   => 'Status pembayaran',
            'payment_method'   => 'Metode pembayaran',
            'reference'        => 'No. referensi',
            'event_name'       => 'Nama bazar',
            'description'      => 'Keterangan',
        ]);
    }
}

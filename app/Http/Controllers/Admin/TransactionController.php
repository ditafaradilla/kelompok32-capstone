<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

        $base = $this->filteredQuery($request, $from, $to);

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

    /**
     * Unduh data pembukuan sebagai CSV (dibuka di Excel / Google Sheets).
     * scope: filter = sesuai filter di layar | all = semua data | period = periode pilihan sendiri
     */
    public function export(Request $request)
    {
        $request->validate([
            'scope'       => 'required|in:filter,all,period',
            'export_from' => 'required_if:scope,period|nullable|date',
            'export_to'   => 'required_if:scope,period|nullable|date|after_or_equal:export_from',
            'from'        => 'nullable|date',
            'to'          => 'nullable|date',
            'type'        => 'nullable|in:income,expense',
            'status'      => 'nullable|in:lunas,belum_lunas',
            'category'    => 'nullable|string|max:60',
            'q'           => 'nullable|string|max:100',
        ], [
            'required_if'     => ':attribute wajib diisi jika memilih "Pilih periode".',
            'after_or_equal'  => 'Tanggal "sampai" tidak boleh sebelum tanggal "dari".',
            'date'            => ':attribute bukan tanggal yang valid.',
        ], [
            'export_from' => 'Tanggal dari',
            'export_to'   => 'Tanggal sampai',
        ]);

        $scope = $request->input('scope');

        if ($scope === 'all') {
            $query = Transaction::query();
            $label = 'semua-data';
        } elseif ($scope === 'period') {
            $query = Transaction::query()->whereBetween('transaction_date', [$request->input('export_from'), $request->input('export_to')]);
            $label = $request->input('export_from') . '_sd_' . $request->input('export_to');
        } else {
            $from  = $request->input('from') ?: now()->startOfMonth()->toDateString();
            $to    = $request->input('to') ?: now()->endOfMonth()->toDateString();
            $query = $this->filteredQuery($request, $from, $to);
            $label = $from . '_sd_' . $to . '-sesuai-filter';
        }

        if (! (clone $query)->exists()) {
            return back()->withErrors(['export' => 'Tidak ada transaksi pada cakupan yang dipilih, jadi tidak ada yang diunduh.']);
        }

        $summary  = Transaction::summarize($query);
        $filename = 'pembukuan-baby-corner-' . $label . '.csv';

        return response()->streamDownload(function () use ($query, $summary) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8 dengan benar

            // Pemisah ";" cocok dengan Excel berpengaturan regional Indonesia
            $put = fn (array $row) => fputcsv($out, $row, ';', '"', '\\', "\r\n");

            $put(['No', 'Tanggal', 'Jenis', 'Kategori', 'Keterangan', 'No. Referensi', 'Nama Bazar',
                  'Metode Pembayaran', 'Status Pembayaran', 'Nominal (Rp)', 'Dicatat Oleh']);

            $no = 0;
            (clone $query)->with('creator:id,name')
                ->orderBy('transaction_date')->orderBy('id')
                ->chunk(500, function ($rows) use (&$no, $put) {
                    foreach ($rows as $t) {
                        $put([
                            ++$no,
                            Carbon::parse($t->transaction_date)->format('d/m/Y'),
                            $t->type === Transaction::TYPE_INCOME ? 'Pemasukan' : 'Pengeluaran',
                            $this->csvSafe($t->category),
                            $this->csvSafe($t->description),
                            $this->csvSafe($t->reference),
                            $this->csvSafe($t->event_name),
                            $this->csvSafe($t->payment_method),
                            $t->payment_status === Transaction::STATUS_PAID ? 'Lunas' : 'Belum lunas',
                            $t->amount, // angka bulat tanpa titik agar bisa dijumlah di Excel
                            $this->csvSafe($t->creator?->name),
                        ]);
                    }
                });

            // Ringkasan (angka yang sama dengan halaman Pembukuan: hanya transaksi lunas)
            $put([]);
            foreach ([
                'RINGKASAN (hanya transaksi lunas)' => '',
                'Total pemasukan'                   => $summary['income'],
                'Total pengeluaran'                 => $summary['expense'],
                'Selisih'                           => $summary['balance'],
                'Piutang (pemasukan belum lunas)'   => $summary['receivable'],
                'Utang (pengeluaran belum lunas)'   => $summary['payable'],
            ] as $name => $value) {
                $line    = array_fill(0, 11, '');
                $line[4] = $name;
                $line[9] = $value;
                $put($line);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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

    /** Query dengan filter yang sama untuk halaman riwayat dan ekspor (satu sumber, tanpa duplikasi). */
    private function filteredQuery(Request $request, string $from, string $to): Builder
    {
        return Transaction::query()
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
    }

    /** Mencegah "CSV injection": sel berawalan = + - @ bisa dieksekusi Excel sebagai rumus. */
    private function csvSafe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }

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
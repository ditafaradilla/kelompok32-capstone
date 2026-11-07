<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Membaca penjualan per produk dari tabel pesanan milik anggota lain.
 * Nama tabel/kolom diambil dari config/bi.php. Mati bila 'enabled' = false.
 */
class ProductSalesRepository
{
    public function enabled(): bool
    {
        return (bool) config('bi.product_sales.enabled');
    }

    /** Jumlah pesanan pada periode. */
    public function orderCount(string $from, string $to): int
    {
        $c = config('bi.product_sales');

        $query = DB::table($this->col($c['orders_table']) . ' as o');
        $this->filterOrders($query, $c, $from, $to);

        return (int) $query->count();
    }

    /**
     * Penjualan per produk pada periode.
     * @return Collection<int, object{product_id:mixed,name:string,stock:?int,qty:int,revenue:int}>
     */
    public function productSales(string $from, string $to): Collection
    {
        $c = config('bi.product_sales');

        $pk    = 'p.' . $this->col($c['products_pk']);
        $name  = 'p.' . $this->col($c['product_name']);
        $stock = $c['product_stock'] ? 'p.' . $this->col($c['product_stock']) : null;
        $qty   = 'i.' . $this->col($c['item_qty']);
        $price = 'i.' . $this->col($c['item_price']);

        $query = DB::table($this->col($c['items_table']) . ' as i')
            ->join($this->col($c['orders_table']) . ' as o', 'o.' . $this->col($c['orders_pk']), '=', 'i.' . $this->col($c['item_order_fk']))
            ->join($this->col($c['products_table']) . ' as p', $pk, '=', 'i.' . $this->col($c['item_product_fk']));

        $this->filterOrders($query, $c, $from, $to);

        $query->selectRaw(
            "{$pk} AS product_id, {$name} AS name, " . ($stock ?? 'NULL') . " AS stock, "
            . "SUM({$qty}) AS qty, SUM({$qty} * {$price}) AS revenue"
        )->groupBy($pk, $name);

        if ($stock) {
            $query->groupBy($stock);
        }

        return $query->get()->map(fn ($r) => (object) [
            'product_id' => $r->product_id,
            'name'       => $r->name,
            'stock'      => $r->stock !== null ? (int) $r->stock : null,
            'qty'        => (int) $r->qty,
            'revenue'    => (int) round((float) $r->revenue),
        ]);
    }

    private function filterOrders(Builder $query, array $c, string $from, string $to): void
    {
        $date = 'o.' . $this->col($c['order_date']);
        $query->whereDate($date, '>=', $from)->whereDate($date, '<=', $to);

        if (! empty($c['paid_statuses'])) {
            $query->whereIn('o.' . $this->col($c['order_status']), $c['paid_statuses']);
        }
    }

    /** Nama tabel/kolom berasal dari config, tetap divalidasi agar aman dipakai di SQL. */
    private function col(string $name): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new InvalidArgumentException("Nama tabel/kolom tidak valid di config/bi.php: {$name}");
        }

        return $name;
    }
}
<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    public function test_admin_can_manage_product_and_separate_stock_by_location(): void
    {
        $store = Location::create(['name' => 'Toko Utama', 'code' => 'TKO-01', 'type' => 'toko']);
        $warehouse = Location::create(['name' => 'Gudang', 'code' => 'GDG-01', 'type' => 'gudang']);

        $response = $this->actingAs($this->admin())->post(route('admin.produk.store'), [
            'sku' => 'BC-BYI-001',
            'name' => 'Baju Bayi',
            'category' => 'Pakaian',
            'price' => 100000,
            'cost_price' => 50000,
            'min_stock' => 3,
            'stocks' => [
                $store->id => 4,
                $warehouse->id => 8,
            ],
        ]);

        $response->assertRedirect(route('admin.produk.index'));
        $product = Product::where('sku', 'BC-BYI-001')->firstOrFail();
        $this->assertSame(12, $product->total_stock);
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'location_id' => $store->id,
            'quantity' => 4,
        ]);
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'location_id' => $warehouse->id,
            'quantity' => 8,
        ]);

        $this->actingAs($this->admin())->put(route('admin.produk.update', $product), [
            'sku' => 'BC-BYI-001',
            'name' => 'Baju Bayi Organik',
            'category' => 'Pakaian',
            'price' => 120000,
            'cost_price' => 60000,
            'min_stock' => 3,
            'stocks' => [
                $store->id => 2,
            ],
        ])->assertRedirect(route('admin.produk.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Baju Bayi Organik',
        ]);
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'location_id' => $store->id,
            'quantity' => 2,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.produk.destroy', $product))
            ->assertRedirect(route('admin.produk.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_stocks', ['product_id' => $product->id]);
    }

    public function test_admin_can_record_location_and_update_its_stock(): void
    {
        $product = Product::create([
            'sku' => 'BC-BYI-002',
            'name' => 'Celana Bayi',
            'category' => 'Pakaian',
            'price' => 50000,
            'cost_price' => 25000,
            'min_stock' => 2,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.lokasi.store'), [
                'name' => 'Cabang Bandung',
                'type' => 'toko',
                'address' => 'Bandung',
            ])
            ->assertRedirect();

        $location = Location::where('name', 'Cabang Bandung')->firstOrFail();
        $this->assertSame('TKO-01', $location->code);

        $this->actingAs($this->admin())
            ->post(route('admin.lokasi.updateStock'), [
                'location_id' => $location->id,
                'stocks' => [$product->id => 6],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 6,
        ]);
    }

    public function test_admin_can_create_purchase_order_for_supplier(): void
    {
        $supplier = Supplier::create(['name' => 'Pemasok Baju']);
        $location = Location::create(['name' => 'Gudang', 'code' => 'GDG-01', 'type' => 'gudang']);
        $product = Product::create([
            'sku' => 'BC-BYI-003',
            'name' => 'Jaket Bayi',
            'category' => 'Pakaian',
            'price' => 150000,
            'cost_price' => 75000,
            'min_stock' => 2,
        ]);

        $response = $this->actingAs($this->admin())->post(route('admin.pemesanan.store'), [
            'supplier_id' => $supplier->id,
            'target_location_id' => $location->id,
            'order_date' => now()->toDateString(),
            'notes' => 'Pesanan stok bulanan',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 3,
                'unit_cost' => 75000,
            ]],
        ]);

        $response->assertRedirect();
        $order = PurchaseOrder::firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertSame(225000.0, (float) $order->total_amount);
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity_ordered' => 3,
            'quantity_received' => 0,
            'subtotal' => 225000,
        ]);
    }

    public function test_receiving_goods_adds_stock_and_warns_when_stock_is_low(): void
    {
        $location = Location::create(['name' => 'Gudang', 'code' => 'GDG-01', 'type' => 'gudang']);
        $supplier = Supplier::create(['name' => 'Pemasok Baju']);
        $product = Product::create([
            'sku' => 'BC-BYI-004',
            'name' => 'Topi Bayi',
            'category' => 'Aksesori',
            'price' => 40000,
            'cost_price' => 20000,
            'min_stock' => 5,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 2,
        ]);
        $order = PurchaseOrder::create([
            'po_number' => 'PO-TEST-0001',
            'supplier_id' => $supplier->id,
            'target_location_id' => $location->id,
            'order_date' => now()->toDateString(),
            'status' => 'pending',
        ]);
        $item = PurchaseOrderItem::create([
            'purchase_order_id' => $order->id,
            'product_id' => $product->id,
            'quantity_ordered' => 4,
            'quantity_received' => 0,
            'unit_cost' => 20000,
            'subtotal' => 80000,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.penerimaan.store', $order), [
                'received_date' => now()->toDateString(),
                'items' => [$item->id => ['received_qty' => 1]],
            ])
            ->assertRedirect(route('admin.penerimaan.show', $order))
            ->assertSessionHas('low_stock_alerts', function (array $alerts) use ($product): bool {
                return count($alerts) === 1
                    && $alerts[0]['name'] === $product->name
                    && $alerts[0]['total_stock'] === 3
                    && $alerts[0]['min_stock'] === 5
                    && $alerts[0]['is_empty'] === false;
            });

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('purchase_order_items', [
            'id' => $item->id,
            'quantity_received' => 1,
        ]);
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $order->id,
            'status' => 'received',
            'received_date' => now()->toDateString(),
        ]);
    }

    public function test_admin_sees_out_of_stock_warning_for_empty_product(): void
    {
        Product::create([
            'sku' => 'BC-BYI-005',
            'name' => 'Produk Kosong',
            'category' => 'Pakaian',
            'price' => 50000,
            'cost_price' => 25000,
            'min_stock' => 2,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.produk.index'))
            ->assertOk()
            ->assertSee('Stok Habis')
            ->assertSee('Produk Kosong');
    }
}

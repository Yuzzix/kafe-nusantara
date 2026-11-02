<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantPosFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_all_primary_pages_and_master_detail_views_render_cleanly(): void
    {
        // Dashboard Ringkasan
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan Gerai')
            ->assertSee('5 Sajian Terlaris');

        // Master: Kategori Menu
        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Makanan Utama')
            ->assertSee('Kopi')
            ->assertSee('Menu Musiman');

        // Detail: Detail Kategori (Kopi)
        $this->get(route('categories.show', 'kopi'))
            ->assertOk()
            ->assertSee('Kopi Susu Gula Aren')
            ->assertSee('images/menus/kopi-susu-gula-aren.webp')
            ->assertSee('Espresso');

        // Master + Filters: Daftar Menu
        $this->get(route('menus.index'))
            ->assertOk()
            ->assertSee('Daftar Menu')
            ->assertSee('Nasi Goreng Spesial');

        $this->get(route('menus.create'))
            ->assertOk()
            ->assertSee('Tambah Menu Baru');

        // Master: Pesanan & Kasir + Detail Pesanan
        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Pesanan & Kasir')
            ->assertSee('Total Tagihan');

        $sampleOrder = Order::firstOrFail();
        $this->get(route('orders.show', $sampleOrder->order_number))
            ->assertOk()
            ->assertSee($sampleOrder->order_number);

        $this->get(route('orders.create'))
            ->assertOk()
            ->assertSee('Buat Pesanan Baru');

        // Status Meja & QR Stand Modal
        $this->get(route('tables.index'))
            ->assertOk()
            ->assertSee('Status Meja')
            ->assertSee('Kartu Stand Meja')
            ->assertSee('VIP2');

        // Layar Dapur (Kitchen Display System)
        $this->get(route('kitchen.index'))
            ->assertOk()
            ->assertSee('Layar Dapur')
            ->assertSee('Menunggu Antrean');

        // Buku Menu Digital Tamu (with Table QR query parameter)
        $this->get(route('menu-book', ['meja' => 'VIP1']))
            ->assertOk()
            ->assertSee('Buku Sajian Resmi')
            ->assertSee('Meja VIP1')
            ->assertSee('Nasi Goreng Spesial');
    }

    public function test_can_create_new_menu_with_category_relation_and_photo(): void
    {
        Storage::fake('public');
        $kopiCategory = Category::where('slug', 'kopi')->firstOrFail();

        $response = $this->post(route('menus.store'), [
            'name' => 'Affogato Nusantara',
            'category_id' => $kopiCategory->id,
            'price' => 34000,
            'description' => 'Es krim vanila disiram espresso hangat.',
            'is_available' => '1',
            'is_featured' => '1',
            'image' => UploadedFile::fake()->image('affogato.jpg', 400, 400),
        ]);

        $response->assertRedirect(route('menus.index'));

        $menu = Menu::where('slug', 'affogato-nusantara')->first();
        $this->assertNotNull($menu);
        $this->assertSame($kopiCategory->id, $menu->category_id);
        $this->assertSame(34000, $menu->price);
        $this->assertTrue($menu->is_featured);
        $this->assertNotNull($menu->image);
        $this->assertTrue($menu->hasStoredUpload());
        $this->assertStringContainsString('/storage/menus/', (string) $menu->image_url);
        Storage::disk('public')->assertExists($menu->image);
    }

    public function test_full_pos_order_creation_kitchen_flow_and_cash_payment_cycle(): void
    {
        $freeTable = DiningTable::where('status', DiningTable::STATUS_AVAILABLE)->firstOrFail();
        $nasgor = Menu::where('slug', 'nasi-goreng-spesial')->firstOrFail(); // 35,000
        $kopisusu = Menu::where('slug', 'kopi-susu-gula-aren')->firstOrFail(); // 25,000

        // 1x 35,000 + 2x 25,000 = 85,000 subtotal
        // Dine-in service 5% = 4,250 -> tax base 89,250 -> PB1 10% = 8,925 -> Total = 98,175 (exact PDF Page 7 math!)
        $createResponse = $this->post(route('orders.store'), [
            'customer_name' => 'Dewi',
            'order_type' => Order::TYPE_DINE_IN,
            'dining_table_id' => $freeTable->id,
            'discount' => 0,
            'items' => [
                $nasgor->id => ['quantity' => 1, 'notes' => ''],
                $kopisusu->id => ['quantity' => 2, 'notes' => 'Kurangi gula'],
            ],
        ]);

        $order = Order::where('customer_name', 'Dewi')->latest('id')->firstOrFail();
        $createResponse->assertRedirect(route('orders.show', $order->order_number));

        $this->assertSame(85000, $order->subtotal);
        $this->assertSame(4250, $order->service_charge);
        $this->assertSame(8925, $order->tax);
        $this->assertSame(98175, $order->total);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame(Order::PAYMENT_UNPAID, $order->payment_status);

        // Table automatically marked occupied
        $this->assertSame(DiningTable::STATUS_OCCUPIED, $freeTable->fresh()->status);

        // Guard: occupied table cannot be manually forced to available while order is active
        $this->patch(route('tables.update-status', $freeTable), ['status' => DiningTable::STATUS_AVAILABLE])
            ->assertSessionHas('error');
        $this->assertSame(DiningTable::STATUS_OCCUPIED, $freeTable->fresh()->status);

        // Kitchen status progression: pending -> preparing -> served
        $this->patch(route('orders.update-status', $order->order_number), ['status' => Order::STATUS_PREPARING]);
        $this->assertSame(Order::STATUS_PREPARING, $order->fresh()->status);

        $this->patch(route('orders.update-status', $order->order_number), ['status' => Order::STATUS_SERVED]);
        $this->assertSame(Order::STATUS_SERVED, $order->fresh()->status);

        // Guard: underpaying cash is rejected
        $this->post(route('orders.pay', $order->order_number), [
            'method' => Payment::METHOD_CASH,
            'received_amount' => 50000,
        ])->assertSessionHasErrors('received_amount');

        // Pay cash Rp 100.000 -> Change Rp 1.825 -> Order completed & table freed back to available!
        $this->post(route('orders.pay', $order->order_number), [
            'method' => Payment::METHOD_CASH,
            'received_amount' => 100000,
        ])->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertSame(1825, $order->payments()->firstOrFail()->change_amount);
        $this->assertSame(DiningTable::STATUS_AVAILABLE, $freeTable->fresh()->status);
    }

    public function test_unavailable_menu_cannot_be_ordered(): void
    {
        $soldOutMenu = Menu::where('is_available', false)->firstOrFail();

        $this->post(route('orders.store'), [
            'customer_name' => 'Rudi',
            'order_type' => Order::TYPE_TAKEAWAY,
            'items' => [
                $soldOutMenu->id => ['quantity' => 1],
            ],
        ])->assertSessionHasErrors('items');
    }
}

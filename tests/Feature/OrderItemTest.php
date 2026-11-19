<?php

namespace Tests\Feature;

use App\Http\Controllers\API\OrderControler;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('orders', function (Blueprint $table): void {
            $table->id('order_id');
            $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('amount');
            $table->timestamps();
            $table->primary(['order_id', 'product_id']);
        });
        Route::post('/test-orders/{orderId}/items', [OrderControler::class, 'addItem']);
    }

    public function test_creates_a_new_order_item(): void
    {
        DB::table('orders')->insert(['order_id' => 1]);

        $response = $this->postJson('/test-orders/1/items', ['product_id' => 2, 'quantity' => 3]);

        $response->assertCreated()->assertJsonPath('orderItem.amount', 3);
        $this->assertDatabaseHas('order_items', ['order_id' => 1, 'product_id' => 2, 'amount' => 3]);
    }

    public function test_replaces_quantity_for_an_existing_item_without_changing_other_orders(): void
    {
        DB::table('orders')->insert([['order_id' => 1], ['order_id' => 2]]);
        DB::table('order_items')->insert([
            ['order_id' => 1, 'product_id' => 2, 'amount' => 3],
            ['order_id' => 2, 'product_id' => 2, 'amount' => 7],
        ]);

        $response = $this->postJson('/test-orders/1/items', ['product_id' => 2, 'quantity' => 5]);

        $response->assertOk()->assertJsonPath('orderItem.amount', 5);
        $this->assertDatabaseHas('order_items', ['order_id' => 1, 'product_id' => 2, 'amount' => 5]);
        $this->assertDatabaseHas('order_items', ['order_id' => 2, 'product_id' => 2, 'amount' => 7]);
        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_zero_quantity_deletes_only_the_matching_order_item(): void
    {
        DB::table('orders')->insert([['order_id' => 1], ['order_id' => 2]]);
        DB::table('order_items')->insert([
            ['order_id' => 1, 'product_id' => 2, 'amount' => 3],
            ['order_id' => 2, 'product_id' => 2, 'amount' => 7],
        ]);

        $response = $this->postJson('/test-orders/1/items', ['product_id' => 2, 'quantity' => 0]);

        $response->assertOk()->assertJsonPath('orderItem', null);
        $this->assertDatabaseMissing('order_items', ['order_id' => 1, 'product_id' => 2]);
        $this->assertDatabaseHas('order_items', ['order_id' => 2, 'product_id' => 2, 'amount' => 7]);
    }

    public function test_zero_quantity_for_a_missing_item_does_not_create_it(): void
    {
        DB::table('orders')->insert(['order_id' => 1]);

        $response = $this->postJson('/test-orders/1/items', ['product_id' => 2, 'quantity' => 0]);

        $response->assertOk()->assertJsonPath('orderItem', null);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_negative_quantity_is_rejected(): void
    {
        DB::table('orders')->insert(['order_id' => 1]);

        $response = $this->postJson('/test-orders/1/items', ['product_id' => 2, 'quantity' => -1]);

        $response->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_missing_order_returns_not_found(): void
    {
        $response = $this->postJson('/test-orders/99/items', ['product_id' => 2, 'quantity' => 1]);

        $response->assertNotFound()->assertJsonPath('message', 'Order not found');
        $this->assertDatabaseCount('order_items', 0);
    }
}

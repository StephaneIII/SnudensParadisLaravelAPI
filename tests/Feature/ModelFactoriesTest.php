<?php

namespace Tests\Feature;

use App\Models\AttributesValue;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModelFactoriesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    }

    public function test_user_factory_produces_a_persistable_user(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', ['uid' => $user->uid, 'is_employee' => false]);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertLessThanOrEqual(50, mb_strlen($user->email));
        $this->assertLessThanOrEqual(15, mb_strlen($user->tlf));
    }

    public function test_order_item_factory_creates_related_order_user_and_product(): void
    {
        $item = OrderItem::factory()->create();

        $this->assertDatabaseHas('order_items', ['order_id' => $item->order_id, 'product_id' => $item->product_id, 'amount' => $item->amount]);
        $this->assertDatabaseHas('orders', ['order_id' => $item->order_id, 'uid' => $item->order->uid]);
        $this->assertDatabaseHas('users', ['uid' => $item->order->uid]);
        $this->assertDatabaseHas('products', ['product_id' => $item->product_id]);
    }

    public function test_attribute_value_factory_creates_related_product_and_attribute(): void
    {
        $value = AttributesValue::factory()->create();

        $this->assertDatabaseHas('attributes_values', ['product_id' => $value->product_id, 'attributes_id' => $value->attributes_id, 'value' => $value->value]);
        $this->assertDatabaseHas('products', ['product_id' => $value->product_id]);
        $this->assertDatabaseHas('attributes', ['attributes_id' => $value->attributes_id]);
    }
}

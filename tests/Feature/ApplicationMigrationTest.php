<?php

namespace Tests\Feature;

use App\Http\Controllers\API\ProductControler;
use App\Models\Attributes;
use App\Models\AttributesValue;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ApplicationMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);
        DB::purge('sqlite');
    }

    public function test_migrations_create_tables_and_cascade_related_records(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $item = OrderItem::factory()->create();
        $value = AttributesValue::factory()->create(['product_id' => $item->product_id]);

        DB::table('users')->where('uid', $item->order->uid)->delete();

        $this->assertDatabaseMissing('orders', ['order_id' => $item->order_id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $item->order_id]);
        $this->assertDatabaseHas('attributes_values', ['product_id' => $value->product_id]);

        DB::table('products')->where('product_id', $value->product_id)->delete();

        $this->assertDatabaseMissing('attributes_values', ['product_id' => $value->product_id]);
    }

    public function test_foreign_keys_reject_an_attribute_value_for_a_missing_product(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $value = AttributesValue::factory()->make(['product_id' => 999]);

        try {
            $value->save();
            $this->fail('A missing product must violate the foreign key.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('FOREIGN KEY constraint failed', $exception->getMessage());
        }

        $this->assertDatabaseCount('attributes_values', 0);
    }

    public function test_product_creation_accepts_omitted_optional_attributes_on_the_migrated_schema(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $attribute = Attributes::factory()->create();
        Route::post('/test-migrated-products', [ProductControler::class, 'create']);

        $response = $this->postJson('/test-migrated-products', [
            'name' => 'Food',
            'description' => 'Pet food',
            'price' => 12,
            'attribute_values' => [['attributes_id' => $attribute->attributes_id, 'value' => 'Chicken']],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('attributes_values', [
            'product_id' => $response->json('product.product_id'),
            'attributes_id' => $attribute->attributes_id,
            'value' => 'Chicken',
        ]);
    }

    public function test_existing_users_block_the_upgrade_without_changing_their_data(): void
    {
        $original = require database_path('migrations/0001_01_01_000000_create_users_table.php');
        $original->up();
        DB::table('users')->insert(['name' => 'Existing User', 'email' => 'user@example.test', 'password' => 'hash']);

        $migration = require database_path('migrations/2026_11_18_102434_align_users_table_with_application_models.php');

        try {
            $migration->up();
            $this->fail('Existing users must block the migration.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('existing users are present', $exception->getMessage());
        }

        $this->assertTrue(Schema::hasColumn('users', 'id'));
        $this->assertFalse(Schema::hasColumn('users', 'uid'));
        $this->assertDatabaseHas('users', ['name' => 'Existing User', 'email' => 'user@example.test']);
    }

    public function test_empty_schema_can_be_rolled_back_to_the_original_users_table(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();

        $catalog = require database_path('migrations/2026_11_18_102435_create_product_and_order_tables.php');
        $users = require database_path('migrations/2026_11_18_102434_align_users_table_with_application_models.php');
        $catalog->down();
        $users->down();

        $this->assertFalse(Schema::hasTable('products'));
        $this->assertTrue(Schema::hasColumn('users', 'id'));
        $this->assertTrue(Schema::hasColumn('users', 'name'));
        $this->assertFalse(Schema::hasColumn('users', 'uid'));
    }

    public function test_rollback_stops_before_dropping_tables_when_users_exist(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $user = User::factory()->create();
        $catalog = require database_path('migrations/2026_11_18_102435_create_product_and_order_tables.php');

        try {
            $catalog->down();
            $this->fail('Existing users must block the rollback.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('while users are present', $exception->getMessage());
        }

        $this->assertTrue(Schema::hasTable('products'));
        $this->assertDatabaseHas('users', ['uid' => $user->uid]);
    }
}

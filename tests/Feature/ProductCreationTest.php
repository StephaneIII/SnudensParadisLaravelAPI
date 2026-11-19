<?php

namespace Tests\Feature;

use App\Http\Controllers\API\ProductControler;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('products', function (Blueprint $table): void {
            $table->id('product_id');
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
        Schema::create('attributes', function (Blueprint $table): void {
            $table->id('attributes_id');
        });
        Schema::create('attributes_values', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attributes_id');
            $table->string('value');
            $table->decimal('min_value', 10, 3)->nullable();
            $table->decimal('max_value', 10, 3)->nullable();
            $table->timestamps();
            $table->primary(['product_id', 'attributes_id']);
        });
        Route::post('/test-products', [ProductControler::class, 'create']);
    }

    public function test_creates_a_product_with_attribute_values(): void
    {
        DB::table('attributes')->insert([['attributes_id' => 1], ['attributes_id' => 2]]);

        $response = $this->postJson('/test-products', [
            'name' => 'Food',
            'description' => 'Pet food',
            'price' => 12,
            'attribute_values' => [
                ['attributes_id' => 1, 'value' => 'Chicken'],
                ['attributes_id' => 2, 'value' => 'Large', 'min_value' => 1, 'max_value' => 2],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('product.attribute_values.1.value', 'Large');
        $productId = $response->json('product.product_id');
        $this->assertDatabaseHas('products', ['product_id' => $productId, 'name' => 'Food']);
        $this->assertDatabaseHas('attributes_values', ['product_id' => $productId, 'attributes_id' => 1, 'value' => 'Chicken']);
        $this->assertDatabaseHas('attributes_values', ['product_id' => $productId, 'attributes_id' => 2, 'value' => 'Large', 'min_value' => 1, 'max_value' => 2]);
    }

    public function test_product_can_be_created_without_attribute_values(): void
    {
        $response = $this->postJson('/test-products', ['name' => 'Food', 'description' => 'Pet food', 'price' => 12]);

        $response->assertCreated()->assertJsonMissingPath('product.attribute_values');
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('attributes_values', 0);
    }

    public function test_invalid_attribute_values_do_not_create_a_product(): void
    {
        $response = $this->postJson('/test-products', [
            'name' => 'Food', 'description' => 'Pet food', 'price' => 12,
            'attribute_values' => [['attributes_id' => 999, 'value' => 'Chicken']],
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('attribute_values.0.attributes_id');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_duplicate_attribute_ids_do_not_create_a_product(): void
    {
        DB::table('attributes')->insert(['attributes_id' => 1]);

        $response = $this->postJson('/test-products', [
            'name' => 'Food', 'description' => 'Pet food', 'price' => 12,
            'attribute_values' => [
                ['attributes_id' => 1, 'value' => 'Chicken'],
                ['attributes_id' => 1, 'value' => 'Beef'],
            ],
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('attribute_values.0.attributes_id');
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_is_rolled_back_if_an_attribute_value_cannot_be_saved(): void
    {
        DB::table('attributes')->insert(['attributes_id' => 1]);
        DB::unprepared("CREATE TRIGGER reject_attribute BEFORE INSERT ON attributes_values BEGIN SELECT RAISE(FAIL, 'Rejected'); END");

        $response = $this->postJson('/test-products', [
            'name' => 'Food', 'description' => 'Pet food', 'price' => 12,
            'attribute_values' => [['attributes_id' => 1, 'value' => 'Chicken']],
        ]);

        $response->assertInternalServerError();
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('attributes_values', 0);
    }
}

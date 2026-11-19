<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('product_id');
            $table->string('name', 30);
            $table->decimal('rating', 10, 2)->nullable();
            $table->decimal('price', 10, 2);
            $table->string('description', 150);
            $table->string('mærke', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->increments('attributes_id');
            $table->string('attributes_name', 100);
            $table->string('description', 100);
            $table->string('data_type', 20);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->unsignedInteger('uid');
            $table->foreign('uid')->references('uid')->on('users')->cascadeOnDelete();
            $table->dateTime('order_date');
            $table->decimal('total_amount', 10, 2);
            $table->string('status', 30);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
            $table->integer('amount');
            $table->primary(['order_id', 'product_id']);
            $table->timestamps();
        });

        Schema::create('attributes_values', function (Blueprint $table) {
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('attributes_id');
            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
            $table->foreign('attributes_id')->references('attributes_id')->on('attributes')->cascadeOnDelete();
            $table->string('value', 100);
            $table->decimal('min_value', 7, 3)->nullable();
            $table->decimal('max_value', 7, 3)->nullable();
            $table->primary(['product_id', 'attributes_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('users')->exists()) {
            throw new RuntimeException('Cannot roll back the application tables while users are present; preserve their data first.');
        }

        Schema::dropIfExists('attributes_values');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('products');
    }
};

<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
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
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    }

    public function test_seeder_populates_related_application_tables_with_correct_order_totals(): void
    {
        $this->seed();

        foreach (['users', 'products', 'attributes', 'attributes_values', 'orders', 'order_items'] as $table) {
            $this->assertGreaterThan(0, DB::table($table)->count(), $table.' should have seeded records');
        }

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        foreach (Order::with('orderItems.product')->get() as $order) {
            $this->assertCount(2, $order->orderItems);
            $expected = $order->orderItems->sum(fn ($item) => (float) $item->product->price * $item->amount);
            $this->assertSame(number_format($expected, 2, '.', ''), $order->total_amount);
        }
    }

    public function test_failed_seed_rolls_back_partial_inserts(): void
    {
        $this->seed();
        $usersBefore = DB::table('users')->count();
        $productsBefore = DB::table('products')->count();

        try {
            $this->seed();
            $this->fail('Seeding the same test email twice must fail.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('UNIQUE constraint failed', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', $usersBefore);
        $this->assertDatabaseCount('products', $productsBefore);
    }
}

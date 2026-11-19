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
        if (DB::table('users')->exists()) {
            throw new RuntimeException('Cannot migrate users while existing users are present; migrate their data first.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('id', 'uid');
            $table->renameColumn('name', 'first_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->increments('uid')->change();
            $table->string('first_name', 50)->change();
            $table->string('last_name', 50);
            $table->string('email', 50)->change();
            $table->string('password', 70)->change();
            $table->string('tlf', 15);
            $table->boolean('is_employee')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('users')->exists()) {
            throw new RuntimeException('Cannot roll back users while users are present; preserve their data first.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'tlf', 'is_employee']);
            $table->bigIncrements('uid')->change();
            $table->string('first_name')->change();
            $table->string('email')->change();
            $table->string('password')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('uid', 'id');
            $table->renameColumn('first_name', 'name');
        });
    }
};

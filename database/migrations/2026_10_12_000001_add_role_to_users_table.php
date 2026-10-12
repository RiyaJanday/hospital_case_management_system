<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Adds a "role" column to the existing users table.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Allowed values: 'admin' or 'viewer'. New users are viewers by default.
            $table->string('role')->default('viewer');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tb_orders', 'limit_capacity')) {
            Schema::table('tb_orders', function (Blueprint $table): void {
                $table->string('limit_capacity')->nullable();
            });
        }

        if (! Schema::hasColumn('tb_orders', 'limit_qty')) {
            Schema::table('tb_orders', function (Blueprint $table): void {
                $table->string('limit_qty')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Columns also exist on the original tb_orders migration for fresh installs.
    }
};

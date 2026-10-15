<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_sales', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0.00)->after('grand_total');
            }
            if (!Schema::hasColumn('tbl_sales', 'balance')) {
                $table->decimal('balance', 12, 2)->default(0.00)->after('paid_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_sales', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('tbl_sales', 'paid_amount')) {
                $columnsToDrop[] = 'paid_amount';
            }
            if (Schema::hasColumn('tbl_sales', 'balance')) {
                $columnsToDrop[] = 'balance';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
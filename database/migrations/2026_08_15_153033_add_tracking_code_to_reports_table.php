<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('reports', function (Blueprint $table) {
        $table->string('tracking_code', 8)->nullable()->unique()->after('id');
    });
}

public function down(): void
{
    Schema::table('reports', function (Blueprint $table) {
        $table->dropColumn('tracking_code');
    });
}
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_clusters', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->decimal('center_latitude', 10, 7);
            $table->decimal('center_longitude', 10, 7);
            $table->unsignedInteger('report_count')->default(1);
            $table->unsignedInteger('priority_score')->default(0);
            $table->string('status')->default('reported');
            $table->timestamp('first_reported_at');
            $table->timestamp('last_reported_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_clusters');
    }
};

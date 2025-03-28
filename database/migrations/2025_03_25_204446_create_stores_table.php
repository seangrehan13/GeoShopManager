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
        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('name');
            $table->geography('coordinates', subtype: 'point');
            $table->enum('status', ['open', 'closed'])->default('open'); // Since this field is unlikely to change, it made sense to use an enum
            $table->string('store_type_id')->foreign('store_type_id')->constrained();
            $table->unsignedInteger('max_delivery_distance_in_meters'); // To avoid a future disaster, better to have clear units and no fractions
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};

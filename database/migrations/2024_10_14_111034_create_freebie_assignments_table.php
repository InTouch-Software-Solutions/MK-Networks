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
        Schema::create('freebie_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); //  products' id 
            $table->unsignedBigInteger('salesman_id'); // salesman_id
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freebie_assignments');
    }
};
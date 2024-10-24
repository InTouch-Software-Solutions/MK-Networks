<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('freebie_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); 
            $table->unsignedBigInteger('salesman_id'); 
            $table->integer('assigned_quantity'); 
            $table->integer('sold_quantity')->default(0); 
            $table->integer('gifted_quantity')->default(0); 
            $table->integer('remaining_quantity')->default(0); 
            $table->integer('threshold')->default(5); // Threshold for notifications
            $table->unsignedBigInteger('assigned_by'); 
            $table->timestamp('assigned_at')->useCurrent();
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
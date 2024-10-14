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
        Schema::create('assign_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Salesperson ID
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('route_plan_id'); // Foreign key to route_plans table
            $table->foreign('route_plan_id')->references('id')->on('routes')->onDelete('cascade');
            $table->string('shop_name'); // Shop name
            $table->string('address'); // Shop address
            $table->string('route'); // Route identifier
            $table->string('area'); // Area to which the route belongs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_routes');
    }
};

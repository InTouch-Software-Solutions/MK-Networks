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
        Schema::create('sim_data', function (Blueprint $table) {
            $table->id();
            $table->string('network');
            $table->string('product');
            $table->string('sim_number');
            $table->string('mobile_number');
            $table->string('customer_name');
            $table->string('sage_customer_number');
            $table->string('date_of_sale');
            $table->string('customer_buy_price');
            $table->string('customer_commission_due');
            $table->string('topup2_bonus')->nullable();
            $table->string('topup3_bonus')->nullable();
            $table->string('topup4_bonus')->nullable();
            $table->string('topup5_bonus')->nullable();
            $table->string('topup6_bonus')->nullable();
            $table->string('topup7_bonus')->nullable();
            $table->string('topup8_bonus')->nullable();
            $table->string('topup9_bonus')->nullable();
            $table->string('topup10_bonus')->nullable();
            $table->string('topup11_bonus')->nullable();
            $table->string('topup12_bonus')->nullable();
            $table->string('end_user_first_name');
            $table->string('end_user_last_name');
            $table->string('end_user_address');
            $table->string('end_user_postcode');
            $table->boolean('invoiced');
            $table->string('master_carton')->nullable();
            $table->string('revenue_share_month1_percent');
            $table->string('revenue_share_month2_percent');
            $table->string('revenue_share_month3_percent');
            $table->string('revenue_share_month4_percent');
            $table->string('revenue_share_month5_percent');
            $table->string('revenue_share_month6_percent');
            $table->string('revenue_share_month7_percent');
            $table->string('revenue_share_month8_percent');
            $table->string('revenue_share_month9_percent');
            $table->string('revenue_share_month10_percent');
            $table->string('revenue_share_month11_percent');
            $table->string('revenue_share_month12_percent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sim_data');
    }
};

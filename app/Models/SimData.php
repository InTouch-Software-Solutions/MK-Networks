<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimData extends Model
{
    protected $table = "sim_data";


    protected $fillable = [
        'network',
        'product',
        'sim_number',
        'mobile_number',
        'customer_name',
        'sage_customer_number',
        'date_of_sale',
        'customer_buy_price',
        'customer_commission_due',
        'topup2_bonus',
        'topup3_bonus',
        'topup4_bonus',
        'topup5_bonus',
        'topup6_bonus',
        'topup7_bonus',
        'topup8_bonus',
        'topup9_bonus',
        'topup10_bonus',
        'topup11_bonus',
        'topup12_bonus',
        'end_user_first_name',
        'end_user_last_name',
        'end_user_address',
        'end_user_postcode',
        'invoiced',
        'master_carton',
        'revenue_share_month1_percent',
        'revenue_share_month2_percent',
        'revenue_share_month3_percent',
        'revenue_share_month4_percent',
        'revenue_share_month5_percent',
        'revenue_share_month6_percent',
        'revenue_share_month7_percent',
        'revenue_share_month8_percent',
        'revenue_share_month9_percent',
        'revenue_share_month10_percent',
        'revenue_share_month11_percent',
        'revenue_share_month12_percent',
        'is_assigned',

    ];
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

Class Order extends Model{
    protected $table = 'orders';
    protected $fillable = [
        'type',
        'delivery_code',
        'request_kv',
        'shop_code',
        'order_number',
        'response_kv',
        'city_kv_id',
        'district_kv_id',
        'ward_kv_id',
        'to_address',
        'to_fullname',
        'to_mobile',
    ];
}

<?php
namespace App\Http\Controllers;
use App\Models\Mongodb\Logging;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class LogController extends Controller{

    public function index(){
        $response = Http::get('https://cpanel-shipping.kiotapi.com/api/setting/index', [
            'page' => 1,
            'type'=>['createOrder']
        ]);

        $response = json_decode($response->body());
        $loggings = $response->loggings;
        $data = $loggings->data;
        if(!empty($data)){
            foreach ($data as $key=>$value){
                $log = new Order();
                $log->type = 'createOrder';
                $log->delivery_code = $value->delivery_code;
                $log->shop_code = $value->shop_code;
                $log->request_kv = json_encode($value->request_kv,true);
                $log->order_number = $value->response_kv->data->ORDER_NUMBER;
                $log->response_kv = json_encode($value->response_kv,true);
                $log->save();
            }
        }
        dd($data);

    }

}
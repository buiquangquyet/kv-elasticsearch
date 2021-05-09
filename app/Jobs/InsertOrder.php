<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Order;

class InsertOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    private $page;
    private $date;
    private $ptypeage;
    public function __construct($page,$date,$type)
    {
        $this->page = $page;
        $this->date = $date;
        $this->type = $type;
    }

    public function handle()
    {
        $response = Http::get('https://cpanel-shipping.kiotapi.com/api/setting/index', [
            'page' => $this->page,
            'type'=> $this->type,
            'date-range'=> $this->date
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
                $log->request_kv = isset($value->request_kv)?json_encode($value->request_kv,true):'';
                $log->order_number = isset($value->response_kv->data->ORDER_NUMBER)?$value->response_kv->data->ORDER_NUMBER:'';
                $log->response_kv = isset($value->response_kv)?json_encode($value->response_kv,true):'';
                $log->save();
            }
        }
        sleep(3);
    }
}

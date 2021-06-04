<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use Carbon\Carbon;


class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $podcast;
    protected $page;
    protected $date;
    

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($page,$date)
    {
        $this->page = $page;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::timeout(20)->get('https://cpanel-shipping.kiotapi.com/api/setting/index', [
            'page' =>  $this->page,
            'date-range' => $this->date
        ]);

        $response = json_decode($response->body());
        $loggings = $response->loggings;
        $data = $loggings->data;
        $dataInsert = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                
                if(isset($value->type) && $value->type == 'createOrder'){
                    $log['type'] = 'createOrder';
                    $log['delivery_code'] = $value->delivery_code;
                    $log['shop_code'] = $value->shop_code;
                    $log['request_kv'] = isset($value->request_kv) ? json_encode($value->request_kv, true) : '';
                    $log['order_number'] = isset($value->response_kv->data->ORDER_NUMBER) ? $value->response_kv->data->ORDER_NUMBER : '';
                    $log['response_kv'] = isset($value->response_kv) ? json_encode($value->response_kv, true) : '';
                    $log['district_kv_id'] = isset($value->request_kv->RECEIVER_LOCATION_ID) ? $value->request_kv->RECEIVER_LOCATION_ID : "";
                    $log['ward_kv_id'] = isset($value->request_kv->RECEIVER_WARD_ID) ? $value->request_kv->RECEIVER_WARD_ID : "";
                    $log['to_address'] = isset($value->request_kv->RECEIVER_ADDRESS) ? $value->request_kv->RECEIVER_ADDRESS : "";
                    $log['to_fullname'] = isset($value->request_kv->RECEIVER_FULLNAME) ? $value->request_kv->RECEIVER_FULLNAME : "";
                    $log['to_mobile'] = isset($value->request_kv->RECEIVER_MOBILE) ? $value->request_kv->RECEIVER_MOBILE : "";
                    $log['created_at'] = Carbon::now()->format('Y-m-d h:i:s');
                    $log['updated_at'] = Carbon::now()->format('Y-m-d h:i:s');
                    $dataInsert[] = $log;
                }
                
            }
        }
        if(!empty($dataInsert)){
            Order::insert($dataInsert);
        }
        
    }
}

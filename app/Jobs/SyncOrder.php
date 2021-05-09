<?php

namespace App\Jobs;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $orders = Order::select('id')->whereNull('district_kv_id')->limit(5000)->get()->pluck('id');
        foreach ($orders as $key=>$value){
            $order = Order::where('id',$value)->first();
            $requestKv = json_decode($order->request_kv);
            $order->district_kv_id = isset($requestKv->RECEIVER_LOCATION_ID) ? $requestKv->RECEIVER_LOCATION_ID : null;
            $order->ward_kv_id = isset($requestKv->RECEIVER_WARD_ID) ? $requestKv->RECEIVER_WARD_ID : null;
            $order->to_address = isset($requestKv->RECEIVER_ADDRESS) ? $requestKv->RECEIVER_ADDRESS : null;
            $order->to_fullname = isset($requestKv->RECEIVER_FULLNAME) ? $requestKv->RECEIVER_FULLNAME : "";
            $order->to_mobile = isset($requestKv->RECEIVER_MOBILE) ? $requestKv->RECEIVER_MOBILE : "";
            $order->updated_at = Carbon::now()->format('Y-m-d h:i:s');
            $order->save();
        }

    }
}

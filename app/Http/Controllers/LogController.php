<?php
namespace App\Http\Controllers;
use App\Jobs\InsertOrder;
use App\Jobs\SendEmail;
use App\Jobs\SendEmailJob;
use App\Jobs\ProcessPodcast;
use App\Jobs\SyncOrder;
use App\Models\Mongodb\Logging;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class LogController extends Controller{

    public function index(){
        $dateRange = '01/03/2021 - 01/03/2021';
        $type = ['createOrder'];

        $response = Http::get('https://cpanel-shipping.kiotapi.com/api/setting/index', [
            'page' => 1,
            'type' => $type,
            'date-range'=> $dateRange
        ]);

        $response = json_decode($response->body());
        $loggings = $response->loggings;
        $data = $loggings->data;
        $totalPage = $loggings->last_page;

        for($i=1; $i<=$totalPage; $i++){
            dispatch(new InsertOrder($i,$dateRange,$type));
        }
        echo $dateRange.__METHOD__;die();

    }

    public function sendEmail()
    {
        dispatch(new SendEmailJob());

        echo 'email sent';
    }
    public function syncFullAddressBill(){
        $orders = Order::select('id')->whereNull('district_kv_id')->limit(5000)->get()->pluck('id');
        $this->dispatch(new SyncOrder($orders));
    }

}

<?php
namespace App\Http\Controllers;
use App\Jobs\InsertOrder;
use App\Jobs\SendEmail;
use App\Jobs\SendEmailJob;
use App\Jobs\ProcessPodcast;
use App\Jobs\CaculateTotalOrderByDate;
use App\Jobs\SyncOrder;
use App\Models\Mongodb\Logging;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class LogController extends Controller{

    public function index(){
        $dateRange = '10/03/2021 - 10/03/2021';
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

    public function podcast(){
        $dateRange = '10/01/2021 - 10/01/2021';


        $response = Http::get('https://cpanel-shipping.kiotapi.com/api/setting/index', [
            'date-range'=> $dateRange
        ]);

        $response = json_decode($response->body());
        $loggings = $response->loggings;
        $data = $loggings->data;
        $totalPage = $loggings->last_page;
        for($i=1; $i<=$totalPage; $i++){
            dispatch(new ProcessPodcast($i,$dateRange));
        }
        echo $dateRange.__METHOD__;die();
    }


    public function syncFullAddressBill(){

        $this->dispatch(new SyncOrder());
    }

    public function createDateRangeArray($strDateFrom,$strDateTo)
    {
        $aryRange = [];

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('d/m/Y', $iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('d/m/Y', $iDateFrom));
            }
        }
        return $aryRange;
    }

    public function crawShip($startDate,$endDate){
        $rs = $this->createDateRangeArray($startDate,$endDate);
        foreach($rs as $key=>$value){
            dispatch(new CaculateTotalOrderByDate($value));
        }
        dd($rs);
    }

}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CaculateTotalOrderByDate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = $date." - ".$date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::get('https://cpanel-shipping.kiotapi.com/api/setting/index', [
            'date-range'=> $this->date
        ]);

        $response = json_decode($response->body());
        $loggings = $response->loggings;
        $data = $loggings->data;
        $totalPage = $loggings->last_page;
        for($i=1; $i<=$totalPage; $i++){
            dispatch(new ProcessPodcast($i,$this->date));
        }
    }
}

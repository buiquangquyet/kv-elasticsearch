<?php

namespace App\Models\Mongodb;

class Logging extends \Moloquent
{


    protected $connection = 'mongodb';
    protected $collection = 'logging';
    public $timestamps = false;
}

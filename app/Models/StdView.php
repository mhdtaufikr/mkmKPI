<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StdView extends Model
{
    protected $table = 'std_view';

    // If your view doesn't have an id column, set the primary key to null and disable auto-incrementing
    protected $primaryKey = null;
    public $incrementing = false;

    // If your view doesn't have timestamp columns, disable them
    public $timestamps = false;

    // Define the columns as fillable
    protected $fillable = [
        'Date',
        'Section',
        'Shop',
        'Model',
        'OTDP',
        'Downtime_Machine_PLN_Off',
        'Downtime_Tooling',
        'Downtime_Delay_Supply',
        'Downtime_Quality_Material',
        'Downtime_Manpower_Repair_IDLE',
        'FTT',
    ];
}

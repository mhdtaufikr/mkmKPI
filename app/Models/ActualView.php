<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActualView extends Model
{
    protected $table = 'actual_view';

    // If your view doesn't have an id column, set the primary key to null and disable auto-incrementing
    protected $primaryKey = null;
    public $incrementing = false;

    // If your view doesn't have timestamp columns, disable them
    public $timestamps = false;
}


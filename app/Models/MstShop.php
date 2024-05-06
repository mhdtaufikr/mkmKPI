<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstShop extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function section()
    {
        return $this->belongsTo(MstChecksheetSection::class, 'section_id');
    }
}

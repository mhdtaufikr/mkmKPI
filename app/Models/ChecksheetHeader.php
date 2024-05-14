<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecksheetHeader extends Model
{
    public function mstChecksheetSection()
    {
        return $this->belongsTo(MstChecksheetSection::class, 'section_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo(MstChecksheetSection::class, 'section_id', 'id');
    }
}


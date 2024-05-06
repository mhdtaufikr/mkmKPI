<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstModel extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function section()
    {
        return $this->belongsTo(MstShop::class, 'shop_id');
    }
}

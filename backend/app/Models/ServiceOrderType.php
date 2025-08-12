<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrderType extends Model
{
    protected $fillable = ['name','is_default'];
}

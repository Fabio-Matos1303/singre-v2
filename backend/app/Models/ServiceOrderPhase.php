<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrderPhase extends Model
{
    protected $fillable = ['name','is_default','points','generates_commission'];
}

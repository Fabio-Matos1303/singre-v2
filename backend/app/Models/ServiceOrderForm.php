<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrderForm extends Model
{
    protected $fillable = ['name','is_default','is_warranty','generates_commission','require_equipment'];
}

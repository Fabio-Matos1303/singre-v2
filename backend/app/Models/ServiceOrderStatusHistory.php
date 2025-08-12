<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderStatusHistory extends Model
{
    protected $fillable = [
        'service_order_id', 'user_id', 'from_status', 'to_status', 'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

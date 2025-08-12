<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    protected $fillable = [
        'client_id', 'equipment_id', 'status', 'total', 'notes', 'opened_at', 'closed_at',
        'code', 'sequence_year', 'sequence_number',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $with = ['client'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderType::class, 'type_id');
    }
    public function form(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderForm::class, 'form_id');
    }
    public function phase(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderPhase::class, 'phase_id');
    }
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderConsultation::class, 'consultation_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'service_order_product')
            ->withPivot(['quantity','unit_price','total'])
            ->withTimestamps();
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_order_service')
            ->withPivot(['quantity','unit_price','total'])
            ->withTimestamps();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ServiceOrderStatusHistory::class)->orderBy('changed_at');
    }
}

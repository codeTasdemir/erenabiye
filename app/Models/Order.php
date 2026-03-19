<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'payment_method',
        'currency',
        'subtotal',
        'discount_amount',
        'shipping_amount',
        'total',
        'coupon_id',
        'paytr_merchant_oid',
        'notes',
        'shipping_name',
        'shipping_phone',
        'shipping_city',
        'shipping_district',
        'shipping_address',
        'cargo_company',
        'cargo_tracking_number',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total'           => 'decimal:2',
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
    ];

    public static array $statusLabels = [
        'pending'    => 'Beklemede',
        'confirmed'  => 'Onaylandı',
        'processing' => 'Hazırlanıyor',
        'shipped'    => 'Kargoya Verildi',
        'delivered'  => 'Teslim Edildi',
        'cancelled'  => 'İptal Edildi',
        'refunded'   => 'İade Edildi',
    ];

    public static array $paymentStatusLabels = [
        'pending'  => 'Ödeme Bekleniyor',
        'paid'     => 'Ödendi',
        'failed'   => 'Başarısız',
        'refunded' => 'İade Edildi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }


    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->order_number)) {
                $model->order_number = 'EA' . date('Ymd') . strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }
}
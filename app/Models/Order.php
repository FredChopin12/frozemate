<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'code', 'user_id', 'customer_name', 'phone', 'address',
        'payment_method', 'notes', 'total', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Kode pesanan mengikuti ID: #ORD0001, #ORD0002, ...
        static::created(function (Order $order) {
            if (! $order->code) {
                $order->code = 'ORD'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
                $order->timestamps = false;
                $order->saveQuietly();
                $order->timestamps = true;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /** Pesanan yang dihitung sebagai penjualan (tidak dibatalkan). */
    public function scopeCounted(Builder $query): Builder
    {
        return $query->where('status', '!=', OrderStatus::Cancelled->value);
    }

    public function paymentLabel(): string
    {
        return config("frozemate.payments.{$this->payment_method}.label", $this->payment_method);
    }

    public function itemCount(): int
    {
        return (int) $this->items->sum('quantity');
    }
}

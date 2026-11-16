<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Ready = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Processing => 'Diproses',
            self::Ready => 'Siap',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /** Penjelasan singkat untuk customer. */
    public function description(): string
    {
        return match ($this) {
            self::Pending => 'Pesanan masuk, menunggu konfirmasi toko.',
            self::Processing => 'Toko sedang menyiapkan pesananmu.',
            self::Ready => 'Pesanan sudah dikemas dan siap diantar / diambil.',
            self::Completed => 'Pesanan sudah diterima. Selamat menikmati!',
            self::Cancelled => 'Pesanan dibatalkan.',
        };
    }

    /** Status berikutnya dalam alur Pending → Processing → Ready → Completed. */
    public function next(): ?self
    {
        return match ($this) {
            self::Pending => self::Processing,
            self::Processing => self::Ready,
            self::Ready => self::Completed,
            default => null,
        };
    }

    public function nextActionLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Proses pesanan',
            self::Processing => 'Tandai siap',
            self::Ready => 'Selesaikan',
            default => null,
        };
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Pending, self::Processing, self::Ready], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    /** Urutan langkah untuk progress tracker (tanpa Cancelled). */
    public static function flow(): array
    {
        return [self::Pending, self::Processing, self::Ready, self::Completed];
    }

    public function step(): int
    {
        return (int) array_search($this, self::flow(), true);
    }
}

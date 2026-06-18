<?php

namespace App\Models;

use Database\Factories\TelegramUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    /** @use HasFactory<TelegramUserFactory> */
    use HasFactory;

    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'is_authorized',
        'role',
        'last_interaction_at',
    ];

    protected function casts(): array
    {
        return [
            'is_authorized' => 'boolean',
            'last_interaction_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_authorized && $this->role === 'admin';
    }
}

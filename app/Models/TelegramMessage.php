<?php

namespace App\Models;

use Database\Factories\TelegramMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramMessage extends Model
{
    /** @use HasFactory<TelegramMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'telegram_user_id',
        'telegram_chat_id',
        'telegram_message_id',
        'text',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
        ];
    }

    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class);
    }
}

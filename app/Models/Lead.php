<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'user_id',
        'name',
        'phone',
        'email',
        'message',
        'status',
        'admin_notes',
        'ip_address',
        'user_agent',
        'contacted_at',
        'closed_at',
    ];

    /**
     * The default pipeline statuses a lead moves through.
     *
     * @var list<string>
     */
    public const STATUSES = ['new', 'contacted', 'visited', 'closed', 'lost'];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInLastDays($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark the lead as contacted, recording when the broker first reached out.
     */
    public function markAsContacted(): bool
    {
        return $this->update([
            'status' => 'contacted',
            'contacted_at' => $this->contacted_at ?? now(),
        ]);
    }

    /**
     * Mark the lead as closed (won or otherwise resolved).
     */
    public function markAsClosed(): bool
    {
        return $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }
}

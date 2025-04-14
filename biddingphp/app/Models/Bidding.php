<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidding extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'title',
        'description',
        'agency_id',
        'bidding_type',
        'modality',
        'status',
        'publication_date',
        'opening_date',
        'closing_date',
        'estimated_value',
        'document_url',
        'contact_email',
        'contact_phone',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'opening_date' => 'datetime',
        'closing_date' => 'datetime',
        'estimated_value' => 'decimal:2',
    ];

    // Relacionamentos
    public function agency()
    {
        return $this->belongsTo(BiddingAgency::class);
    }

    public function items()
    {
        return $this->hasMany(BiddingItem::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'related');
    }

    // Escopos
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['published', 'in_progress']);
    }

    public function scopeClosingSoon($query, $days = 7)
    {
        return $query->whereDate('closing_date', '>=', now())
                    ->whereDate('closing_date', '<=', now()->addDays($days));
    }

    // Métodos de utilidade
    public function isActive()
    {
        return in_array($this->status, ['published', 'in_progress']);
    }

    public function canSubmitProposal()
    {
        return $this->isActive() && now()->lt($this->closing_date);
    }

    public function daysUntilClosing()
    {
        if ($this->closing_date && now()->lt($this->closing_date)) {
            return now()->diffInDays($this->closing_date);
        }

        return 0;
    }

    public function totalEstimatedValue()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->estimated_unit_price;
        });
    }
}

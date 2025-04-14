<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'bidding_id',
        'user_id',
        'status',
        'submission_date',
        'total_value',
        'discount_percentage',
        'notes',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'total_value' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
    ];

    // Relacionamentos
    public function bidding()
    {
        return $this->belongsTo(Bidding::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ProposalItem::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'related');
    }

    // Escopos
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    // Métodos de utilidade
    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function canEdit()
    {
        return $this->status === 'draft' &&
               $this->bidding &&
               $this->bidding->canSubmitProposal();
    }

    public function calculateTotalValue()
    {
        $total = $this->items->sum('total_price');
        return $total;
    }

    public function calculateDiscountPercentage()
    {
        if (!$this->bidding) {
            return 0;
        }

        $originalTotal = $this->bidding->totalEstimatedValue();
        $proposalTotal = $this->calculateTotalValue();

        if ($originalTotal > 0) {
            return (($originalTotal - $proposalTotal) / $originalTotal) * 100;
        }

        return 0;
    }

    public function updateTotals()
    {
        $this->total_value = $this->calculateTotalValue();
        $this->discount_percentage = $this->calculateDiscountPercentage();
        $this->save();
    }

    public function submit()
    {
        if (!$this->canEdit()) {
            return false;
        }

        $this->updateTotals();
        $this->status = 'submitted';
        $this->submission_date = now();
        return $this->save();
    }
}

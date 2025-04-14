<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiddingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bidding_id',
        'item_number',
        'description',
        'quantity',
        'unit',
        'estimated_unit_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_unit_price' => 'decimal:2',
    ];

    /**
     * Relacionamento com a licitação
     */
    public function bidding()
    {
        return $this->belongsTo(Bidding::class);
    }

    /**
     * Relacionamento com os itens de proposta
     */
    public function proposalItems()
    {
        return $this->hasMany(ProposalItem::class);
    }

    /**
     * Calcula o valor total estimado do item
     */
    public function totalEstimatedValue()
    {
        return $this->quantity * $this->estimated_unit_price;
    }

    /**
     * Retorna o menor preço oferecido para este item em todas as propostas
     */
    public function getLowestOfferedPrice()
    {
        $lowestItem = $this->proposalItems()
            ->whereHas('proposal', function($query) {
                $query->whereIn('status', ['submitted', 'won', 'lost']);
            })
            ->orderBy('unit_price', 'asc')
            ->first();

        return $lowestItem ? $lowestItem->unit_price : null;
    }

    /**
     * Retorna o preço médio oferecido para este item em todas as propostas
     */
    public function getAverageOfferedPrice()
    {
        return $this->proposalItems()
            ->whereHas('proposal', function($query) {
                $query->whereIn('status', ['submitted', 'won', 'lost']);
            })
            ->avg('unit_price');
    }
}

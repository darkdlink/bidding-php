<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'bidding_item_id',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Relacionamento com a proposta
     */
    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * Relacionamento com o item da licitação
     */
    public function bidding_item()
    {
        return $this->belongsTo(BiddingItem::class, 'bidding_item_id');
    }
    
    /**
     * Calcula o desconto em relação ao preço estimado original
     */
    public function getDiscountPercentage()
    {
        $originalPrice = $this->bidding_item->estimated_unit_price;
        
        if ($originalPrice > 0) {
            return (($originalPrice - $this->unit_price) / $originalPrice) * 100;
        }
        
        return 0;
    }
    
    /**
     * Calcula o desconto em valor absoluto
     */
    public function getDiscountValue()
    {
        $originalPrice = $this->bidding_item->estimated_unit_price;
        $quantity = $this->bidding_item->quantity;
        
        $originalTotal = $originalPrice * $quantity;
        $currentTotal = $this->total_price;
        
        return $originalTotal - $currentTotal;
    }
    
    /**
     * Atualiza o preço total com base no preço unitário e quantidade
     */
    public function updateTotalPrice()
    {
        $quantity = $this->bidding_item->quantity;
        $this->total_price = $this->unit_price * $quantity;
        $this->save();
        
        return $this;
    }
}
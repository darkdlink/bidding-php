<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiddingAgency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'website',
        'contact_info',
    ];

    /**
     * Relacionamento com licitações
     */
    public function biddings()
    {
        return $this->hasMany(Bidding::class, 'agency_id');
    }

    /**
     * Relacionamento com configurações de scraping
     */
    public function scrapingConfigs()
    {
        return $this->hasMany(ScrapingConfig::class, 'agency_id');
    }

    /**
     * Retorna as licitações ativas deste órgão
     */
    public function activeBiddings()
    {
        return $this->biddings()->active();
    }

    /**
     * Retorna o total de licitações ganhas deste órgão
     */
    public function wonBiddingsCount()
    {
        return Proposal::whereHas('bidding', function ($query) {
            $query->where('agency_id', $this->id);
        })->where('status', 'won')->count();
    }

    /**
     * Retorna o valor total ganho neste órgão
     */
    public function totalWonValue()
    {
        return Proposal::whereHas('bidding', function ($query) {
            $query->where('agency_id', $this->id);
        })->where('status', 'won')->sum('total_value');
    }
}

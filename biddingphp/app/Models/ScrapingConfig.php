<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapingConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'url',
        'selectors',
        'schedule',
        'last_run',
        'active',
    ];

    protected $casts = [
        'selectors' => 'json',
        'last_run' => 'datetime',
        'active' => 'boolean',
    ];

    /**
     * Relacionamento com o órgão licitante
     */
    public function agency()
    {
        return $this->belongsTo(BiddingAgency::class, 'agency_id');
    }

    /**
     * Relacionamento com os logs de scraping
     */
    public function logs()
    {
        return $this->hasMany(ScrapingLog::class, 'config_id');
    }

    /**
     * Retorna o último log de scraping
     */
    public function lastLog()
    {
        return $this->logs()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Verifica se o último scraping foi bem-sucedido
     */
    public function lastScrapingSuccessful()
    {
        $lastLog = $this->lastLog();
        return $lastLog ? $lastLog->status === 'success' : false;
    }

    /**
     * Retorna o seletor específico
     */
    public function getSelector($name, $default = null)
    {
        $selectors = $this->selectors;
        return $selectors[$name] ?? $default;
    }

    /**
     * Verifica se todos os seletores obrigatórios estão definidos
     */
    public function hasRequiredSelectors()
    {
        $required = [
            'bidding_list_selector',
            'external_id_selector',
            'title_selector',
        ];

        foreach ($required as $selector) {
            if (!isset($this->selectors[$selector])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retorna configurações ativas que precisam ser executadas baseado no agendamento
     */
    public static function getScheduledConfigs()
    {
        return self::where('active', true)
            ->where(function($query) {
                $query->whereNull('last_run')
                      ->orWhereRaw('DATE_ADD(last_run, INTERVAL (CASE
                          WHEN schedule = "hourly" THEN 1
                          WHEN schedule = "daily" THEN 24
                          WHEN schedule = "weekly" THEN 168
                          ELSE 720 END) HOUR) <= NOW()');
            })
            ->get();
    }
}

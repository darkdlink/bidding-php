<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiddingAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_date',
        'total_active_biddings',
        'total_submitted_proposals',
        'total_won_proposals',
        'total_value_won',
        'success_rate',
    ];

    protected $casts = [
        'reference_date' => 'date',
        'total_active_biddings' => 'integer',
        'total_submitted_proposals' => 'integer',
        'total_won_proposals' => 'integer',
        'total_value_won' => 'decimal:2',
        'success_rate' => 'decimal:2',
    ];
    
    /**
     * Obtém análise para uma data específica
     */
    public static function forDate($date)
    {
        return self::whereDate('reference_date', $date)->first();
    }
    
    /**
     * Obtém análises para um período
     */
    public static function forPeriod($startDate, $endDate)
    {
        return self::whereBetween('reference_date', [$startDate, $endDate])
                  ->orderBy('reference_date')
                  ->get();
    }
    
    /**
     * Compara com análise anterior
     */
    public function compareWithPrevious()
    {
        $previousDate = $this->reference_date->copy()->subDay();
        $previous = self::forDate($previousDate);
        
        if (!$previous) {
            return null;
        }
        
        $result = [];
        
        // Cálculo das diferenças
        foreach ($this->getAttributes() as $key => $value) {
            // Ignora campos que não são métricas
            if (in_array($key, ['id', 'reference_date', 'created_at', 'updated_at'])) {
                continue;
            }
            
            $previousValue = $previous->{$key} ?? 0;
            
            // Evita divisão por zero
            if ($previousValue == 0) {
                $result[$key] = [
                    'current' => $value,
                    'previous' => $previousValue,
                    'difference' => 0,
                    'percentage_change' => 0,
                ];
                continue;
            }
            
            $difference = $value - $previousValue;
            $percentageChange = ($difference / $previousValue) * 100;
            
            $result[$key] = [
                'current' => $value,
                'previous' => $previousValue,
                'difference' => $difference,
                'percentage_change' => $percentageChange,
            ];
        }
        
        return $result;
    }
    
    /**
     * Retorna estatísticas para um intervalo de datas específico
     */
    public static function getStatisticsForPeriod($startDate, $endDate)
    {
        $stats = self::selectRaw('
            AVG(total_active_biddings) as avg_active_biddings,
            AVG(total_submitted_proposals) as avg_submitted_proposals,
            AVG(total_won_proposals) as avg_won_proposals,
            AVG(total_value_won) as avg_value_won,
            AVG(success_rate) as avg_success_rate,
            MAX(total_active_biddings) as max_active_biddings,
            MAX(total_submitted_proposals) as max_submitted_proposals,
            MAX(total_won_proposals) as max_won_proposals,
            MAX(total_value_won) as max_value_won,
            MAX(success_rate) as max_success_rate
        ')
        ->whereBetween('reference_date', [$startDate, $endDate])
        ->first();
        
        return $stats;
    }
}
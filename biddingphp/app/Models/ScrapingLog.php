<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapingLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'config_id',
        'start_time',
        'end_time',
        'status',
        'items_found',
        'items_processed',
        'error_message',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'items_found' => 'integer',
        'items_processed' => 'integer',
    ];

    /**
     * Relacionamento com a configuração de scraping
     */
    public function config()
    {
        return $this->belongsTo(ScrapingConfig::class, 'config_id');
    }

    /**
     * Calcula o tempo de execução em segundos
     */
    public function getExecutionTimeAttribute()
    {
        if ($this->end_time && $this->start_time) {
            return $this->end_time->diffInSeconds($this->start_time);
        }

        return null;
    }

    /**
     * Retorna o tempo de execução formatado
     */
    public function getFormattedExecutionTimeAttribute()
    {
        $seconds = $this->execution_time;

        if (is_null($seconds)) {
            return 'Em andamento';
        }

        if ($seconds < 60) {
            return $seconds . ' segundos';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return $minutes . ' min ' . $remainingSeconds . ' seg';
    }

    /**
     * Retorna a taxa de sucesso
     */
    public function getSuccessRateAttribute()
    {
        if ($this->items_found > 0) {
            return ($this->items_processed / $this->items_found) * 100;
        }

        return 0;
    }

    /**
     * Define a classe CSS de acordo com o status
     */
    public function getStatusClassAttribute()
    {
        switch ($this->status) {
            case 'success':
                return 'success';
            case 'partial':
                return 'warning';
            case 'failed':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Marca o log como concluído
     */
    public function complete($status = 'success', $errorMessage = null)
    {
        $this->end_time = now();
        $this->status = $status;

        if ($errorMessage) {
            $this->error_message = $errorMessage;
        }

        $this->save();

        return $this;
    }
}

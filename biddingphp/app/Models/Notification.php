<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'related_type',
        'related_id',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento polimórfico com o elemento relacionado
     */
    public function related()
    {
        if ($this->related_type === 'bidding') {
            return $this->belongsTo(Bidding::class, 'related_id');
        } else if ($this->related_type === 'proposal') {
            return $this->belongsTo(Proposal::class, 'related_id');
        }
        
        return null;
    }
    
    /**
     * Retorna o URL do elemento relacionado
     */
    public function getRelatedUrl()
    {
        if ($this->related_type === 'bidding') {
            return route('biddings.show', $this->related_id);
        } else if ($this->related_type === 'proposal') {
            return route('proposals.show', $this->related_id);
        }
        
        return '#';
    }
    
    /**
     * Marca a notificação como lida
     */
    public function markAsRead()
    {
        $this->read = true;
        $this->save();
        
        return $this;
    }
    
    /**
     * Retorna o ícone apropriado para o tipo de notificação
     */
    public function getIconAttribute()
    {
        switch ($this->type) {
            case 'bidding_new':
                return 'fa-file-alt';
            case 'bidding_update':
                return 'fa-edit';
            case 'bidding_closing':
                return 'fa-clock';
            case 'bidding_result':
                return 'fa-trophy';
            case 'proposal_submitted':
                return 'fa-paper-plane';
            case 'proposal_won':
                return 'fa-award';
            case 'proposal_lost':
                return 'fa-times-circle';
            default:
                return 'fa-bell';
        }
    }
    
    /**
     * Retorna a classe de cor para o tipo de notificação
     */
    public function getColorClassAttribute()
    {
        switch ($this->type) {
            case 'bidding_new':
                return 'primary';
            case 'bidding_update':
                return 'info';
            case 'bidding_closing':
                return 'warning';
            case 'bidding_result':
                return 'dark';
            case 'proposal_submitted':
                return 'info';
            case 'proposal_won':
                return 'success';
            case 'proposal_lost':
                return 'danger';
            default:
                return 'secondary';
        }
    }
    
    /**
     * Retorna as notificações não lidas de um usuário
     */
    public static function getUnreadForUser($userId, $limit = 10)
    {
        return self::where('user_id', $userId)
                 ->where('read', false)
                 ->orderBy('created_at', 'desc')
                 ->limit($limit)
                 ->get();
    }
    
    /**
     * Retorna a contagem de notificações não lidas
     */
    public static function unreadCount($userId)
    {
        return self::where('user_id', $userId)
                 ->where('read', false)
                 ->count();
    }
    
    /**
     * Marca todas as notificações de um usuário como lidas
     */
    public static function markAllAsRead($userId)
    {
        return self::where('user_id', $userId)
                 ->where('read', false)
                 ->update(['read' => true]);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'related_type',
        'related_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'description',
        'uploaded_by',
    ];

    /**
     * Relacionamento com o usuário que fez o upload
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Relacionamento polimórfico com o elemento relacionado (licitação ou proposta)
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
     * Retorna o URL para download do arquivo
     */
    public function getDownloadUrl()
    {
        if ($this->related_type === 'bidding') {
            return route('biddings.attachments.download', $this);
        } else if ($this->related_type === 'proposal') {
            return route('proposals.attachments.download', $this);
        }
        
        return '#';
    }
    
    /**
     * Verifica se o arquivo realmente existe no storage
     */
    public function fileExists()
    {
        return Storage::exists($this->file_path);
    }
    
    /**
     * Formata o tamanho do arquivo para exibição amigável
     */
    public function getFormattedFileSize()
    {
        $bytes = $this->file_size;
        
        if ($bytes < 1024) {
            return $bytes . ' bytes';
        } else if ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else if ($bytes < 1073741824) {
            return round($bytes / 1048576, 2) . ' MB';
        } else {
            return round($bytes / 1073741824, 2) . ' GB';
        }
    }
    
    /**
     * Retorna um ícone apropriado baseado no tipo de arquivo
     */
    public function getFileIcon()
    {
        $extension = pathinfo($this->file_name, PATHINFO_EXTENSION);
        
        switch (strtolower($extension)) {
            case 'pdf':
                return 'fa-file-pdf';
            case 'doc':
            case 'docx':
                return 'fa-file-word';
            case 'xls':
            case 'xlsx':
            case 'csv':
                return 'fa-file-excel';
            case 'ppt':
            case 'pptx':
                return 'fa-file-powerpoint';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'bmp':
                return 'fa-file-image';
            case 'zip':
            case 'rar':
            case '7z':
                return 'fa-file-archive';
            case 'txt':
                return 'fa-file-alt';
            default:
                return 'fa-file';
        }
    }
}
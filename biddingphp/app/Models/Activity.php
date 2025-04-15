<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'collection',
    ];

    /**
     * Get the subject of the activity.
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that caused the activity.
     */
    public function causer()
    {
        return $this->morphTo();
    }
}

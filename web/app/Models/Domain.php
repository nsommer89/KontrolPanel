<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $fillable = [
        'team_id',
        'webhotel_id',
        'domain',
        'ssl_enabled',
        'cert_path',
        'key_path',
        'valid_until',
        'primary',
    ];

    protected $casts = [
        'ssl_enabled' => 'boolean',
        'primary' => 'boolean',
        'valid_until' => 'datetime',
    ];

    public function webhotel()
    {
        return $this->belongsTo(Webhotel::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->team_id)) {
                $model->team_id = Filament::getTenant()?->id;
            }
        });
    }
}

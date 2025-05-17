<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhotel extends Model
{
    protected $fillable = [
        'name',
        'team_id',
        'php_version',
        'port',
        'enabled',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
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

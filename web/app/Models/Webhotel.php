<?php

namespace App\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhotel extends Model
{
    protected $fillable = [
        'system_user',
        'system_user_uid',
        'system_user_gid',
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

    public function ftpUsers(): HasMany
    {
        return $this->hasMany(FtpUser::class);
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

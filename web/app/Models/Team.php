<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use function Illuminate\Events\queueable;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
    ];

    protected $casts = [
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Active organization';
    }

    public function getFilamentName(): string
    {
        return "{$this->name}";
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            $team->slug = Str::slug($team->name);
        });
    }

    protected static function booted(): void
    {
        static::updated(queueable(function (Team $customer) {
        }));

        static::created(queueable(function (Team $customer) {
        }));
    }
}

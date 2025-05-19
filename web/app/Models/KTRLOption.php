<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KTRLOption extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ktrl_options';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fqdn',
        'port',
        'ktrl_version',
        'pma_port',
    ];
}

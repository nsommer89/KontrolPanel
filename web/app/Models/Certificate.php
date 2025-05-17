<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'webhotel_id',
        'domain',
        'cert_path',
        'key_path',
        'valid_until',
    ];

    public function webhotel()
    {
        return $this->belongsTo(Webhotel::class);
    }
}

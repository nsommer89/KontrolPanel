<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhpVersion extends Model
{
    protected $fillable = [
        'version',
        'default',
        'binary_path',
        'fpm_path',
    ];
}

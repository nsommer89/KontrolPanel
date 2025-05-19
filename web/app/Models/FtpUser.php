<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FtpUser extends Model
{
    protected $fillable = [
        'team_id',
        'webhotel_id',
        'username',
        'password',
        'homedir',
        'shell',
        'uid',
        'gid',
    ];

    protected $hidden = ['password'];

    public $timestamps = true;

    public function webhotel()
    {
        return $this->belongsTo(Webhotel::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    function hashFtpPassword(string $password): string
    {
        return crypt($password, base64_encode(random_bytes(6)));
        // or: return '{md5}' . md5($password);
    }
}

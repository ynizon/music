<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ip extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
    ];

    public function getIp() : string
    {
        return $this->ip;
    }

    public function setIp(string $ip) : void
    {
        $this->ip = $ip;
    }
}

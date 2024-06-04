<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dnsChecks extends Model
{
    use HasFactory;

    protected $fillable = [
        'URL',
        'DNS_records',
        'NS_servers',
        'Error_message',
        'Websites_id',
    ];
    public function website()
    {
        return $this->belongsTo(Website::class);
    }

}

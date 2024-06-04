<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sslChecks extends Model
{
    use HasFactory;
    protected $fillable = [
        'URL',
        'Valid',
        'Expiration_date',
        'Websites_id',
    ];
    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}

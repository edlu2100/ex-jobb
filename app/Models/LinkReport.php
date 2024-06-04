<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'Status_code',
        'Error_message',
        'URL',
        'company_id',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}

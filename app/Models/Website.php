<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'URL',
        'company_id',
        'Scan',
        'DNS',
        'SSL'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function linkReports()
    {
        return $this->hasMany(linkReport::class);
    }
}

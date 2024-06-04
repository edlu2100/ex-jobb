<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable =
        [
            'Company_name',
        ];
        public function websites()
        {
            return $this->hasOne(Website::class);
        }
        public function linkReports()
        {
            return $this->hasMany(linkReport::class);
        }
}

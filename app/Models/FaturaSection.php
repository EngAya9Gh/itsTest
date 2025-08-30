<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class FaturaSection extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'name',
      'increase_percentage',
        'image',
        'type',
        'image_url',
        'status',  
      'is-found',
    ];

    public function faturas(): HasMany
    {
        return $this->hasMany(Fatura::class, 'section_id');
    }
}

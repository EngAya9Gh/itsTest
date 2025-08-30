<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Fatura extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'section_id',
        'name',
        'image', 
      'fatura_no',
        'image_url',
        'basic_price',
        'sale_price',
        'price',
        'status',
        'amount',
      
        'note',
      'is-found',
    ];
 
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'fatura_orders','user_id', 'fatura_id');
    }
    
    public function faturaSection(): BelongsTo
    {
        return $this->belongsTo(FaturaSection::class, 'section_id');
    }
    public function orders()
{
    return $this->hasMany(FaturaOrder::class, 'fatura_id');
}






}

<?php

namespace App\Models;

use App\Models\Favorite;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{    protected $fillable = ['section_id', 'name', 'external_id', 'status','note','type','basic_price', 'sale_price','price','image','image_url'];

    public function category()
    {
        return $this->belongsTo(ServiceCategories::class, 'section_id');
    }


    public function users()
    {
        return $this->belongsToMany(User::class, 'service_orders','user_id', 'service_id');
    }
    public function orders()
    {
        return $this->hasMany(ServiceOrder::class, 'service_id');
    }



}

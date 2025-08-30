<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategories extends Model
{
    protected $fillable = ['name', 'description','image','status','type','image_url','increase_percentage'];

    public function services()
    {
        return $this->hasMany(Service::class, 'section_id');
    }
}

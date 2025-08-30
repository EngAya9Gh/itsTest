<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'program_id',
        'user_id',
        'count',
        'price',
        'note',
        'status'
    ];
    public function profits()
    {
        return $this->morphMany(Profit::class, 'profitable');
    }
}

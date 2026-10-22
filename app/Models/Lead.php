<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shop_name',
        'business_type',
        'phone',
        'message',
        'contacted',
    ];

    protected function casts(): array
    {
        return [
            'contacted' => 'boolean',
        ];
    }
}

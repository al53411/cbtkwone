<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Relasi balik ke Model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
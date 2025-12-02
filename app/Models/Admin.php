<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admins';

    protected $fillable = [
        'user_id',
    ];

    // Relationship: Admin belongs to one User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

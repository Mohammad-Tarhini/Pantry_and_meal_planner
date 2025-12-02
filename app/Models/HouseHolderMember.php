<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseHolderMember extends Model
{
    protected $table = 'householder_members';

    // protected $fillable = [
    //     'user_id',
    // ];

    // Relationship: HouseHolderMember belongs to one User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function household() {
        return $this->belongsTo(Household::class, 'household_id');
    }

}

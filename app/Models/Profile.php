<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'phone_verified_at',
        'address',
        'identity_number',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'phone_verified_at' => 'datetime',
    ];

    /**
     * Relationship: Profile belongs to User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

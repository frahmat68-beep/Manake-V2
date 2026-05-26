<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'image_path',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Relationship: Image belongs to Equipment.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Organization\OrganizationStructure;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenPosition extends Model
{
    protected $table = "open_positions";
    protected $fillable = [
        'position_id'
    ];
    
    public function PositionBelongsToOrgStructure(): BelongsTo
    {
        return $this->belongsTo(OrganizationStructure::class, 'position_id', 'id');
    }
}
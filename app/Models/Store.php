<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Store extends Model
{
    use HasFactory;
    use HasSpatial;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'coordinates',
        'status',
        'store_type_id',
        'max_delivery_distance_in_meters',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'coordinates' => Point::class,
        'status' => Status::class,
        'store_type_id' => 'string',
        'max_delivery_distance_in_meters' => 'integer',
    ];
}

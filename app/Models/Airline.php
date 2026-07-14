<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    protected $table = 'aerolineas';
    protected $primaryKey = 'id_aerolinea';
    public $timestamps = false;

    protected $fillable = [
        'iata',
        'nombre'
    ];
}

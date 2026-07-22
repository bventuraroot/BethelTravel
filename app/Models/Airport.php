<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    protected $table = 'aeropuertos';
    protected $primaryKey = 'id_aeropuerto';
    public $timestamps = false;

    protected $fillable = [
        'iata',
        'ciudad',
        'pais',
        'continente',
        'subregion'
    ];
}

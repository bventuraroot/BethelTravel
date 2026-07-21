<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'client_id',
        'client_name',
        'client_email',
        'client_phone',
        'title',
        'subtitle',
        'banner_images',
        'includes',
        'hotels_grid',
        'flights',
        'notes',
        'status',
        'user_id'
    ];

    protected $casts = [
        'banner_images' => 'array',
        'includes' => 'array',
        'hotels_grid' => 'array',
        'flights' => 'array',
        'notes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con la empresa emisora
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relación con el cliente (opcional)
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación con el usuario/agente que la creó
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el nombre del cliente (registrado o prospecto)
     */
    public function getCustomerNameAttribute()
    {
        if ($this->client) {
            $name = trim(($this->client->firstname ?? '') . ' ' . ($this->client->firstlastname ?? ''));
            return !empty($name) ? $name : $this->client->name_contribuyente;
        }
        return $this->client_name ?: 'Prospecto';
    }
}

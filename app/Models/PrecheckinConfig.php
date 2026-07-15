<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecheckinConfig extends Model
{
    use HasFactory;

    protected $table = 'precheckin_configs';

    protected $fillable = [
        'company_id',
        'dias_antes',
        'enviar_cliente',
        'email_agencia',
        'asunto',
        'cuerpo',
        'active'
    ];

    protected $casts = [
        'enviar_cliente' => 'boolean',
        'active' => 'boolean',
        'dias_antes' => 'integer',
    ];

    /**
     * Relación con la empresa
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

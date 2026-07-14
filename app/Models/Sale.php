<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'nu_unico',
        'nu_doc',
        'acuenta',
        'parent_sale_id',
        'is_parent',
        'state',
        'state_credit',
        'totalamount',
        'retencion_agente',
        'waytopay',
        'typesale',
        'date',
        'user_id',
        'typedocument_id',
        'client_id',
        'company_id',
        'provider_id',
        'json',
        'doc_related',
        'id_contingencia',
        'codigoGeneracion',
        'motivo'
    ];

    protected $casts = [
        'date' => 'date',
        'state' => 'boolean',
        'state_credit' => 'boolean',
        'totalamount' => 'decimal:2'
    ];

    public function details()
    {
        return $this->hasMany(Salesdetail::class, 'sale_id');
    }

    public function salesdetails()
    {
        return $this->hasMany(Salesdetail::class, 'sale_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function typedocument()
    {
        return $this->belongsTo(Typedocument::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function dte()
    {
        return $this->hasOne(Dte::class, 'sale_id');
    }

    // Relaciones para ventas padre/hijo
    public function parentSale()
    {
        return $this->belongsTo(Sale::class, 'parent_sale_id');
    }

    public function childSales()
    {
        return $this->hasMany(Sale::class, 'parent_sale_id')->orderBy('id');
    }

    // Helpers
    public function isParent()
    {
        return $this->is_parent == 1;
    }

    public function hasChildren()
    {
        return $this->childSales()->count() > 0;
    }

    public function isChild()
    {
        return !is_null($this->parent_sale_id);
    }

    // Scope para filtrar solo ventas padre en el index
    public function scopeParentsOnly($query)
    {
        return $query->where(function($q) {
            $q->where('is_parent', 1)
              ->orWhereNull('parent_sale_id');
        });
    }
}

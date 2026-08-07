<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'tax_number',
        'email',
        'phone_number',
        'address',
        'street_name',
        'additional_street_name',
        'building_number',
        'plot_identification',
        'city_subdivision_name',
        'city',
        'postal_code',
        'country_id',
        'country_subentity',
        'is_tax_exempt',
        'is_supplier',
        'is_enabled',
        'due_date_period',
        'price_list_id',
    ];

    protected $casts = [
        'is_tax_exempt'   => 'boolean',
        'is_supplier'     => 'boolean',
        'is_enabled'      => 'boolean',
        'due_date_period' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function customerDiscounts()
    {
        return $this->hasMany(CustomerDiscount::class);
    }

    public function loyaltyCards()
    {
        return $this->hasMany(LoyaltyCard::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function posOrders()
    {
        return $this->hasMany(PosOrder::class);
    }

    public function stockControls()
    {
        return $this->hasMany(StockControl::class);
    }
}

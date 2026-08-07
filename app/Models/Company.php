<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Company extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'companies';

    protected $fillable = [
        'tenant_id',
        'name',
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
        'tax_number',
        'email',
        'phone_number',
        'bank_account_number',
        'bank_details',
        'logo',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'branch_code',
        'plan_type',
        'business_type',
        'address',
        'phone',
        'is_active',
        'is_headquarters',
        'trial_ends_at',
        'subscription_ends_at',
        'settings',
        'company_id',
        'parent_branch_id',
        'is_company',
        'country',
        'division',
        'district',
        'city',
        'latitude',
        'longitude',
        'manager_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_headquarters' => 'boolean',
            'is_company' => 'boolean',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'settings' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'status' => 'string',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'company_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Tenant::class, 'parent_branch_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function branchSettings(): HasMany
    {
        return $this->hasMany(BranchSetting::class, 'branch_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function documentCategories(): HasMany
    {
        return $this->hasMany(DocumentCategory::class);
    }

    public function documentTypes(): HasMany
    {
        return $this->hasMany(DocumentType::class);
    }

    public function paymentTypes(): HasMany
    {
        return $this->hasMany(PaymentType::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class);
    }

    public function voidReasons(): HasMany
    {
        return $this->hasMany(VoidReason::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    public function posOrderItems(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function posVoids(): HasMany
    {
        return $this->hasMany(PosVoid::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function startingCash(): HasMany
    {
        return $this->hasMany(StartingCash::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function documentItemTaxes(): HasMany
    {
        return $this->hasMany(DocumentItemTax::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

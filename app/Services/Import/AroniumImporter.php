<?php

namespace App\Services\Import;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Tax;
use App\Models\Warehouse;
use App\Models\PaymentType;
use App\Models\DocumentType;
use App\Models\DocumentCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;

class AroniumImporter
{
    private string $sqlitePath;
    private string $tenantId;
    private PDO $source;
    private array $idMap = [];

    public function import(string $sqlitePath, string $tenantId): array
    {
        $this->sqlitePath = $sqlitePath;
        $this->tenantId = $tenantId;

        if (!file_exists($this->sqlitePath)) {
            throw new \RuntimeException("SQLite database not found at: {$this->sqlitePath}");
        }

        $this->source = new PDO("sqlite:{$this->sqlitePath}");
        $this->source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $summary = [];

        DB::beginTransaction();

        try {
            $summary['product_groups'] = $this->importProductGroups();
            $summary['taxes'] = $this->importTaxes();
            $summary['warehouses'] = $this->importWarehouses();
            $summary['payment_types'] = $this->importPaymentTypes();
            $summary['document_types'] = $this->importDocumentTypes();
            $summary['document_categories'] = $this->importDocumentCategories();
            $summary['customers'] = $this->importCustomers();
            $summary['products'] = $this->importProducts();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new \RuntimeException("Import failed: {$e->getMessage()}", 0, $e);
        }

        return $summary;
    }

    private function importProductGroups(): int
    {
        $stmt = $this->source->query("SELECT id, name, parent_id, sort_order FROM product_groups");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;

        foreach ($rows as $row) {
            $newId = (string) Str::uuid();
            $this->idMap['product_groups'][(int) $row['id']] = $newId;

            ProductGroup::create([
                'id' => $newId,
                'tenant_id' => $this->tenantId,
                'name' => $row['name'],
                'parent_group_id' => isset($this->idMap['product_groups'][(int) $row['parent_id']])
                    ? $this->idMap['product_groups'][(int) $row['parent_id']]
                    : null,
                'rank' => (int) ($row['sort_order'] ?? 0),
            ]);
            $count++;
        }

        return $count;
    }

    private function importTaxes(): int
    {
        $stmt = $this->source->query("SELECT id, name, rate, is_enabled FROM taxes");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;

        foreach ($rows as $row) {
            $newId = (string) Str::uuid();
            $this->idMap['taxes'][(int) $row['id']] = $newId;

            Tax::create([
                'id' => $newId,
                'tenant_id' => $this->tenantId,
                'name' => $row['name'],
                'rate' => (float) ($row['rate'] ?? 0),
                'is_enabled' => (bool) ($row['is_enabled'] ?? true),
            ]);
            $count++;
        }

        return $count;
    }

    private function importWarehouses(): int
    {
        $stmt = $this->source->query("SELECT id, name, address, is_default, is_enabled FROM warehouses");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;

        foreach ($rows as $row) {
            $newId = (string) Str::uuid();
            $this->idMap['warehouses'][(int) $row['id']] = $newId;

            Warehouse::create([
                'id' => $newId,
                'tenant_id' => $this->tenantId,
                'name' => $row['name'],
                'is_default' => (bool) ($row['is_default'] ?? false),
            ]);
            $count++;
        }

        return $count;
    }

    private function importPaymentTypes(): int
    {
        $stmt = $this->source->query("SELECT id, name, code, is_enabled FROM payment_types");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;

        foreach ($rows as $row) {
            $newId = (string) Str::uuid();
            $this->idMap['payment_types'][(int) $row['id']] = $newId;

            PaymentType::create([
                'id' => $newId,
                'tenant_id' => $this->tenantId,
                'name' => $row['name'],
                'code' => $row['code'] ?? '',
                'is_enabled' => (bool) ($row['is_enabled'] ?? true),
            ]);
            $count++;
        }

        return $count;
    }

    private function importDocumentTypes(): int
    {
        $stmt = $this->source->query("SELECT id, name, code, is_sale, is_return, is_enabled FROM document_types");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;

        foreach ($rows as $row) {
            $newId = (string) Str::uuid();
            $this->idMap['document_types'][(int) $row['id']] = $newId;

            DocumentType::create([
                'id' => $newId,
                'tenant_id' => $this->tenantId,
                'name' => $row['name'],
                'code' => $row['code'] ?? '',
            ]);
            $count++;
        }

        return $count;
    }

    private function importDocumentCategories(): int
    {
        $stmt = $this->source->query("SELECT id, name, sort_order FROM document_categories");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;

        foreach ($rows as $row) {
            $newId = (string) Str::uuid();
            $this->idMap['document_categories'][(int) $row['id']] = $newId;

            DocumentCategory::create([
                'id' => $newId,
                'tenant_id' => $this->tenantId,
                'name' => $row['name'],
                'rank' => (int) ($row['sort_order'] ?? 0),
            ]);
            $count++;
        }

        return $count;
    }

    private function importCustomers(): int
    {
        $stmt = $this->source->query("SELECT id, name, code, email, phone, address, is_enabled FROM customers");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;

        foreach ($rows as $row) {
            $newId = (string) Str::uuid();
            $this->idMap['customers'][(int) $row['id']] = $newId;

            Customer::create([
                'id' => $newId,
                'tenant_id' => $this->tenantId,
                'name' => $row['name'],
                'code' => $row['code'] ?? '',
                'email' => $row['email'] ?? '',
                'phone_number' => $row['phone'] ?? '',
                'address' => $row['address'] ?? '',
                'is_enabled' => (bool) ($row['is_enabled'] ?? true),
            ]);
            $count++;
        }

        return $count;
    }

    private function importProducts(): int
    {
        $stmt = $this->source->query(
            "SELECT id, name, code, plu, price, cost, product_group_id, warehouse_id, is_enabled, description FROM products"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;

        foreach ($rows as $row) {
            $newId = (string) Str::uuid();
            $this->idMap['products'][(int) $row['id']] = $newId;

            Product::create([
                'id' => $newId,
                'tenant_id' => $this->tenantId,
                'name' => $row['name'],
                'code' => $row['code'] ?? '',
                'plu' => $row['plu'] ?? '',
                'price' => (float) ($row['price'] ?? 0),
                'cost' => (float) ($row['cost'] ?? 0),
                'product_group_id' => isset($this->idMap['product_groups'][(int) $row['product_group_id']])
                    ? $this->idMap['product_groups'][(int) $row['product_group_id']]
                    : null,
                'is_enabled' => (bool) ($row['is_enabled'] ?? true),
                'description' => $row['description'] ?? '',
            ]);

            $this->importProductBarcodes((int) $row['id'], $newId);
            $this->importProductTaxes((int) $row['id'], $newId);

            $count++;
        }

        return $count;
    }

    private function importProductBarcodes(int $oldProductId, string $newProductId): void
    {
        $stmt = $this->source->prepare("SELECT barcode FROM barcodes WHERE product_id = :pid");
        $stmt->execute(['pid' => $oldProductId]);
        $barcodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($barcodes as $barcode) {
            DB::table('barcodes')->insert([
                'id' => (string) Str::uuid(),
                'product_id' => $newProductId,
                'tenant_id' => $this->tenantId,
                'barcode' => $barcode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function importProductTaxes(int $oldProductId, string $newProductId): void
    {
        $stmt = $this->source->prepare("SELECT tax_id FROM product_taxes WHERE product_id = :pid");
        $stmt->execute(['pid' => $oldProductId]);
        $taxIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($taxIds as $oldTaxId) {
            if (isset($this->idMap['taxes'][(int) $oldTaxId])) {
                DB::table('product_taxes')->insert([
                    'product_id' => $newProductId,
                    'tax_id' => $this->idMap['taxes'][(int) $oldTaxId],
                ]);
            }
        }
    }
}

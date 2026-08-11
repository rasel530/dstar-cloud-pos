<?php

namespace App\Services;

use App\Models\Barcode;

class BarcodeService
{
    public function generateCode128(): string
    {
        $prefix = '2';
        $random = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $body = $prefix . $random;
        $check = $this->calculateCheckDigit($body);
        return $body . $check;
    }

    public function generateEan13(): string
    {
        $prefix = '200';
        $random = '';
        for ($i = 0; $i < 9; $i++) {
            $random .= random_int(0, 9);
        }
        $body = $prefix . $random;
        $check = $this->calculateEanCheckDigit($body);
        return $body . $check;
    }

    public function generateUpcA(): string
    {
        $manufacturer = str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        $product = str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        $body = $manufacturer . $product;
        $check = $this->calculateEanCheckDigit('0' . $body);
        return $body . $check;
    }

    public function generate(string $type = 'CODE_128'): string
    {
        return match ($type) {
            'EAN_13', 'EAN-13' => $this->generateEan13(),
            'UPC_A', 'UPC-A' => $this->generateUpcA(),
            default => $this->generateCode128(),
        };
    }

    private function calculateCheckDigit(string $body): string
    {
        $sum = 0;
        $len = strlen($body);
        for ($i = 0; $i < $len; $i++) {
            $sum += (int) $body[$i] * (($len - $i) % 2 === 0 ? 3 : 1);
        }
        return (string) ((10 - ($sum % 10)) % 10);
    }

    private function calculateEanCheckDigit(string $body): string
    {
        $sum = 0;
        $len = strlen($body);
        for ($i = 0; $i < $len; $i++) {
            $sum += (int) $body[$i] * (($len - $i) % 2 === 0 ? 3 : 1);
        }
        return (string) ((10 - ($sum % 10)) % 10);
    }

    public function isDuplicate(string $value, ?string $excludeId = null): bool
    {
        $query = Barcode::where('value', $value);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}

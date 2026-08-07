<?php

namespace App\Services\Printing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrintJobDispatcher
{
    private string $proxyUrl;
    private string $proxyToken;

    public function __construct()
    {
        $this->proxyUrl = config('printers.proxy_url', 'http://localhost:9999');
        $this->proxyToken = config('printers.proxy_token', 'dstar-print-proxy-2026');
    }

    /**
     * Send print job to local print proxy
     */
    public function dispatch(string $printerKey, string $content, string $tenantId): array
    {
        try {
            $response = Http::withHeaders([
                'X-Proxy-Token' => $this->proxyToken,
            ])->timeout(10)->post("{$this->proxyUrl}/print/escpos", [
                'printerName' => $printerKey,
                'content' => $content,
                'cutPaper' => true,
                'openDrawer' => true,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Print job sent to printer.',
                    'printer' => $printerKey,
                    'sent_at' => now()->toIso8601String(),
                ];
            }

            Log::warning('Print proxy returned error', [
                'printer' => $printerKey,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Print proxy unavailable. Is the print proxy running?',
            ];
        } catch (\Exception $e) {
            Log::warning('Print proxy connection failed', [
                'printer' => $printerKey,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Print proxy not reachable. Start the print proxy on this terminal.',
                'hint' => 'Run: cd print-proxy && npm start',
            ];
        }
    }

    /**
     * Send test print to the local proxy
     */
    public function testPrint(string $printerName): array
    {
        $testContent = "=== TEST PRINT ===\n"
            . "D Star Company POS\n"
            . "Printer: {$printerName}\n"
            . "Date: " . now()->format('Y-m-d H:i:s') . "\n"
            . "----------------------------\n"
            . "If you can read this, the\n"
            . "printer is working correctly.\n"
            . "----------------------------\n"
            . "Thank you!\n\n\n";

        try {
            $response = Http::withHeaders([
                'X-Proxy-Token' => $this->proxyToken,
            ])->timeout(10)->post("{$this->proxyUrl}/print/escpos", [
                'printerName' => $printerName,
                'content' => $testContent,
                'cutPaper' => true,
                'openDrawer' => true,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Test print sent successfully.',
                    'printer' => $printerName,
                ];
            }

            return [
                'success' => false,
                'message' => 'Print proxy error: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Print proxy not reachable. Start it with: cd print-proxy && npm start',
            ];
        }
    }
}

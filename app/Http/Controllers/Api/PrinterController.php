<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosPrinterSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function index(): JsonResponse
    {
        $data = PosPrinterSetting::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'printer_name' => 'required|string|max:255',
            'paper_width' => 'nullable|integer',
            'header' => 'nullable|string',
            'footer' => 'nullable|string',
            'feed_lines' => 'nullable|integer|min:0',
            'cut_paper' => 'nullable|boolean',
            'print_bitmap' => 'nullable|boolean',
            'open_cash_drawer' => 'nullable|boolean',
            'cash_drawer_command' => 'nullable|string',
            'header_alignment' => 'nullable|integer',
            'footer_alignment' => 'nullable|integer',
            'is_formatting_enabled' => 'nullable|boolean',
            'printer_type' => 'nullable|integer',
            'number_of_copies' => 'nullable|integer|min:1',
            'code_page' => 'nullable|integer',
            'character_set' => 'nullable|integer',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $printer = PosPrinterSetting::create($validated);

        return response()->json(['data' => $printer], 201);
    }

    public function show($id): JsonResponse
    {
        $printer = PosPrinterSetting::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$printer) {
            return response()->json(['message' => 'Printer not found.'], 404);
        }

        return response()->json(['data' => $printer]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $printer = PosPrinterSetting::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$printer) {
            return response()->json(['message' => 'Printer not found.'], 404);
        }

        $validated = $request->validate([
            'printer_name' => 'sometimes|string|max:255',
            'paper_width' => 'nullable|integer',
            'header' => 'nullable|string',
            'footer' => 'nullable|string',
            'feed_lines' => 'nullable|integer|min:0',
            'cut_paper' => 'nullable|boolean',
            'print_bitmap' => 'nullable|boolean',
            'open_cash_drawer' => 'nullable|boolean',
            'cash_drawer_command' => 'nullable|string',
            'header_alignment' => 'nullable|integer',
            'footer_alignment' => 'nullable|integer',
            'is_formatting_enabled' => 'nullable|boolean',
            'printer_type' => 'nullable|integer',
            'number_of_copies' => 'nullable|integer|min:1',
            'code_page' => 'nullable|integer',
            'character_set' => 'nullable|integer',
        ]);

        $printer->update($validated);

        return response()->json(['data' => $printer->fresh()]);
    }

    public function destroy($id): JsonResponse
    {
        $printer = PosPrinterSetting::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$printer) {
            return response()->json(['message' => 'Printer not found.'], 404);
        }

        $printer->delete();

        return response()->json(null, 204);
    }

    public function testPrint($id): JsonResponse
    {
        $printer = PosPrinterSetting::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$printer) {
            return response()->json(['message' => 'Printer not found.'], 404);
        }

        $dispatcher = new \App\Services\Printing\PrintJobDispatcher();

        $result = $dispatcher->testPrint($printer->printer_name);

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}

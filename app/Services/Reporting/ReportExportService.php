<?php

namespace App\Services\Reporting;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function exportToCsv(array $data, array $columns, string $filename): string
    {
        $lines = [];
        $lines[] = implode(',', array_map(function ($col) {
            return '"' . str_replace('"', '""', $col) . '"';
        }, $columns));

        foreach ($data as $row) {
            $line = [];
            foreach ($columns as $key => $label) {
                $value = $row[$key] ?? '';
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $line[] = '"' . str_replace('"', '""', (string) $value) . '"';
            }
            $lines[] = implode(',', $line);
        }

        return implode("\n", $lines);
    }

    public function exportToPdf(array $data, string $title): string
    {
        $rows = '';
        foreach ($data as $row) {
            $cells = '';
            foreach ($row as $value) {
                $cells .= '<td style="border:1px solid #ddd;padding:6px;">' . htmlspecialchars((string) $value) . '</td>';
            }
            $rows .= '<tr>' . $cells . '</tr>';
        }

        $headers = '';
        if (!empty($data)) {
            $headers = '<tr>';
            foreach (array_keys(reset($data)) as $col) {
                $headers .= '<th style="border:1px solid #ddd;padding:8px;background:#f4f4f4;text-align:left;">' . htmlspecialchars((string) $col) . '</th>';
            }
            $headers .= '</tr>';
        }

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 16px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($title) . '</h1>
    <table>
        <thead>' . $headers . '</thead>
        <tbody>' . $rows . '</tbody>
    </table>
</body>
</html>';
    }

    public function download(array $data, string $format, string $filename): Response
    {
        $columns = [];
        if (!empty($data)) {
            $columns = array_keys(reset($data));
        }

        return match ($format) {
            'csv' => new Response(
                $this->exportToCsv($data, $columns, $filename),
                200,
                [
                    'Content-Type' => 'text/csv; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
                ]
            ),
            'pdf' => new Response(
                $this->exportToPdf($data, $filename),
                200,
                [
                    'Content-Type' => 'text/html; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '.html"',
                ]
            ),
            default => new Response(
                $this->exportToCsv($data, $columns, $filename),
                200,
                [
                    'Content-Type' => 'text/csv; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
                ]
            ),
        };
    }
}

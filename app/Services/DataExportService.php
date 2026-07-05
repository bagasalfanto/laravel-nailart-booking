<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportService
{
    /**
     * Generate a CSV file and return it as a download response.
     *
     * @param  string  $title     e.g. "payment-report"
     * @param  array   $columns   ['Order ID', 'Customer', 'Nominal']
     * @param  array   $rows      [['ORDER-1', 'John', '50000'], ...]
     */
    public function csv(string $title, array $columns, array $rows): StreamedResponse
    {
        $filename = Str::slug($title).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Generate a PDF from a Blade view and return it as a download response.
     *
     * @param  string  $title     e.g. "Payment Report"
     * @param  string  $view      Blade view path, e.g. "exports.payments-pdf"
     * @param  array   $data      Data passed to the Blade view
     * @param  string  $paper     'a4' or 'a4-landscape'
     */
    public function pdf(string $title, string $view, array $data = [], string $paper = 'a4-landscape'): Response
    {
        $filename = Str::slug($title).'-'.now()->format('Ymd-His').'.pdf';

        $pdf = Pdf::loadView($view, array_merge($data, [
            'exportTitle' => $title,
            'exportDate'  => now()->format('d M Y H:i'),
        ]));

        $pdf->setPaper($paper);

        return $pdf->download($filename);
    }

    /**
     * Convert a collection of Eloquent models into an array of rows using a column map.
     *
     * @param  Collection  $items
     * @param  array       $map    [['key' => 'customer.user.full_name', 'label' => 'Customer'], ...]
     * @return array                [['John Doe', '50000'], ...]
     */
    public function formatRows(Collection $items, array $map): array
    {
        return $items->map(function ($item) use ($map) {
            $row = [];
            foreach ($map as $col) {
                $value = data_get($item, $col['key'], '—');
                if ($value instanceof \BackedEnum) {
                    $value = $value->value;
                }
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format('d M Y H:i');
                }
                if (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                }
                $row[] = is_scalar($value) ? (string) $value : json_encode($value);
            }
            return $row;
        })->all();
    }
}

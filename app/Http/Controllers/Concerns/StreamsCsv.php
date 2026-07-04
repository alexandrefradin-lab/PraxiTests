<?php

namespace App\Http\Controllers\Concerns;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export CSV streamé pour les listes admin (leads, invitations, abonnements).
 * BOM UTF-8 + séparateur ';' : ouverture directe dans Excel FR.
 */
trait StreamsCsv
{
    protected function streamCsv(string $filename, array $header, \Closure $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';');
            foreach ($rows() as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

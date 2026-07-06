<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelXml
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    public static function download(string $filename, string $sheetName, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($sheetName, $headers, $rows): void {
            echo self::workbook($sheetName, $headers, $rows);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function read(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            return self::readCsv($file->getRealPath());
        }

        return self::readXml($file->getRealPath());
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    private static function workbook(string $sheetName, array $headers, array $rows): string
    {
        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<?mso-application progid="Excel.Sheet"?>',
            '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">',
            '<Worksheet ss:Name="'.self::escape($sheetName).'"><Table>',
            self::row($headers, true),
        ];

        foreach ($rows as $row) {
            $xml[] = self::row($row);
        }

        $xml[] = '</Table></Worksheet></Workbook>';

        return implode('', $xml);
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private static function row(array $values, bool $header = false): string
    {
        $cells = array_map(fn ($value): string => '<Cell><Data ss:Type="String">'.self::escape((string) $value).'</Data></Cell>', $values);

        return '<Row>'.implode('', $cells).'</Row>';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $headers = [];
        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === []) {
                $headers = self::normalizeHeaders($line);

                continue;
            }

            $rows[] = self::combine($headers, $line);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function readXml(string $path): array
    {
        $xml = simplexml_load_file($path);

        if ($xml === false) {
            return [];
        }

        $rawRows = $xml->xpath('//*[local-name()="Row"]') ?: [];
        $headers = [];
        $rows = [];

        foreach ($rawRows as $rawRow) {
            $values = self::rowValues($rawRow);

            if ($headers === []) {
                $headers = self::normalizeHeaders($values);

                continue;
            }

            $rows[] = self::combine($headers, $values);
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private static function rowValues(\SimpleXMLElement $row): array
    {
        $cells = $row->xpath('./*[local-name()="Cell"]') ?: [];
        $values = [];

        foreach ($cells as $cell) {
            $attributes = $cell->attributes('urn:schemas-microsoft-com:office:spreadsheet');
            $index = isset($attributes['Index']) ? ((int) $attributes['Index']) : null;

            if ($index !== null) {
                while (count($values) < $index - 1) {
                    $values[] = '';
                }
            }

            $data = $cell->xpath('./*[local-name()="Data"]');
            $values[] = trim((string) ($data[0] ?? ''));
        }

        return $values;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $line
     * @return array<string, string>
     */
    private static function combine(array $headers, array $line): array
    {
        $record = [];

        foreach ($headers as $index => $header) {
            $record[$header] = trim((string) ($line[$index] ?? ''));
        }

        return $record;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    private static function normalizeHeaders(array $headers): array
    {
        return array_map(function (string $header): string {
            $header = strtolower(trim($header));
            $header = str_replace([' ', '-'], '_', $header);

            return preg_replace('/[^a-z0-9_]/', '', $header) ?: '';
        }, $headers);
    }
}

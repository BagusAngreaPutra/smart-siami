<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use ZipArchive;

class LetterheadDocumentParser
{
    /**
     * @return array<int, string>
     */
    public static function linesFromDocx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        if ($xml === '') {
            return [];
        }

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return [];
        }

        $lines = [];
        foreach ($document->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'p') as $paragraph) {
            if (! $paragraph instanceof DOMElement) {
                continue;
            }

            $parts = [];
            foreach ($paragraph->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 't') as $textNode) {
                $parts[] = $textNode->textContent;
            }

            $line = trim(preg_replace('/\s+/', ' ', implode('', $parts)) ?? '');
            if ($line === '' || preg_match('/^[_=\-\s]{8,}$/', $line)) {
                continue;
            }

            if (str_contains(strtolower($line), 'template kop surat')) {
                continue;
            }

            if (str_contains(strtolower($line), 'silakan ubah identitas')) {
                continue;
            }

            $lines[] = $line;
        }

        return collect($lines)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $lines
     * @return array{institution: string|null, unit: string|null, address: string|null, contact: string|null}
     */
    public static function mapLinesToLetterhead(array $lines): array
    {
        $lines = array_values(array_filter(array_map('trim', $lines)));

        if ($lines === []) {
            return [
                'institution' => null,
                'unit' => null,
                'address' => null,
                'contact' => null,
            ];
        }

        $institution = $lines[0] ?? null;
        $unit = $lines[1] ?? null;
        $remaining = array_slice($lines, 2);
        $address = [];
        $contact = [];

        foreach ($remaining as $line) {
            if (preg_match('/(telp|telepon|fax|email|@|www|http|laman)/i', $line)) {
                $contact[] = $line;
            } else {
                $address[] = $line;
            }
        }

        return [
            'institution' => $institution,
            'unit' => $unit,
            'address' => $address ? implode("\n", $address) : null,
            'contact' => $contact ? implode("\n", $contact) : null,
        ];
    }
}

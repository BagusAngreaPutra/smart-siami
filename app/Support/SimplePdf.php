<?php

namespace App\Support;

class SimplePdf
{
    /**
     * @param  array<int, string>  $lines
     * @param  array<string, mixed>  $options
     */
    public static function document(array $lines, array $options = []): string
    {
        [$pageWidth, $pageHeight] = self::pageSize($options['paper_size'] ?? 'A4', $options['orientation'] ?? 'portrait');
        $fontSize = max(9, min(16, (int) ($options['font_size'] ?? 12)));
        $lineHeight = max(1.15, min(1.8, (float) ($options['line_height'] ?? 1.45)));
        $fontName = self::fontName($options['font_family'] ?? 'Helvetica');
        $marginTop = self::cmToPoint((float) ($options['margin_top_cm'] ?? 1.8));
        $marginRight = self::cmToPoint((float) ($options['margin_right_cm'] ?? 1.6));
        $marginBottom = self::cmToPoint((float) ($options['margin_bottom_cm'] ?? 1.8));
        $marginLeft = self::cmToPoint((float) ($options['margin_left_cm'] ?? 1.6));
        $usableWidth = max(120, $pageWidth - $marginLeft - $marginRight);
        $usableHeight = max(120, $pageHeight - $marginTop - $marginBottom);
        $letterhead = ($options['include_letterhead'] ?? true) ? ReportLetterhead::jpegHeader(1000) : null;
        $letterheadWidth = 0.0;
        $letterheadHeight = 0.0;
        if ($letterhead) {
            $letterheadWidth = $usableWidth;
            $letterheadHeight = round($usableWidth * ($letterhead['height'] / max(1, $letterhead['width'])), 2);
            $usableHeight = max(120, $usableHeight - $letterheadHeight - 18);
        }
        $lineStep = round($fontSize * $lineHeight, 2);
        $maxChars = max(28, (int) floor($usableWidth / ($fontSize * 0.52)));
        $linesPerPage = max(8, (int) floor($usableHeight / $lineStep));

        $wrappedLines = collect($lines)
            ->flatMap(fn (string $line): array => str_split($line, $maxChars) ?: [''])
            ->values();

        $pages = $wrappedLines->chunk($linesPerPage)->values();
        if ($pages->isEmpty()) {
            $pages = collect([collect([''])]);
        }

        $fontObjectId = 3 + ($pages->count() * 2);
        $imageObjectId = $letterhead ? $fontObjectId + 1 : null;
        $pageObjectIds = [];
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '',
        ];

        foreach ($pages as $index => $pageLines) {
            $pageObjectId = 3 + ($index * 2);
            $contentObjectId = $pageObjectId + 1;
            $pageObjectIds[] = $pageObjectId.' 0 R';

            $content = '';
            $startY = $pageHeight - $marginTop;
            if ($letterhead && $index === 0) {
                $imageY = $pageHeight - $marginTop - $letterheadHeight;
                $content .= "q\n{$letterheadWidth} 0 0 {$letterheadHeight} {$marginLeft} {$imageY} cm\n/Im1 Do\nQ\n";
                $startY = $imageY - 18;
            }

            $content .= "BT\n/F1 {$fontSize} Tf\n{$marginLeft} {$startY} Td\n";

            foreach ($pageLines->map(fn (string $line): string => self::escape($line))->values() as $lineIndex => $line) {
                if ($lineIndex > 0) {
                    $content .= "0 -{$lineStep} Td\n";
                }

                $content .= "({$line}) Tj\n";
            }

            $content .= 'ET';

            $xObject = $letterhead ? " /XObject << /Im1 {$imageObjectId} 0 R >>" : '';
            $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageWidth} {$pageHeight}] /Resources << /Font << /F1 {$fontObjectId} 0 R >>{$xObject} >> /Contents {$contentObjectId} 0 R >>";
            $objects[] = '<< /Length '.strlen($content)." >>\nstream\n{$content}\nendstream";
        }

        $objects[1] = '<< /Type /Pages /Kids ['.implode(' ', $pageObjectIds).'] /Count '.$pages->count().' >>';
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /{$fontName} >>";
        if ($letterhead) {
            $objects[] = "<< /Type /XObject /Subtype /Image /Width {$letterhead['width']} /Height {$letterhead['height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($letterhead['data'])." >>\nstream\n{$letterhead['data']}\nendstream";
        }

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($number + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    /**
     * @return array<int, int>
     */
    private static function pageSize(string $paperSize, string $orientation): array
    {
        $sizes = [
            'A4' => [595, 842],
            'F4' => [595, 935],
            'Letter' => [612, 792],
            'Legal' => [612, 1008],
        ];

        [$width, $height] = $sizes[$paperSize] ?? $sizes['A4'];

        return $orientation === 'landscape' ? [$height, $width] : [$width, $height];
    }

    private static function cmToPoint(float $centimeters): float
    {
        return round(max(0.5, min(5, $centimeters)) * 28.3465, 2);
    }

    private static function fontName(string $fontFamily): string
    {
        return match ($fontFamily) {
            'Times New Roman' => 'Times-Roman',
            'Courier' => 'Courier',
            default => 'Helvetica',
        };
    }

    private static function escape(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', $value);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}

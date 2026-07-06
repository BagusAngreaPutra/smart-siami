<?php

namespace App\Support;

class SimplePdf
{
    /**
     * @param  array<string, mixed>  $report
     * @param  array<string, mixed>  $options
     */
    public static function report(array $report, array $options = []): string
    {
        $options = self::autoPdfOptions($report, $options);
        [$pageWidth, $pageHeight] = self::pageSize($options['paper_size'] ?? 'A4', $options['orientation'] ?? 'portrait');
        $fontSize = max(8, min(14, (int) ($options['font_size'] ?? 11)));
        $lineHeight = max(1.15, min(1.8, (float) ($options['line_height'] ?? 1.35)));
        $fontName = self::fontName($options['font_family'] ?? 'Helvetica');
        $marginTop = self::cmToPoint((float) ($options['margin_top_cm'] ?? 1.8));
        $marginRight = self::cmToPoint((float) ($options['margin_right_cm'] ?? 1.6));
        $marginBottom = self::cmToPoint((float) ($options['margin_bottom_cm'] ?? 1.8));
        $marginLeft = self::cmToPoint((float) ($options['margin_left_cm'] ?? 1.6));
        $usableWidth = max(120, $pageWidth - $marginLeft - $marginRight);
        $letterhead = ($options['include_letterhead'] ?? true) ? ReportLetterhead::jpegHeader(1000) : null;
        $letterheadWidth = 0.0;
        $letterheadHeight = 0.0;

        if ($letterhead) {
            $letterheadWidth = $usableWidth;
            $letterheadHeight = round($usableWidth * ($letterhead['height'] / max(1, $letterhead['width'])), 2);
        }

        $pages = [''];
        $currentPage = 0;
        $y = $pageHeight - $marginTop;

        $add = function (string $command) use (&$pages, &$currentPage): void {
            $pages[$currentPage] .= $command;
        };

        $newPage = function () use (&$pages, &$currentPage, &$y, $pageHeight, $marginTop): void {
            $pages[] = '';
            $currentPage++;
            $y = $pageHeight - $marginTop;
        };

        $ensure = function (float $height) use (&$y, $marginBottom, $newPage): void {
            if ($y - $height < $marginBottom) {
                $newPage();
            }
        };

        if ($letterhead) {
            $imageY = $pageHeight - $marginTop - $letterheadHeight;
            $add("q\n{$letterheadWidth} 0 0 {$letterheadHeight} {$marginLeft} {$imageY} cm\n/Im1 Do\nQ\n");
            $y = $imageY - 16;
        }

        self::drawCenteredText($add, (string) ($report['title'] ?? 'Laporan'), $marginLeft, $usableWidth, $y, $fontSize + 3, true);
        $y -= ($fontSize + 9);
        if (! empty($report['subtitle'])) {
            self::drawCenteredText($add, (string) $report['subtitle'], $marginLeft, $usableWidth, $y, max(8, $fontSize - 1), false);
            $y -= ($fontSize + 12);
        }

        foreach (($report['meta'] ?? []) as $key => $value) {
            $ensure($fontSize * $lineHeight + 2);
                self::drawText($add, (string) $key.':', $marginLeft, $y, max(8, $fontSize - 1), true);
                self::drawText($add, (string) $value, $marginLeft + 112, $y, max(8, $fontSize - 1));
            $y -= round($fontSize * $lineHeight, 2);
        }

        if (($options['show_visual_summary'] ?? true) && ! empty($report['visuals'])) {
            $y -= 10;
            $ensure(86);
            self::drawText($add, 'Ringkasan Visual', $marginLeft, $y, $fontSize + 1, true);
            $y -= 18;
            $readiness = (int) ($report['visuals']['readiness'] ?? 0);
            self::drawRect($add, $marginLeft, $y - 40, 74, 44, [228, 242, 238], [196, 218, 212]);
            self::drawCenteredText($add, $readiness.'%', $marginLeft, 74, $y - 21, $fontSize + 8, true);
            self::drawCenteredText($add, 'Kesiapan', $marginLeft, 74, $y - 37, max(7, $fontSize - 3));

            $barX = $marginLeft + 94;
            $barY = $y - 3;
            foreach (array_slice($report['visuals']['radar'] ?? [], 0, 6) as $item) {
                $value = (int) ($item['value'] ?? 0);
                self::drawText($add, (string) ($item['label'] ?? '-'), $barX, $barY, max(7, $fontSize - 3), true);
                self::drawRect($add, $barX + 58, $barY - 4, max(80, $usableWidth - 210), 6, [228, 242, 238], null);
                self::drawRect($add, $barX + 58, $barY - 4, max(1, (max(80, $usableWidth - 210) * $value) / 100), 6, [14, 102, 86], null);
                self::drawText($add, $value.'%', $marginLeft + $usableWidth - 32, $barY, max(7, $fontSize - 3));
                $barY -= 14;
            }
            $y -= 68;
        }

        foreach (($report['tables'] ?? []) as $table) {
            $y -= 8;
            $ensure(48);
            self::drawText($add, (string) ($table['title'] ?? 'Tabel'), $marginLeft, $y, $fontSize + 1, true);
            $y -= 17;
            self::drawTable($add, $ensure, $newPage, $y, $marginLeft, $usableWidth, $marginBottom, $pageHeight, $marginTop, $table, $fontSize, $lineHeight, $options['table_density'] ?? 'normal');
        }

        return self::compilePdf($pages, $pageWidth, $pageHeight, $fontName, $letterhead);
    }

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

        return self::compileObjects($objects);
    }

    /**
     * @param  array<int, string>  $pages
     * @param  array<string, mixed>|null  $letterhead
     */
    private static function compilePdf(array $pages, int $pageWidth, int $pageHeight, string $fontName, ?array $letterhead): string
    {
        $fontObjectId = 3 + (count($pages) * 2);
        $imageObjectId = $letterhead ? $fontObjectId + 1 : null;
        $pageObjectIds = [];
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '',
        ];

        foreach ($pages as $index => $content) {
            $pageObjectId = 3 + ($index * 2);
            $contentObjectId = $pageObjectId + 1;
            $pageObjectIds[] = $pageObjectId.' 0 R';
            $xObject = $letterhead ? " /XObject << /Im1 {$imageObjectId} 0 R >>" : '';
            $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageWidth} {$pageHeight}] /Resources << /Font << /F1 {$fontObjectId} 0 R >>{$xObject} >> /Contents {$contentObjectId} 0 R >>";
            $objects[] = '<< /Length '.strlen($content)." >>\nstream\n{$content}\nendstream";
        }

        $objects[1] = '<< /Type /Pages /Kids ['.implode(' ', $pageObjectIds).'] /Count '.count($pages).' >>';
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /{$fontName} >>";
        if ($letterhead) {
            $objects[] = "<< /Type /XObject /Subtype /Image /Width {$letterhead['width']} /Height {$letterhead['height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($letterhead['data'])." >>\nstream\n{$letterhead['data']}\nendstream";
        }

        return self::compileObjects($objects);
    }

    /**
     * @param  array<int, string>  $objects
     */
    private static function compileObjects(array $objects): string
    {
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

    private static function drawTable(
        callable $add,
        callable $ensure,
        callable $newPage,
        float &$y,
        float $x,
        float $width,
        float $marginBottom,
        int $pageHeight,
        float $marginTop,
        array $table,
        int $fontSize,
        float $lineHeight,
        string $density,
    ): void {
        $headers = array_values($table['headers'] ?? []);
        $rows = array_values($table['rows'] ?? []);
        $columnCount = max(1, count($headers));
        $cellFont = self::tableFontSize($fontSize, $columnCount, $width, $rows);
        $padding = match ($density) {
            'compact' => 3,
            'auto-compact' => 2.5,
            'loose' => 6,
            default => 4,
        };
        $rowLineHeight = max(8, round($cellFont * $lineHeight, 2));
        $columnWidths = self::columnWidths($headers, $width);

        $drawHeader = function () use ($add, &$y, $x, $columnWidths, $headers, $cellFont, $padding, $rowLineHeight): void {
            $wrappedHeaders = [];
            $headerLines = 1;
            foreach ($headers as $index => $header) {
                $maxChars = self::cellMaxChars($columnWidths[$index], $padding, $cellFont);
                $wrappedHeaders[$index] = self::wrapText((string) $header, $maxChars, 3);
                $headerLines = max($headerLines, count($wrappedHeaders[$index]));
            }

            $headerHeight = max(18, ($headerLines * $rowLineHeight) + ($padding * 2));
            $cursorX = $x;
            foreach ($headers as $index => $header) {
                $cellWidth = $columnWidths[$index];
                self::drawRect($add, $cursorX, $y - $headerHeight + 4, $cellWidth, $headerHeight, [14, 102, 86], [14, 102, 86]);
                $textY = $y - $padding - 5;
                foreach ($wrappedHeaders[$index] as $line) {
                    self::drawText(
                        $add,
                        $line,
                        $cursorX + $padding,
                        $textY,
                        $cellFont,
                        true,
                        [255, 255, 255],
                        [$cursorX + $padding, $y - $headerHeight + 4 + $padding, $cellWidth - ($padding * 2), $headerHeight - ($padding * 2)]
                    );
                    $textY -= $rowLineHeight;
                }
                $cursorX += $cellWidth;
            }
            $y -= $headerHeight;
        };

        $ensure(42);
        $drawHeader();

        if ($rows === []) {
            $rows = [[str_repeat('-', $columnCount)]];
        }

        foreach ($rows as $row) {
            $cells = array_values($row);
            $wrappedCells = [];
            $maxLines = 1;

            for ($i = 0; $i < $columnCount; $i++) {
                $text = (string) ($cells[$i] ?? '');
                $maxChars = self::cellMaxChars($columnWidths[$i], $padding, $cellFont);
                $wrapped = self::wrapText($text, $maxChars, 8);
                $wrappedCells[$i] = $wrapped;
                $maxLines = max($maxLines, count($wrapped));
            }

            $rowHeight = max(18, ($maxLines * $rowLineHeight) + ($padding * 2));
            if ($y - $rowHeight < $marginBottom) {
                $newPage();
                $y = $pageHeight - $marginTop;
                $drawHeader();
            }

            $cursorX = $x;
            foreach ($wrappedCells as $index => $lines) {
                $cellWidth = $columnWidths[$index];
                self::drawRect($add, $cursorX, $y - $rowHeight + 4, $cellWidth, $rowHeight, null, [217, 222, 232]);
                $textY = $y - $padding - 5;
                foreach ($lines as $line) {
                    self::drawText(
                        $add,
                        $line,
                        $cursorX + $padding,
                        $textY,
                        $cellFont,
                        false,
                        [23, 32, 51],
                        [$cursorX + $padding, $y - $rowHeight + 4 + $padding, $cellWidth - ($padding * 2), $rowHeight - ($padding * 2)]
                    );
                    $textY -= $rowLineHeight;
                }
                $cursorX += $cellWidth;
            }

            $y -= $rowHeight;
        }
    }

    /**
     * @param  array<string, mixed>  $report
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private static function autoPdfOptions(array $report, array $options): array
    {
        $maxColumns = collect($report['tables'] ?? [])
            ->map(fn (array $table): int => count($table['headers'] ?? []))
            ->max() ?? 0;
        $hasDenseRows = collect($report['tables'] ?? [])
            ->flatMap(fn (array $table): array => $table['rows'] ?? [])
            ->contains(fn (array $row): bool => collect($row)->contains(fn ($value): bool => strlen((string) $value) > 90));

        if (($options['orientation'] ?? 'portrait') === 'portrait' && ($maxColumns >= 6 || $hasDenseRows)) {
            $options['orientation'] = 'landscape';
        }

        if (($options['table_density'] ?? 'normal') === 'normal' && ($maxColumns >= 6 || $hasDenseRows)) {
            $options['table_density'] = 'auto-compact';
        }

        return $options;
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private static function tableFontSize(int $baseFont, int $columnCount, float $width, array $rows): int
    {
        $longest = collect($rows)
            ->flatMap(fn (array $row): array => array_map(fn ($value): int => strlen((string) $value), $row))
            ->max() ?? 0;
        $averageColumnWidth = $width / max(1, $columnCount);
        $font = $baseFont - 2;

        if ($columnCount >= 6) {
            $font -= 2;
        }

        if ($columnCount >= 8 || $averageColumnWidth < 70 || $longest > 140) {
            $font -= 1;
        }

        return max(6, min(10, $font));
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, float>
     */
    private static function columnWidths(array $headers, float $totalWidth): array
    {
        $weights = array_map(function (string $header): float {
            return match (strtolower($header)) {
                'instrumen', 'standar', 'skor', 'kategori', 'prioritas' => 0.9,
                'status', 'status bukti', 'status jawaban' => 1.15,
                'target' => 1.55,
                'realisasi' => 1.25,
                'nomor', 'nomor temuan', 'pic' => 1.0,
                'pertanyaan', 'jawaban', 'jawaban auditee', 'kondisi aktual', 'kriteria', 'bukti objektif', 'rekomendasi', 'rencana tindakan', 'catatan auditor' => 2.0,
                default => 1.2,
            };
        }, $headers);
        $sum = array_sum($weights) ?: 1;

        return array_map(fn (float $weight): float => round(($weight / $sum) * $totalWidth, 2), $weights);
    }

    private static function cellMaxChars(float $cellWidth, float $padding, int $fontSize): int
    {
        $innerWidth = max(10, $cellWidth - ($padding * 2));

        return max(3, (int) floor($innerWidth / max(3.6, $fontSize * 0.62)));
    }

    /**
     * @return array<int, string>
     */
    private static function wrapText(string $text, int $maxChars, int $maxLines = 99): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?: '');
        if ($text === '') {
            return ['-'];
        }

        $lines = [];
        foreach (explode(' ', $text) as $word) {
            $chunks = strlen($word) > $maxChars
                ? str_split($word, max(1, $maxChars - 1))
                : [$word];

            foreach ($chunks as $chunkIndex => $chunk) {
                $chunk .= (count($chunks) > 1 && $chunkIndex < count($chunks) - 1) ? '-' : '';
                $last = array_key_last($lines);
                if ($last === null || strlen($lines[$last].' '.$chunk) > $maxChars) {
                    $lines[] = $chunk;
                } else {
                    $lines[$last] .= ' '.$chunk;
                }

                if (count($lines) >= $maxLines) {
                    break 2;
                }
            }
        }

        if (count($lines) === $maxLines && strlen($text) > strlen(implode(' ', $lines))) {
            $lines[$maxLines - 1] = rtrim(substr($lines[$maxLines - 1], 0, max(1, $maxChars - 3))).'...';
        }

        return $lines ?: ['-'];
    }

    private static function drawCenteredText(callable $add, string $text, float $x, float $width, float $y, int $fontSize, bool $bold = false): void
    {
        $textWidth = strlen($text) * $fontSize * 0.46;
        self::drawText($add, $text, $x + max(0, ($width - $textWidth) / 2), $y, $fontSize, $bold);
    }

    /**
     * @param  array<int, int>  $color
     */
    /**
     * @param  array<int, int>  $color
     * @param  array{0: float, 1: float, 2: float, 3: float}|null  $clip
     */
    private static function drawText(callable $add, string $text, float $x, float $y, int $fontSize, bool $bold = false, array $color = [23, 32, 51], ?array $clip = null): void
    {
        [$r, $g, $b] = self::rgb($color);
        $escaped = self::escape(self::pdfText($bold ? strtoupper($text) : $text));
        $prefix = '';
        $suffix = '';

        if ($clip) {
            [$clipX, $clipY, $clipWidth, $clipHeight] = $clip;
            $prefix = "q\n{$clipX} {$clipY} {$clipWidth} {$clipHeight} re W n\n";
            $suffix = "Q\n";
        }

        $add("{$prefix}BT\n{$r} {$g} {$b} rg\n/F1 {$fontSize} Tf\n{$x} {$y} Td\n({$escaped}) Tj\nET\n{$suffix}");
    }

    /**
     * @param  array<int, int>|null  $fill
     * @param  array<int, int>|null  $stroke
     */
    private static function drawRect(callable $add, float $x, float $y, float $width, float $height, ?array $fill = null, ?array $stroke = null): void
    {
        $command = "q\n";
        if ($fill) {
            [$r, $g, $b] = self::rgb($fill);
            $command .= "{$r} {$g} {$b} rg\n";
        }
        if ($stroke) {
            [$r, $g, $b] = self::rgb($stroke);
            $command .= "{$r} {$g} {$b} RG\n0.5 w\n";
        }
        $command .= "{$x} {$y} {$width} {$height} re ".($fill && $stroke ? 'B' : ($fill ? 'f' : 'S'))."\nQ\n";
        $add($command);
    }

    /**
     * @param  array<int, int>  $color
     * @return array<int, string>
     */
    private static function rgb(array $color): array
    {
        return array_map(fn (int $value): string => rtrim(rtrim(sprintf('%.4F', max(0, min(255, $value)) / 255), '0'), '.'), $color);
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

    private static function pdfText(string $value): string
    {
        return iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value) ?: $value;
    }
}

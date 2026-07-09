<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ExcelXml
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    public static function download(string $filename, string $sheetName, array $headers, array $rows): StreamedResponse
    {
        $filename = self::xlsxFilename($filename);

        return response()->streamDownload(function () use ($sheetName, $headers, $rows): void {
            $path = tempnam(sys_get_temp_dir(), 'siami-xlsx-');

            if ($path === false) {
                return;
            }

            self::writeXlsx($path, $sheetName, $headers, $rows);
            readfile($path);
            @unlink($path);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private static function xlsxFilename(string $filename): string
    {
        return preg_replace('/\.(xls|xml|csv|txt)$/i', '.xlsx', $filename) ?: $filename;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function read(UploadedFile $file): array
    {
        $rawRows = self::readRaw($file);

        if ($rawRows === []) {
            return [];
        }

        $headers = self::normalizeHeaders(array_shift($rawRows));

        return array_map(fn (array $row): array => self::combine($headers, $row), $rawRows);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function readRaw(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            return self::readCsvRows($file->getRealPath());
        }

        if ($extension === 'xlsx') {
            return self::readXlsxRows($file->getRealPath());
        }

        return self::readXmlRows($file->getRealPath());
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
            '<Styles>',
            '<Style ss:ID="Header"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#0E6656" ss:Pattern="Solid"/><Alignment ss:Vertical="Center" ss:WrapText="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0A4A3F"/></Borders></Style>',
            '<Style ss:ID="Cell"><Alignment ss:Vertical="Top" ss:WrapText="1"/></Style>',
            '</Styles>',
            '<Worksheet ss:Name="'.self::escape($sheetName).'"><Table>',
            self::columns($headers, $rows),
            self::row($headers, true),
        ];

        foreach ($rows as $row) {
            $xml[] = self::row($row);
        }

        $xml[] = '</Table></Worksheet></Workbook>';

        return implode('', $xml);
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    private static function writeXlsx(string $path, string $sheetName, array $headers, array $rows): void
    {
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', self::xlsxContentTypes());
        $zip->addFromString('_rels/.rels', self::xlsxRootRels());
        $zip->addFromString('docProps/app.xml', self::xlsxAppProperties());
        $zip->addFromString('docProps/core.xml', self::xlsxCoreProperties());
        $zip->addFromString('xl/workbook.xml', self::xlsxWorkbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::xlsxWorkbookRels());
        $zip->addFromString('xl/styles.xml', self::xlsxStyles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::xlsxWorksheet($headers, $rows));
        $zip->close();
    }

    private static function xlsxContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>';
    }

    private static function xlsxRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private static function xlsxWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private static function xlsxWorkbook(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.self::escapeXmlAttribute(self::safeSheetName($sheetName)).'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private static function xlsxAppProperties(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            .'<Application>SMART SIAMI</Application>'
            .'</Properties>';
    }

    private static function xlsxCoreProperties(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            .'<dc:creator>SMART SIAMI</dc:creator>'
            .'<cp:lastModifiedBy>SMART SIAMI</cp:lastModifiedBy>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.gmdate('c').'</dcterms:created>'
            .'<dcterms:modified xsi:type="dcterms:W3CDTF">'.gmdate('c').'</dcterms:modified>'
            .'</cp:coreProperties>';
    }

    private static function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><color theme="1"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0E6656"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFD9E5E1"/></left><right style="thin"><color rgb="FFD9E5E1"/></right><top style="thin"><color rgb="FFD9E5E1"/></top><bottom style="thin"><color rgb="FFD9E5E1"/></bottom><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'<dxfs count="0"/><tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleLight16"/>'
            .'</styleSheet>';
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    private static function xlsxWorksheet(array $headers, array $rows): string
    {
        $allRows = [$headers, ...$rows];
        $columnCount = max(count($headers), ...array_map('count', $rows ?: [[]]));
        $rowCount = max(1, count($allRows));
        $dimension = 'A1:'.self::xlsxColumnName($columnCount).$rowCount;
        $xml = [
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">',
            '<dimension ref="'.$dimension.'"/>',
            '<cols>'.self::xlsxColumns($headers, $rows).'</cols>',
            '<sheetData>',
        ];

        foreach ($allRows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $xml[] = '<row r="'.$excelRow.'">';

            for ($columnIndex = 1; $columnIndex <= $columnCount; $columnIndex++) {
                $value = (string) ($row[$columnIndex - 1] ?? '');
                $style = $excelRow === 1 ? 1 : 2;
                $reference = self::xlsxColumnName($columnIndex).$excelRow;
                $xml[] = '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.self::escape($value).'</t></is></c>';
            }

            $xml[] = '</row>';
        }

        $xml[] = '</sheetData></worksheet>';

        return implode('', $xml);
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    private static function xlsxColumns(array $headers, array $rows): string
    {
        $widths = [];
        $sampleRows = array_slice($rows, 0, 100);

        foreach ([$headers, ...$sampleRows] as $row) {
            foreach ($row as $index => $value) {
                $length = self::displayLength((string) $value);
                $widths[$index] = max($widths[$index] ?? 0, $length);
            }
        }

        $columnCount = max(count($headers), count($widths));
        $columns = [];

        for ($index = 1; $index <= $columnCount; $index++) {
            $length = $widths[$index - 1] ?? 10;
            $width = min(80, max(10, $length + 3));
            $columns[] = '<col min="'.$index.'" max="'.$index.'" width="'.number_format($width, 2, '.', '').'" customWidth="1"/>';
        }

        return implode('', $columns);
    }

    private static function xlsxColumnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private static function safeSheetName(string $sheetName): string
    {
        $sheetName = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', $sheetName) ?: 'Sheet1';

        return mb_substr(trim($sheetName) ?: 'Sheet1', 0, 31);
    }

    private static function escapeXmlAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private static function row(array $values, bool $header = false): string
    {
        $style = $header ? 'Header' : 'Cell';
        $cells = array_map(fn ($value): string => '<Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.self::escape((string) $value).'</Data></Cell>', $values);

        return '<Row>'.implode('', $cells).'</Row>';
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     */
    private static function columns(array $headers, array $rows): string
    {
        $widths = [];
        $sampleRows = array_slice($rows, 0, 100);

        foreach ([$headers, ...$sampleRows] as $row) {
            foreach ($row as $index => $value) {
                $length = self::displayLength((string) $value);
                $widths[$index] = max($widths[$index] ?? 0, $length);
            }
        }

        $columnCount = max(count($headers), count($widths));
        $columns = [];

        for ($index = 0; $index < $columnCount; $index++) {
            $length = $widths[$index] ?? 10;
            $width = min(80, max(10, ($length * 7) + 18));
            $columns[] = '<Column ss:AutoFitWidth="0" ss:Width="'.number_format($width, 2, '.', '').'"/>';
        }

        return implode('', $columns);
    }

    private static function displayLength(string $value): int
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        if ($value === '') {
            return 8;
        }

        return mb_strlen($value);
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
        $rawRows = self::readCsvRows($path);
        $headers = self::normalizeHeaders(array_shift($rawRows) ?? []);

        return array_map(fn (array $row): array => self::combine($headers, $row), $rawRows);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function readXml(string $path): array
    {
        $rawRows = self::readXmlRows($path);
        $headers = self::normalizeHeaders(array_shift($rawRows) ?? []);

        return array_map(fn (array $row): array => self::combine($headers, $row), $rawRows);
    }

    /**
     * @return array<int, array<int, string>>
     */
    private static function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            $rows[] = array_map(fn ($value): string => trim((string) $value), $line);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, array<int, string>>
     */
    private static function readXmlRows(string $path): array
    {
        $xml = simplexml_load_file($path);

        if ($xml === false) {
            return [];
        }

        $rawRows = $xml->xpath('//*[local-name()="Row"]') ?: [];
        $rows = [];

        foreach ($rawRows as $rawRow) {
            $rows[] = self::rowValues($rawRow);
        }

        return $rows;
    }

    /**
     * @return array<int, array<int, string>>
     */
    private static function readXlsxRows(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = self::xlsxSharedStrings($zip);
        $sheetPath = self::xlsxFirstSheetPath($zip);

        if ($sheetPath === null) {
            $zip->close();

            return [];
        }

        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $xml = simplexml_load_string($sheetXml);

        if ($xml === false) {
            return [];
        }

        $rows = [];

        foreach ($xml->sheetData->row ?? [] as $row) {
            $values = [];

            foreach ($row->c ?? [] as $cell) {
                $attributes = $cell->attributes();
                $reference = (string) ($attributes['r'] ?? '');
                $type = (string) ($attributes['t'] ?? '');
                $columnIndex = self::xlsxColumnIndex($reference);

                while (count($values) < $columnIndex - 1) {
                    $values[] = '';
                }

                $values[] = self::xlsxCellValue($cell, $type, $sharedStrings);
            }

            $rows[] = $values;
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private static function xlsxSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $shared = simplexml_load_string($xml);

        if ($shared === false) {
            return [];
        }

        $strings = [];

        foreach ($shared->si ?? [] as $item) {
            if (isset($item->t)) {
                $strings[] = trim((string) $item->t);

                continue;
            }

            $text = '';

            foreach ($item->r ?? [] as $run) {
                $text .= (string) ($run->t ?? '');
            }

            $strings[] = trim($text);
        }

        return $strings;
    }

    private static function xlsxFirstSheetPath(ZipArchive $zip): ?string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            return $zip->locateName('xl/worksheets/sheet1.xml') !== false ? 'xl/worksheets/sheet1.xml' : null;
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);

        if ($workbook === false || $rels === false) {
            return null;
        }

        $relationshipId = null;

        foreach ($workbook->sheets->sheet ?? [] as $sheet) {
            $attributes = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $relationshipId = (string) ($attributes['id'] ?? '');
            break;
        }

        if ($relationshipId === null || $relationshipId === '') {
            return null;
        }

        foreach ($rels->Relationship ?? [] as $relationship) {
            $attributes = $relationship->attributes();

            if ((string) ($attributes['Id'] ?? '') === $relationshipId) {
                $target = (string) ($attributes['Target'] ?? '');

                return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            }
        }

        return null;
    }

    /**
     * @param  \SimpleXMLElement  $cell
     * @param  array<int, string>  $sharedStrings
     */
    private static function xlsxCellValue(\SimpleXMLElement $cell, string $type, array $sharedStrings): string
    {
        if ($type === 's') {
            $index = (int) ($cell->v ?? 0);

            return trim($sharedStrings[$index] ?? '');
        }

        if ($type === 'inlineStr') {
            return trim((string) ($cell->is->t ?? ''));
        }

        return trim((string) ($cell->v ?? ''));
    }

    private static function xlsxColumnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference)) ?: 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(1, $index);
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

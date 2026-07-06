<?php

namespace App\Support;

use ZipArchive;

class LetterheadTemplate
{
    public static function pdf(): string
    {
        return SimplePdf::document([
            'Template kop surat ini dapat diganti sesuai identitas institusi.',
        ], [
            'paper_size' => 'A4',
            'orientation' => 'portrait',
            'margin_top_cm' => 1.8,
            'margin_right_cm' => 1.6,
            'margin_bottom_cm' => 1.8,
            'margin_left_cm' => 1.6,
            'font_family' => 'Helvetica',
            'font_size' => 12,
            'line_height' => 1.4,
            'include_letterhead' => true,
        ]);
    }

    public static function docx(): string
    {
        $temporary = tempnam(sys_get_temp_dir(), 'kop-jds-').'.docx';
        $zip = new ZipArchive();
        $zip->open($temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $logoPath = resource_path('assets/logo JDS tanpa company.png');
        $hasLogo = is_file($logoPath);

        $zip->addFromString('[Content_Types].xml', self::contentTypes($hasLogo));
        $zip->addFromString('_rels/.rels', self::rootRelationships());
        $zip->addFromString('word/_rels/document.xml.rels', self::documentRelationships($hasLogo));
        $zip->addFromString('word/document.xml', self::documentXml($hasLogo));

        if ($hasLogo) {
            $zip->addFile($logoPath, 'word/media/logo.png');
        }

        $zip->close();

        $contents = file_get_contents($temporary);
        @unlink($temporary);

        return $contents ?: '';
    }

    private static function contentTypes(bool $hasLogo): string
    {
        $imageType = $hasLogo ? '<Default Extension="png" ContentType="image/png"/>' : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .$imageType
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'</Types>';
    }

    private static function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'</Relationships>';
    }

    private static function documentRelationships(bool $hasLogo): string
    {
        $image = $hasLogo
            ? '<Relationship Id="rIdLogo" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/logo.png"/>'
            : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$image
            .'</Relationships>';
    }

    private static function documentXml(bool $hasLogo): string
    {
        $logoRun = $hasLogo ? self::logoDrawing() : '<w:t>[Logo JDS]</w:t>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            .'<w:body>'
            .'<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders><w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/><w:insideH w:val="nil"/><w:insideV w:val="nil"/></w:tblBorders></w:tblPr><w:tr>'
            .'<w:tc><w:tcPr><w:tcW w:w="1800" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>'.$logoRun.'</w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="7600" w:type="dxa"/></w:tcPr>'
            .self::paragraph('UNIVERSITAS JDS', true, 32)
            .self::paragraph('LEMBAGA PENJAMINAN MUTU', true, 24)
            .self::paragraph('Jl. Contoh Kampus JDS No. 10, Kota Pendidikan 12345', false, 22)
            .self::paragraph('Telp. (021) 555-0199 | Email: lpm@universitasjds.test | www.universitasjds.test', false, 22)
            .'</w:tc></w:tr></w:tbl>'
            .self::paragraph(str_repeat('_', 95))
            .self::paragraph('Template kop surat. Silakan ubah identitas, alamat, dan kontak sesuai institusi Anda.')
            .'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1021" w:right="907" w:bottom="1021" w:left="907" w:header="708" w:footer="708" w:gutter="0"/></w:sectPr>'
            .'</w:body></w:document>';
    }

    private static function paragraph(string $text, bool $bold = false, int $size = 20): string
    {
        $boldTag = $bold ? '<w:b/>' : '';

        return '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr>'.$boldTag.'<w:sz w:val="'.$size.'"/></w:rPr><w:t>'.self::xml($text).'</w:t></w:r></w:p>';
    }

    private static function logoDrawing(): string
    {
        return '<w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="914400" cy="914400"/><wp:docPr id="1" name="Logo JDS"/><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="0" name="Logo JDS"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="rIdLogo"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="914400" cy="914400"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing>';
    }

    private static function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}

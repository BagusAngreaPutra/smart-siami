<?php

namespace App\Support;

class ReportLetterhead
{
    /**
     * @return array<string, mixed>
     */
    public static function settings(): array
    {
        return [
            ...reportLetterheadSettings(),
            'institution_font_size' => (int) getSetting('report_letterhead_institution_font_size', '16'),
            'unit_font_size' => (int) getSetting('report_letterhead_unit_font_size', '14'),
            'address_font_size' => (int) getSetting('report_letterhead_address_font_size', '11'),
            'institution_bold' => getSetting('report_letterhead_institution_bold', '1') === '1',
            'unit_bold' => getSetting('report_letterhead_unit_bold', '1') === '1',
            'address_bold' => getSetting('report_letterhead_address_bold', '0') === '1',
            'logo_width' => (int) getSetting('report_letterhead_logo_width', '88'),
        ];
    }

    public static function logoPath(): string
    {
        return resource_path('assets/logo JDS tanpa company.png');
    }

    /**
     * @return array{data:string,width:int,height:int}|null
     */
    public static function jpegHeader(int $targetWidth = 900): ?array
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $settings = self::settings();
        $width = max(680, $targetWidth);
        $height = 170;
        $image = imagecreatetruecolor($width, $height);
        if (! $image) {
            return null;
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 17, 24, 39);
        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        $logoAreaWidth = 150;
        $logoMax = max(54, min(120, (int) $settings['logo_width']));
        $logoPath = self::logoPath();
        if (is_file($logoPath)) {
            $logo = @imagecreatefrompng($logoPath);
            if ($logo) {
                $sourceWidth = imagesx($logo);
                $sourceHeight = imagesy($logo);
                $scale = min($logoMax / max(1, $sourceWidth), $logoMax / max(1, $sourceHeight));
                $logoWidth = (int) round($sourceWidth * $scale);
                $logoHeight = (int) round($sourceHeight * $scale);
                $logoX = (int) round(($logoAreaWidth - $logoWidth) / 2);
                $logoY = 26 + (int) round((102 - $logoHeight) / 2);
                imagealphablending($image, true);
                imagecopyresampled($image, $logo, $logoX, $logoY, 0, 0, $logoWidth, $logoHeight, $sourceWidth, $sourceHeight);
                imagedestroy($logo);
            }
        }

        $textX = $logoAreaWidth + 8;
        $textWidth = $width - $textX - 20;
        $centerX = $textX + (int) floor($textWidth / 2);
        $y = 32;

        $y = self::drawCentered($image, (string) $settings['institution'], $centerX, $y, (int) $settings['institution_font_size'], (bool) $settings['institution_bold'], $black) + 3;
        if (! empty($settings['unit'])) {
            $y = self::drawCentered($image, (string) $settings['unit'], $centerX, $y, (int) $settings['unit_font_size'], (bool) $settings['unit_bold'], $black) + 4;
        }

        foreach (self::addressLines($settings) as $line) {
            $y = self::drawCentered($image, $line, $centerX, $y, (int) $settings['address_font_size'], (bool) $settings['address_bold'], $black) + 2;
        }

        $lineY = 146;
        imagesetthickness($image, 3);
        imageline($image, 0, $lineY, $width, $lineY, $black);
        imagesetthickness($image, 1);
        imageline($image, 0, $lineY + 5, $width, $lineY + 5, $black);

        ob_start();
        imagejpeg($image, null, 92);
        $data = (string) ob_get_clean();
        imagedestroy($image);

        return ['data' => $data, 'width' => $width, 'height' => $height];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<int, string>
     */
    public static function addressLines(array $settings): array
    {
        return collect([
            ...preg_split('/\r\n|\r|\n/', (string) ($settings['address'] ?? '')),
            ...preg_split('/\r\n|\r|\n/', (string) ($settings['contact'] ?? '')),
        ])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private static function drawCentered($image, string $text, int $centerX, int $baselineY, int $fontSize, bool $bold, int $color): int
    {
        $font = self::fontPath($bold);
        if ($font && function_exists('imagettftext')) {
            $box = imagettfbbox($fontSize, 0, $font, $text);
            $textWidth = abs(($box[2] ?? 0) - ($box[0] ?? 0));
            imagettftext($image, $fontSize, 0, $centerX - (int) floor($textWidth / 2), $baselineY, $color, $font, $text);

            return $baselineY + $fontSize + 3;
        }

        $fontId = 5;
        $textWidth = imagefontwidth($fontId) * strlen($text);
        imagestring($image, $fontId, $centerX - (int) floor($textWidth / 2), $baselineY - 12, $text, $color);

        return $baselineY + 18;
    }

    private static function fontPath(bool $bold): ?string
    {
        $paths = $bold
            ? ['C:\Windows\Fonts\arialbd.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf']
            : ['C:\Windows\Fonts\arial.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}

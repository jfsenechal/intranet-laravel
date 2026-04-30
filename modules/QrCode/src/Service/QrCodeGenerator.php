<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Service;

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use AcMarche\QrCode\Models\QrCode as QrCodeModel;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeFacade;

final class QrCodeGenerator
{
    /**
     * Render a QR code as a string (SVG markup or PNG binary)
     * based on the model's configured action and styling.
     */
    public function render(QrCodeModel $qrCode): string
    {
        $payload = $this->buildPayload($qrCode);

        $rgb = $this->hexToRgb($qrCode->color ?? '#000000');
        $bgRgb = $this->hexToRgb($qrCode->background_color ?? '#FFFFFF');
        $format = mb_strtolower($qrCode->format ?? 'svg');
        $style = $qrCode->style ?? 'square';

        return (string) QrCodeFacade::format($format)
            ->size((int) ($qrCode->pixels ?? 400))
            ->margin((int) ($qrCode->margin ?? 10))
            ->style($style)
            ->color($rgb[0], $rgb[1], $rgb[2])
            ->backgroundColor($bgRgb[0], $bgRgb[1], $bgRgb[2])
            ->errorCorrection('H')
            ->generate($payload);
    }

    public function mimeType(QrCodeModel $qrCode): string
    {
        return match (mb_strtolower($qrCode->format ?? 'svg')) {
            'png' => 'image/png',
            'eps' => 'application/postscript',
            default => 'image/svg+xml',
        };
    }

    public function extension(QrCodeModel $qrCode): string
    {
        return mb_strtolower($qrCode->format ?? 'svg');
    }

    public function buildPayload(QrCodeModel $qrCode): string
    {
        return match ($qrCode->action) {
            QrCodeActionEnum::URL,
            QrCodeActionEnum::TEXT => (string) ($qrCode->message ?? ''),

            QrCodeActionEnum::PHONE_NUMBER => 'tel:'.($qrCode->phone_number ?? ''),

            QrCodeActionEnum::SMS => 'SMSTO:'.($qrCode->phone_number ?? '').':'.($qrCode->message ?? ''),

            QrCodeActionEnum::EMAIL => $this->buildEmail($qrCode),

            QrCodeActionEnum::WIFI => $this->buildWifi($qrCode),

            QrCodeActionEnum::GEO => sprintf(
                'geo:%s,%s',
                $qrCode->latitude ?? '0',
                $qrCode->longitude ?? '0',
            ),

            QrCodeActionEnum::EPC => $this->buildEpc($qrCode),
        };
    }

    private function buildEmail(QrCodeModel $qrCode): string
    {
        $email = 'mailto:'.($qrCode->email ?? '');
        $params = array_filter([
            'subject' => $qrCode->subject,
            'body' => $qrCode->message,
        ]);

        if ($params !== []) {
            $email .= '?'.http_build_query($params);
        }

        return $email;
    }

    private function buildWifi(QrCodeModel $qrCode): string
    {
        $encryption = $qrCode->encryption ?: 'nopass';
        $ssid = $this->escapeWifi($qrCode->ssid ?? '');
        $password = $this->escapeWifi($qrCode->password ?? '');
        $hidden = $qrCode->network_hidden ? 'true' : 'false';

        return sprintf('WIFI:T:%s;S:%s;P:%s;H:%s;;', $encryption, $ssid, $password, $hidden);
    }

    /**
     * EPC QR code (SEPA credit transfer) — version 002.
     */
    private function buildEpc(QrCodeModel $qrCode): string
    {
        $iban = preg_replace('/\s+/', '', (string) ($qrCode->iban ?? ''));
        $amount = $qrCode->amount !== null && $qrCode->amount !== ''
            ? 'EUR'.number_format((float) $qrCode->amount, 2, '.', '')
            : '';

        return implode("\n", [
            'BCD',
            '002',
            '1',
            'SCT',
            '',
            (string) ($qrCode->recipient ?? ''),
            (string) $iban,
            $amount,
            '',
            '',
            (string) ($qrCode->message ?? ''),
        ]);
    }

    private function escapeWifi(string $value): string
    {
        return addcslashes($value, '\\;,":');
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = mb_ltrim($hex, '#');

        if (mb_strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (mb_strlen($hex) !== 6) {
            return [0, 0, 0];
        }

        return [
            (int) hexdec(mb_substr($hex, 0, 2)),
            (int) hexdec(mb_substr($hex, 2, 2)),
            (int) hexdec(mb_substr($hex, 4, 2)),
        ];
    }
}

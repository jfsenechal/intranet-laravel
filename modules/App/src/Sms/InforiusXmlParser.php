<?php

declare(strict_types=1);

namespace AcMarche\App\Sms;

use AcMarche\App\Sms\Dto\HistoricLine;
use AcMarche\App\Sms\Dto\HistoricResponse;
use AcMarche\App\Sms\Dto\SmsMessageStatus;
use AcMarche\App\Sms\Dto\SmsResponse;
use AcMarche\App\Sms\Exception\SmsException;
use SimpleXMLElement;

final class InforiusXmlParser
{
    private const NS = 'http://schemas.datacontract.org/2004/07/Inforius.eCommunication.Contract.Message';

    /**
     * @return array{token: string, expiration: int}
     */
    public function parseTokenResponse(string $xmlString): array
    {
        $xml = $this->load($xmlString);

        $error = $this->value($xml->Error);
        if ($error !== null) {
            $code = $this->value($xml->ErrorCode) ?? '';

            throw new SmsException(sprintf('Token request failed [%s]: %s', $code, $error));
        }

        $token = $this->value($xml->Token);
        if ($token === null || $token === '') {
            throw new SmsException('Token request returned no token.');
        }

        return [
            'token' => $token,
            'expiration' => (int) ($this->value($xml->Expiration) ?? 0),
        ];
    }

    public function parseSendResponse(string $xmlString): SmsResponse
    {
        $xml = $this->load($xmlString);

        $messages = [];
        if (isset($xml->Messages->MessageStatus)) {
            foreach ($xml->Messages->MessageStatus as $node) {
                $messages[] = new SmsMessageStatus(
                    number: $this->value($node->Number),
                    type: $this->value($node->Type),
                    errorCode: $this->value($node->ErrorCode),
                    errorMessage: $this->value($node->ErrorMessage),
                    customerReference: $this->value($node->CustomerReference),
                );
            }
        }

        return new SmsResponse(
            error: $this->value($xml->Error),
            balance: (float) ($this->value($xml->Balance) ?? 0),
            messages: $messages,
        );
    }

    public function parseHistoricResponse(string $xmlString): HistoricResponse
    {
        $xml = $this->load($xmlString);

        $error = $this->value($xml->Error);
        if ($error !== null) {
            return new HistoricResponse(error: $error);
        }

        $xml->registerXPathNamespace('ns', self::NS);
        $lineNodes = $xml->xpath('//ns:HistoricLine') ?: [];

        $lines = [];
        foreach ($lineNodes as $node) {
            $lines[] = new HistoricLine(
                date: $this->value($node->date),
                recipient: $this->value($node->recipient),
                user: $this->value($node->user),
                type: $this->value($node->type),
                ackDate: $this->value($node->ackDate),
                statusText: $this->value($node->statusText),
                estimatedCost: (float) ($this->value($node->estimatedCost) ?? 0),
                realCost: (float) ($this->value($node->realCost) ?? 0),
                targetCountry: $this->value($node->targetCountry),
                group: $this->value($node->group),
                content: $this->value($node->content),
                customerReference: $this->value($node->CustomerReference),
            );
        }

        return new HistoricResponse(lines: $lines);
    }

    private function load(string $xmlString): SimpleXMLElement
    {
        $xml = @simplexml_load_string($xmlString, SimpleXMLElement::class, LIBXML_NOCDATA);

        if ($xml === false) {
            throw new SmsException('Failed to parse XML response: '.$xmlString);
        }

        return $xml;
    }

    private function value(?SimpleXMLElement $element): ?string
    {
        if ($element === null) {
            return null;
        }

        $attributes = $element->attributes('i', true);
        if (isset($attributes['nil']) && (string) $attributes['nil'] === 'true') {
            return null;
        }

        $value = mb_trim((string) $element);

        return $value === '' ? null : $value;
    }
}

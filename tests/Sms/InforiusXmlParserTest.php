<?php

declare(strict_types=1);

use AcMarche\App\Sms\Exception\SmsException;
use AcMarche\App\Sms\InforiusXmlParser;

uses(PHPUnit\Framework\TestCase::class);

beforeEach(function (): void {
    $this->parser = new InforiusXmlParser;
});

test('parseTokenResponse returns token and expiration', function (): void {
    $xml = <<<'XML'
<RequestTokenResponse xmlns="http://schemas.datacontract.org/2004/07/Inforius.eCommunication.Contract.Message" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Expiration>1200000</Expiration>
    <Token>abc-123</Token>
</RequestTokenResponse>
XML;

    $result = $this->parser->parseTokenResponse($xml);

    expect($result)->toBe(['token' => 'abc-123', 'expiration' => 1200000]);
});

test('parseTokenResponse throws when error is present', function (): void {
    $xml = <<<'XML'
<RequestTokenResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error>Invalid credentials</Error>
    <ErrorCode>E3</ErrorCode>
</RequestTokenResponse>
XML;

    $this->parser->parseTokenResponse($xml);
})->throws(SmsException::class, 'Invalid credentials');

test('parseSendResponse extracts balance and message statuses', function (): void {
    $xml = <<<'XML'
<SendMessageResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Balance>456</Balance>
    <Messages>
        <MessageStatus>
            <ErrorCode i:nil="true"/>
            <ErrorMessage i:nil="true"/>
            <Number>+32476000000</Number>
            <Type>S</Type>
            <CustomerReference>ref-1</CustomerReference>
        </MessageStatus>
    </Messages>
</SendMessageResponse>
XML;

    $response = $this->parser->parseSendResponse($xml);

    expect($response->error)->toBeNull()
        ->and($response->balance)->toBe(456.0)
        ->and($response->messages)->toHaveCount(1)
        ->and($response->messages[0]->number)->toBe('+32476000000')
        ->and($response->messages[0]->customerReference)->toBe('ref-1')
        ->and($response->isSuccessful())->toBeTrue();
});

test('parseHistoricResponse extracts historic lines', function (): void {
    $xml = <<<'XML'
<HistoricResponse xmlns="http://schemas.datacontract.org/2004/07/Inforius.eCommunication.Contract.Message" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <content>
        <HistoricLine>
            <date>2026-01-01T10:00:00</date>
            <recipient>+32476000000</recipient>
            <user>grhmarche_new</user>
            <type>S</type>
            <ackDate>2026-01-01T10:00:02</ackDate>
            <statusText>Sent</statusText>
            <estimatedCost>1.5</estimatedCost>
            <realCost>1.5</realCost>
            <targetCountry>Belgium</targetCountry>
            <group>HR</group>
            <content>Hello</content>
            <CustomerReference>ref-42</CustomerReference>
        </HistoricLine>
    </content>
</HistoricResponse>
XML;

    $response = $this->parser->parseHistoricResponse($xml);

    expect($response->error)->toBeNull()
        ->and($response->lines)->toHaveCount(1)
        ->and($response->lines[0]->recipient)->toBe('+32476000000')
        ->and($response->lines[0]->statusText)->toBe('Sent')
        ->and($response->lines[0]->estimatedCost)->toBe(1.5)
        ->and($response->lines[0]->customerReference)->toBe('ref-42');
});

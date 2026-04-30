<?php

declare(strict_types=1);

use AcMarche\App\Sms\InforiusClient;
use Illuminate\Http\Client\Factory as HttpFactory;

function smsFake(array $responses): HttpFactory
{
    $http = new HttpFactory;
    $http->fake($responses);

    return $http;
}

function buildClient(HttpFactory $http, ?string $sender = null): InforiusClient
{
    return new InforiusClient(
        host: 'https://ecom.example.test/Api/',
        user: 'test_user',
        password: 'secret',
        sender: $sender,
        http: $http,
    );
}

test('sendSms requests a token then posts the message', function (): void {
    $http = smsFake([
        'ecom.example.test/Api/RequestToken' => HttpFactory::response(
            <<<'XML'
<RequestTokenResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Expiration>1200000</Expiration>
    <Token>token-xyz</Token>
</RequestTokenResponse>
XML
        ),
        'ecom.example.test/Api/Send' => HttpFactory::response(
            <<<'XML'
<SendMessageResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Balance>500</Balance>
    <Messages>
        <MessageStatus>
            <ErrorCode i:nil="true"/>
            <ErrorMessage i:nil="true"/>
            <Number>+32476123456</Number>
            <Type>S</Type>
        </MessageStatus>
    </Messages>
</SendMessageResponse>
XML
        ),
    ]);

    $response = buildClient($http)->sendSms('0476 12 34 56', 'Hello world');

    expect($response->isSuccessful())->toBeTrue()
        ->and($response->balance)->toBe(500.0)
        ->and($response->messages)->toHaveCount(1);

    $http->assertSent(fn ($request) => str_ends_with($request->url(), '/RequestToken'));
    $http->assertSent(function ($request): bool {
        if (! str_ends_with($request->url(), '/Send')) {
            return false;
        }
        $body = $request->body();

        return str_contains($body, '<token>token-xyz</token>')
            && str_contains($body, '<user>test_user</user>')
            && str_contains($body, 'Hello+world')
            && str_contains($body, urlencode('+32476123456'));
    });
});

test('sendSms reuses a cached token between calls', function (): void {
    $http = smsFake([
        'ecom.example.test/Api/RequestToken' => HttpFactory::response(
            <<<'XML'
<RequestTokenResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Expiration>1200000</Expiration>
    <Token>token-xyz</Token>
</RequestTokenResponse>
XML
        ),
        'ecom.example.test/Api/Send' => HttpFactory::response(
            <<<'XML'
<SendMessageResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Balance>499</Balance>
    <Messages/>
</SendMessageResponse>
XML
        ),
    ]);

    $client = buildClient($http);
    $client->sendSms('0476123456', 'first');
    $client->sendSms('0476123456', 'second');

    $http->assertSentCount(3);
});

test('sendSms includes a sender tag when configured', function (): void {
    $http = smsFake([
        'ecom.example.test/Api/RequestToken' => HttpFactory::response(
            <<<'XML'
<RequestTokenResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Expiration>1200000</Expiration>
    <Token>token-xyz</Token>
</RequestTokenResponse>
XML
        ),
        'ecom.example.test/Api/Send' => HttpFactory::response(
            '<SendMessageResponse><Balance>0</Balance><Messages/></SendMessageResponse>'
        ),
    ]);

    buildClient($http, sender: 'MarcheAC')->sendSms('0476123456', 'hi');

    $http->assertSent(fn ($request) => str_ends_with($request->url(), '/Send')
        && str_contains($request->body(), '<sender>MarcheAC</sender>'));
});

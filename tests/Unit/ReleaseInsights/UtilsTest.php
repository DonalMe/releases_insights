<?php

declare(strict_types=1);

use Cache\Cache;
use \DateTime\Datime;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use ReleaseInsights\Utils as U;

test('Utils::isBuildID', function () {
    $this->assertFalse(U::isBuildID('1234587392871'));
    $this->assertFalse(U::isBuildID('123458739287122'));
    $this->assertFalse(U::isBuildID('12345873928712'));
    $this->assertFalse(U::isBuildID('20501229120000'));
    $this->assertTrue(U::isBuildID('20201229120000'));
    $this->assertFalse(U::isBuildID('20501229120000'));
    $this->assertTrue(U::isBuildID('20201229120000'));
    $this->assertTrue(U::isBuildID('20220220120000'));
    // Invalid date
    $this->assertFalse(U::isBuildID('99999999999999'));

    // Today is a valid date
    $this->assertTrue(U::isBuildID(
        (new DateTime())->format('Ymdhhs')
    ));
});

test('Utils::getBuildID', function () {
    // Test fallback value
    $this->assertEquals(20191014213051, U::getBuildID(20501229120000));

    // Test good value
    $this->assertEquals(20201229120000, U::getBuildID(20201229120000));
});

test('Utils::secureText', function ($input, $output) {
    expect($output)->toEqual(U::secureText($input));
})->with([
    ["achat des couteaux\nsuisses", 'achat des couteaux suisses'],
    ['<b>foo</b>', '&#60;b&#62;foo&#60;/b&#62;'],
    ['<b>foo%0D</b>', '&#60;b&#62;foo&#60;/b&#62;'],
    ['<b>foo%0A</b>', '&#60;b&#62;foo&#60;/b&#62;'],
    [null, ''],
]);

test('Utils::getDate', function () {

    // No GET parameter, Today
    $this->assertEquals(date('Ymd'), U::getDate());

    $_GET['date'] = 'today';
    $this->assertEquals(date('Ymd'), U::getDate());

    // Not a date format
    $_GET['date'] = '5a ';
    $this->assertEquals(date('Ymd'), U::getDate());

    // Invalid, there is a space
    $_GET['date'] = '20191231 ';
    $this->assertEquals(date('Ymd'), U::getDate());

    // Valid date
    $_GET['date'] = '20210912';
    $this->assertEquals('20210912', U::getDate());
    unset($_GET['date']);
});

test('Utils::mtrim', function ($input, $output) {
    expect($output)->toEqual(U::mtrim($input));
})->with([
    ['Le cheval  blanc ', 'Le cheval blanc'],
    ['  Le cheval  blanc', 'Le cheval blanc'],
    ['  Le cheval  blanc  ', 'Le cheval blanc'],
    ['Le cheval  blanc', 'Le cheval blanc'],
]);

test('Utils::startsWith', function ($input, $matches, $result) {
    expect($result)->toEqual(U::startsWith($input, $matches));
})->with([
    ['it is raining', 'it', true],
    [' foobar starts with a nasty space', 'foobar', false],
    ['multiple matches test', ['horse', 'multiple'], true],
    ['multiple matches test', ['not', 'there'], false],
]);

test('Utils::isDateBetweenDates', function ($date, $startDate, $endDate, $result) {
    expect(U::isDateBetweenDates(
        new DateTime($date),
        new DateTime($startDate),
        new DateTime($endDate)
    ))->toEqual($result);
})->with([
    ['2022-01-10', '2022-01-05', '2022-01-15', true],
    ['2022-01-01', '2022-01-05', '2022-01-15', false],
    ['2022-01-10', '2022-01-05', '2022-01-09', false],
]);


test('Utils::inString', function ($a, $b, $c, $d) {
    expect(U::inString($a, $b, $c))->toEqual($d);
})->with([
    ['La maison est blanche', 'blanche', false, true],
    ['La maison est blanche', 'blanche', true, true],
    ['La maison est blanche', ['blanche', 'maison'], true, true],
    ['La maison est blanche', ['blanche', 'maison'], false, true],
    ['La maison est blanche', ['blanche', 'noire'], true, false],
    ['La maison est blanche', ['blanche', 'noire'], false, true],
    ['Le ciel est bleu', 'noir', false, false],
    ['Le ciel est bleu', 'Le', false, true],
]);

test('Utils::getCrashesForBuildID', function () {
    expect(U::getCrashesForBuildID(20190927094817))->toBeArray()->toBe(['error' => 'URL provided no data']);
    expect(U::getCrashesForBuildID(20200927094817))->toBeArray()->not->toBeEmpty();
});

test('Utils::getBugsforCrashSignature', function () {
    expect(U::getBugsforCrashSignature('failure'))
        ->toBeArray()
        ->toBeEmpty();
    expect(U::getBugsforCrashSignature('some signature'))
        ->toBeArray()
        ->not->toBeEmpty();
});

test('Utils::httpStatusLabel', function (?int $status, string $expected) {
    expect(U::httpStatusLabel($status))->toBe($expected);
})->with([
    [null, ''],                 // Not fetched over HTTP, nothing to report
    [0, ' (no response)'],      // DNS, connection or timeout error
    [406, ' (HTTP 406)'],
    [429, ' (HTTP 429)'],
    [503, ' (HTTP 503)'],
]);

test('Utils::httpHeaders', function () {
    expect(U::httpHeaders())
        ->toBeArray()
        ->toBe([
            'User-Agent' => 'WhatTrainIsItNow/1.0',
            'Referer'    => 'https://whattrainisitnow.com',
        ]);
});

/*
    All our external requests must be identifiable, we check the headers on the
    wire with a mock handler instead of trusting the client configuration.
*/
test('Utils::httpClient', function () {
    $sent = [];

    $client = function (array $config = []) use (&$sent) {
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], '{}')]));
        $stack->push(Middleware::history($sent));

        return U::httpClient([...$config, 'handler' => $stack]);
    };

    // Our User-Agent is sent by default
    $client()->get('https://example.com/');
    expect($sent[0]['request']->getHeaderLine('User-Agent'))->toBe('WhatTrainIsItNow/1.0');
    expect($sent[0]['request']->getHeaderLine('Referer'))->toBe('https://whattrainisitnow.com');

    // A caller adding its own headers doesn't lose it
    $client(['headers' => ['X-Custom' => 'yes']])->get('https://example.com/');
    expect($sent[1]['request']->getHeaderLine('User-Agent'))->toBe('WhatTrainIsItNow/1.0');
    expect($sent[1]['request']->getHeaderLine('X-Custom'))->toBe('yes');

    // A caller passing something else than headers doesn't lose it either
    $client(['base_uri' => 'https://example.com/'])->get('foo');
    expect($sent[2]['request']->getHeaderLine('User-Agent'))->toBe('WhatTrainIsItNow/1.0');

    // An explicit override wins, by design
    $client(['headers' => ['User-Agent' => 'Override/9']])->get('https://example.com/');
    expect($sent[3]['request']->getHeaderLine('User-Agent'))->toBe('Override/9');
});

test('Utils::streamContext', function () {
    $options = stream_context_get_options(U::streamContext())['http'];

    expect($options['header'])
        ->toContain('User-Agent: WhatTrainIsItNow/1.0')
        ->toContain('Referer: https://whattrainisitnow.com');
    expect($options['timeout'])->toBe(15);

    // Extra options are merged in and can override our defaults
    $options = stream_context_get_options(U::streamContext([
        'method'  => 'POST',
        'timeout' => 5,
    ]))['http'];

    expect($options['method'])->toBe('POST');
    expect($options['timeout'])->toBe(5);
    expect($options['header'])->toContain('User-Agent: WhatTrainIsItNow/1.0');
});

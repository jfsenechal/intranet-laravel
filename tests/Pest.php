<?php

declare(strict_types=1);

uses(PHPUnit\Framework\TestCase::class)->in('Sms');

uses(
    AcMarche\Hrm\Tests\HrmTestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in(
    '../modules/Hrm/tests/Feature',
    '../modules/Hrm/tests/Unit',
);

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in(
    'Feature',
    'Unit',
    'Browser',
    '../modules/MailingList/tests/Feature',
    '../modules/MailingList/tests/Unit',
    '../modules/MailingList/tests/Browser',
    '../modules/Pst/tests/Feature',
    '../modules/Pst/tests/Unit',
    '../modules/Pst/tests/Browser',
    '../modules/Document/tests/Feature',
    '../modules/Document/tests/Unit',
    '../modules/Document/tests/Browser',
    '../modules/Mileage/tests/Feature',
    '../modules/Mileage/tests/Unit',
    '../modules/News/tests/Feature',
    '../modules/Publication/tests/Feature',
    '../modules/Courrier/tests/Feature',
    '../modules/Courrier/tests/Unit',
    '../modules/QrCode/tests/Unit',
    '../modules/QrCode/tests/Feature',
    '../modules/GuichetHdv/tests/Feature',
);

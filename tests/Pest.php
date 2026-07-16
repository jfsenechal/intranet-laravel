<?php

declare(strict_types=1);

uses(PHPUnit\Framework\TestCase::class)->in('Sms');

uses(
    AcMarche\Hrm\Tests\HrmTestCase::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class,
)->in(
    '../modules/Hrm/tests/Feature',
    '../modules/Hrm/tests/Unit',
    '../modules/Ad/tests/Feature',
    '../modules/Ad/tests/Unit',
);

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class,
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
    '../modules/CpasLibrary/tests/Feature',
    '../modules/CpasLibrary/tests/Unit',
    '../modules/College/tests/Feature',
    '../modules/College/tests/Unit',
    '../modules/ActivityManager/tests/Feature',
    '../modules/ActivityManager/tests/Unit',
    '../modules/StreetWatch/tests/Feature',
    '../modules/StreetWatch/tests/Unit',
    '../modules/Agent/tests/Feature',
    '../modules/Agent/tests/Unit',
    '../modules/Conseil/tests/Filament',
    '../modules/App/tests/Feature',
    '../modules/App/tests/Unit',
);

uses(
    AcMarche\EmailManagement\Tests\EmailManagementTestCase::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class,
)->in(
    '../modules/EmailManagement/tests/Feature',
    '../modules/EmailManagement/tests/Unit',
);

uses(
    AcMarche\Conseil\Tests\ConseilTestCase::class,
)->in(
    '../modules/Conseil/tests/Feature',
);

uses(
    Tests\MealDeliveryTestCase::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class,
)->in(
    '../modules/MealDelivery/tests/Feature',
    '../modules/MealDelivery/tests/Unit',
);

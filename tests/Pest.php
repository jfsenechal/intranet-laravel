<?php

declare(strict_types=1);

uses(PHPUnit\Framework\TestCase::class)->in('Sms');

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\LazilyRefreshDatabase::class,
)->in(
    '../modules/Hrm/tests/Feature',
    '../modules/Hrm/tests/Unit',
    '../modules/Ad/tests/Feature',
    '../modules/Ad/tests/Unit',
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
    '../modules/Note/tests/Feature',
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
    '../modules/MealDelivery/tests/Feature',
    '../modules/MealDelivery/tests/Unit',
    '../modules/WhoIsWho/tests/Feature',
    '../modules/Offenses/tests/Feature',
    '../modules/Issep/tests/Feature',
    '../modules/Issep/tests/Unit',
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

/**
 * Reads back the raw bytes of an XLSX export, header row included. Livewire
 * captures a `StreamedResponse` returned by an action into the `download`
 * effect, so a test reaches the bytes with
 * `base64_decode(data_get($component->effects, 'download.content'))`.
 *
 * @return list<list<string>>
 */
function xlsxRows(string $content): array
{
    $path = tempnam(sys_get_temp_dir(), 'xlsx');
    file_put_contents($path, $content);

    $reader = new OpenSpout\Reader\XLSX\Reader();
    $reader->open($path);

    $rows = [];
    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = array_map(strval(...), $row->toArray());
        }

        break;
    }

    $reader->close();
    unlink($path);

    return $rows;
}

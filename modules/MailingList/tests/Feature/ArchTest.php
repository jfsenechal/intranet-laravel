<?php

declare(strict_types=1);

arch('All files in the casts directory extend `CastsAttributes`')
    ->expect('AcMarche\MailingList\Casts')
    ->toExtend('Illuminate\Contracts\Database\Eloquent\CastsAttributes');

arch('All files in the casts directory have suffix `Cast`')
    ->expect('AcMarche\MailingList\Casts')
    ->toHaveSuffix('Cast');

arch('All files in the observers directory have suffix `Observer`')
    ->expect('AcMarche\MailingList\Observers')
    ->toHaveSuffix('Observer');

arch('All files in the policies directory have suffix `Policy`')
    ->expect('AcMarche\MailingList\Policies')
    ->toHaveSuffix('Policy');

arch('All files in the services directory have suffix `Service`')
    ->expect('AcMarche\MailingList\Services')
    ->toHaveSuffix('Service');

arch('ensures `env()` is only used in config files')
    ->expect('env')
    ->not->toBeUsed()
    ->ignoring('config');

arch('No file in the app directory uses `die`, `dd`, or `dump`.')
    ->expect('AcMarche\MailingList')
    ->not->toUse(['die', 'dd', 'dump', 'ray']);

<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\ListOuEnum;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use AcMarche\EmailManagement\Repository\ListLdapRepository;

/**
 * orderBy() asks Active Directory for a server-side sort, and the directory returns the sort key
 * stripped from every entry unless it is named in the selection alongside '*'. Both repositories
 * sort by the attribute that identifies their entries, so losing it is silent and severe: the
 * lists table keys its rows by cn and collapsed all of them into a single row.
 *
 * This is asserted against the query rather than against returned entries because DirectoryEmulator
 * does not reproduce the stripping — it answers orderBy('cn') with cn intact, which is why the
 * behaviour tests stayed green while the page was broken against the real directory.
 */
it('selects the sort key it orders lists by', function (): void {
    $query = (new ReflectionMethod(ListLdapRepository::class, 'query'))
        ->invoke(new ListLdapRepository, ListOuEnum::LISTS);

    expect($query->getSelects())->toContain('cn');
});

it('selects the sort key it orders employes by', function (): void {
    $query = (new ReflectionMethod(EmployeLdapRepository::class, 'query'))
        ->invoke(new EmployeLdapRepository);

    expect($query->getSelects())->toContain('samaccountname');
});

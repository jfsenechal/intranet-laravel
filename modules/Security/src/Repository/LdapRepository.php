<?php

declare(strict_types=1);

namespace AcMarche\Security\Repository;

use AcMarche\Security\Ldap\EntryLdap;
use AcMarche\Security\Ldap\UserLdap;
use LdapRecord\Models\Model;
use LdapRecord\Query\Collection;

final class LdapRepository
{
    public static function allUsers(): Collection
    {
        return UserLdap::all();
    }

    public static function findByUsername(string $username): ?Model
    {
        return UserLdap::query()->findBy('sAMAccountName', $username);
    }

    public static function existsByUsername(?string $username): bool
    {
        if ($username === null || $username === '') {
            return false;
        }

        return self::findByUsername($username) instanceof Model;
    }

    public static function lists(): Collection
    {
        return EntryLdap::query()
            ->in(config('security.ldap.lists_dn'))
            ->whereHas('mail')
            ->orderBy('sAMAccountName')
            ->get();
    }

    public static function services(): Collection
    {
        return EntryLdap::query()
            ->in(config('security.ldap.services_dn'))
            ->whereHas('mail')
            ->orderBy('sAMAccountName')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public static function listsAsOptions(): array
    {
        $items = self::toOptions(self::lists());

        ksort($items);

        return $items;
    }

    /**
     * @return array<string, string>
     */
    private static function toOptions(Collection $entries): array
    {
        $options = [];
        foreach ($entries as $entry) {
            $email = $entry->getFirstAttribute('mail');
            if ($email === null || $email === '') {
                continue;
            }

            $options[$email] = $email;
        }

        return $options;
    }
}

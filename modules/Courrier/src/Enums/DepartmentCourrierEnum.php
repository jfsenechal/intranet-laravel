<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Enums;

enum DepartmentCourrierEnum: string
{
    case BGM = 'Bgm';
    case VILLE = 'Ville';
    case CPAS = 'Cpas';

    public static function toArray(): array
    {
        $values = [];
        foreach (self::cases() as $actionStateEnum) {
            $values[] = $actionStateEnum->value;
        }

        return $values;
    }

    /**
     * Human-friendly label for display in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::BGM => 'Bourgmestre',
            self::VILLE => 'Ville',
            self::CPAS => 'CPAS',
        };
    }

    /**
     * The IMAP mailbox name registered for this department (e.g. `imap_ville`).
     */
    public function imapMailbox(): string
    {
        return 'imap_'.mb_strtolower($this->value);
    }

    /**
     * The configured mailbox email address for this department, if any.
     */
    public function imapEmail(): ?string
    {
        return config('courrier.imap.'.mb_strtolower($this->value).'.email');
    }
}

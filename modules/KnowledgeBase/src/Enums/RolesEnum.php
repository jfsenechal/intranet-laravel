<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_KNOWLEDGE_BASE_ADMIN = 'ROLE_KNOWLEDGE_BASE_ADMIN';
    case ROLE_KNOWLEDGE_BASE = 'ROLE_KNOWLEDGE_BASE';

    /**
     * @return array<string, string>
     */
    public static function getRoles(): array
    {
        $roles = [];
        foreach (self::cases() as $role) {
            $roles[$role->value] = $role->value;
        }

        return $roles;
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ROLE_KNOWLEDGE_BASE_ADMIN => 'Base de connaissances (édition)',
            self::ROLE_KNOWLEDGE_BASE => 'Base de connaissances (lecture)',
        };
    }
}

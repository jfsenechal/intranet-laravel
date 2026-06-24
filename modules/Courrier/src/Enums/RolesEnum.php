<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Enums;

enum RolesEnum: string
{
    case ROLE_INDICATEUR_CPAS = 'ROLE_INDICATEUR_CPAS';
    case ROLE_INDICATEUR_CPAS_ADMIN = 'ROLE_INDICATEUR_CPAS_ADMIN';
    case ROLE_INDICATEUR_CPAS_INDEX = 'ROLE_INDICATEUR_CPAS_INDEX';
    case ROLE_INDICATEUR_CPAS_READ = 'ROLE_INDICATEUR_CPAS_READ';

    case ROLE_INDICATEUR_BOURGMESTRE = 'ROLE_INDICATEUR_BOURGMESTRE';
    case ROLE_INDICATEUR_BOURGMESTRE_ADMIN = 'ROLE_INDICATEUR_BOURGMESTRE_ADMIN';
    case ROLE_INDICATEUR_BOURGMESTRE_INDEX = 'ROLE_INDICATEUR_BOURGMESTRE_INDEX';
    case ROLE_INDICATEUR_BOURGMESTRE_READ = 'ROLE_INDICATEUR_BOURGMESTRE_READ';

    case ROLE_INDICATEUR_VILLE = 'ROLE_INDICATEUR_VILLE';
    case ROLE_INDICATEUR_VILLE_ADMIN = 'ROLE_INDICATEUR_VILLE_ADMIN';
    case ROLE_INDICATEUR_VILLE_INDEX = 'ROLE_INDICATEUR_VILLE_INDEX';
    case ROLE_INDICATEUR_VILLE_READ = 'ROLE_INDICATEUR_VILLE_READ';

    public static function getRoles(): array
    {
        return array_values(self::cases());
    }

    public static function getAdminRoles(): array
    {
        return [
            self::ROLE_INDICATEUR_BOURGMESTRE_ADMIN,
            self::ROLE_INDICATEUR_VILLE_ADMIN,
            self::ROLE_INDICATEUR_CPAS_ADMIN,
        ];
    }

    public static function getIndexRoles(): array
    {
        return [
            self::ROLE_INDICATEUR_BOURGMESTRE_INDEX,
            self::ROLE_INDICATEUR_VILLE_INDEX,
            self::ROLE_INDICATEUR_CPAS_INDEX,
        ];
    }

    public function getDepartmentAdmin(): ?DepartmentCourrierEnum
    {
        return match ($this) {
            self::ROLE_INDICATEUR_BOURGMESTRE_ADMIN => DepartmentCourrierEnum::BGM,
            self::ROLE_INDICATEUR_VILLE_ADMIN => DepartmentCourrierEnum::VILLE,
            self::ROLE_INDICATEUR_CPAS_ADMIN => DepartmentCourrierEnum::CPAS,
            default => null,
        };
    }

    public function getDepartmentIndex(): ?DepartmentCourrierEnum
    {
        return match ($this) {
            self::ROLE_INDICATEUR_BOURGMESTRE_INDEX => DepartmentCourrierEnum::BGM,
            self::ROLE_INDICATEUR_VILLE_INDEX => DepartmentCourrierEnum::VILLE,
            self::ROLE_INDICATEUR_CPAS_INDEX => DepartmentCourrierEnum::CPAS,
            default => null,
        };
    }

    public function getDepartmentRead(): ?DepartmentCourrierEnum
    {
        return match ($this) {
            self::ROLE_INDICATEUR_BOURGMESTRE_READ => DepartmentCourrierEnum::BGM,
            self::ROLE_INDICATEUR_VILLE_READ => DepartmentCourrierEnum::VILLE,
            self::ROLE_INDICATEUR_CPAS_READ => DepartmentCourrierEnum::CPAS,
            default => null,
        };
    }
}

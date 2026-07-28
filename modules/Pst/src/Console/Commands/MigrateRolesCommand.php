<?php

declare(strict_types=1);

namespace AcMarche\Pst\Console\Commands;

use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Providers\PstServiceProvider;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Repository\ModuleRepository;
use App\Models\User;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

/**
 * The legacy PST application kept its own users, roles and role_user tables.
 * Those tables no longer exist, only the dump in data/pst.sql remains, so the
 * roles are rebuilt from that dump and attached to the intranet users.
 */
final class MigrateRolesCommand extends Command
{
    /**
     * Legacy role name => role name inside the PST module.
     *
     * @var array<string, string>
     */
    private const array ROLE_MAP = [
        'ROLE_ADMIN' => RolesEnum::ADMIN->value,
        'ROLE_MANDATAIRE' => RolesEnum::MANDATAIRE->value,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    #[Override]
    protected $signature = 'pst:migrate-roles
        {--file=data/pst.sql : Path to the legacy dump, relative to the project root when not absolute}
        {--dry-run : Show what would be attached without touching the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Give the PST roles to the users of the legacy PST application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $file = $this->resolveDumpPath();

        if (! is_readable($file)) {
            $this->error("Legacy dump not found: {$file}");

            return SfCommand::FAILURE;
        }

        if (! ($module = ModuleRepository::find(PstServiceProvider::$module_id)) instanceof Module) {
            $this->error('Pst module '.PstServiceProvider::$module_id.' not found, run intranet:sync-roles first.');

            return SfCommand::FAILURE;
        }

        $roles = $this->rolesOfModule($module);

        $missingRoles = array_diff(RolesEnum::toArray(), array_keys($roles));

        if ($missingRoles !== []) {
            $this->error('Roles missing in database ('.implode(', ', $missingRoles).'), run intranet:sync-roles first.');

            return SfCommand::FAILURE;
        }

        $sql = (string) file_get_contents($file);
        $rolesToAttach = $this->rolesToAttachByUsername($sql);

        if ($rolesToAttach === []) {
            $this->warn('No user found in the legacy dump.');

            return SfCommand::SUCCESS;
        }

        $this->info(count($rolesToAttach).' legacy users found in '.$file);

        $dryRun = (bool) $this->option('dry-run');
        $attached = 0;
        $skipped = 0;
        $unknownUsers = [];

        $progressBar = $this->output->createProgressBar(count($rolesToAttach));
        $progressBar->start();

        foreach ($rolesToAttach as $username => $roleNames) {
            $user = User::where('username', $username)->first();

            if (! $user instanceof User) {
                $unknownUsers[] = $username;
                $progressBar->advance();

                continue;
            }

            foreach ($roleNames as $roleName) {
                if ($user->hasRole($roleName)) {
                    $skipped++;

                    continue;
                }

                if (! $dryRun) {
                    $user->addRole($roles[$roleName]);
                }

                $this->line(($dryRun ? '  [dry-run] ' : '  ').$username.' => '.$roleName, verbosity: 'v');
                $attached++;
            }

            if (! $dryRun) {
                $user->addModule($module);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info(($dryRun ? '[dry-run] ' : '').'✓ Role migration completed!');
        $this->info("  Attached: {$attached} roles");

        if ($skipped > 0) {
            $this->info("  Skipped: {$skipped} roles (already held)");
        }

        if ($unknownUsers !== []) {
            $this->newLine();
            $this->warn(count($unknownUsers).' legacy users have no intranet account:');
            foreach ($unknownUsers as $username) {
                $this->warn("  • {$username}");
            }
        }

        return SfCommand::SUCCESS;
    }

    private function resolveDumpPath(): string
    {
        $file = (string) $this->option('file');

        return str_starts_with($file, '/') ? $file : base_path($file);
    }

    /**
     * @return array<string, Role> role name => role
     */
    private function rolesOfModule(Module $module): array
    {
        return Role::query()
            ->where('module_id', $module->id)
            ->whereIn('name', RolesEnum::toArray())
            ->get()
            ->keyBy('name')
            ->all();
    }

    /**
     * Every legacy user gets ROLE_PST, plus the module counterpart of each
     * legacy role they held.
     *
     * @return array<string, list<string>> username => role names
     */
    private function rolesToAttachByUsername(string $sql): array
    {
        $legacyRoles = [];
        foreach ($this->parseInsertedRows($sql, 'roles') as $row) {
            $legacyRoles[(int) $row['id']] = (string) $row['name'];
        }

        $usernames = [];
        foreach ($this->parseInsertedRows($sql, 'users') as $row) {
            $usernames[(int) $row['id']] = (string) $row['username'];
        }

        $rolesToAttach = [];
        foreach ($usernames as $username) {
            $rolesToAttach[$username] = [RolesEnum::PST->value];
        }

        foreach ($this->parseInsertedRows($sql, 'role_user') as $row) {
            $username = $usernames[(int) $row['user_id']] ?? null;

            if ($username === null) {
                $this->warn('Legacy user '.$row['user_id'].' not found in the dump, skipped.');

                continue;
            }

            $legacyRole = $legacyRoles[(int) $row['role_id']] ?? null;
            $roleName = $legacyRole === null ? null : (self::ROLE_MAP[$legacyRole] ?? null);

            if ($roleName === null) {
                $this->warn('Legacy role '.($legacyRole ?? $row['role_id'])." has no PST counterpart, skipped for {$username}.");

                continue;
            }

            $rolesToAttach[$username][] = $roleName;
        }

        return array_map(
            static fn (array $roleNames): array => array_values(array_unique($roleNames)),
            $rolesToAttach,
        );
    }

    /**
     * Read the rows of the `INSERT INTO <table> (...) VALUES (...),(...);`
     * statements of a mysqldump.
     *
     * @return list<array<string, string|null>>
     */
    private function parseInsertedRows(string $sql, string $table): array
    {
        $rows = [];
        $offset = 0;
        $statement = 'INSERT INTO `'.$table.'`';
        // Character offsets, so that the accented names of the dump line up.
        $characters = mb_str_split($sql);

        while (($start = mb_strpos($sql, $statement, $offset)) !== false) {
            $valuesAt = mb_strpos($sql, 'VALUES', $start);

            if ($valuesAt === false) {
                break;
            }

            $columns = $this->parseColumnNames(mb_substr($sql, $start, $valuesAt - $start));

            foreach ($this->parseValues($characters, $valuesAt + mb_strlen('VALUES'), $offset) as $values) {
                if (count($values) === count($columns)) {
                    $rows[] = array_combine($columns, $values);
                }
            }
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function parseColumnNames(string $header): array
    {
        preg_match_all('/`([^`]+)`/', $header, $matches);

        // The first backticked name is the table itself.
        return array_slice($matches[1], 1);
    }

    /**
     * Walk the value tuples of an INSERT statement, honouring quoted strings so
     * that commas, parentheses and semicolons inside them are left alone.
     *
     * @param  list<string>  $characters  the dump, split per character
     * @param  int  $offset  set to the position right after the closing semicolon
     * @return list<list<string|null>>
     */
    private function parseValues(array $characters, int $start, int &$offset): array
    {
        $rows = [];
        $row = [];
        $buffer = '';
        $quoted = false;
        $inRow = false;
        $length = count($characters);
        $position = $start;

        for (; $position < $length; $position++) {
            $character = $characters[$position];

            if ($quoted) {
                if ($character === '\\') {
                    $buffer .= $this->unescape($characters[++$position] ?? '');

                    continue;
                }

                if ($character === "'") {
                    // A doubled quote is an escaped quote, not the end of the string.
                    if (($characters[$position + 1] ?? '') === "'") {
                        $buffer .= "'";
                        $position++;

                        continue;
                    }

                    $quoted = false;
                    $row[] = $buffer;
                    $buffer = null;

                    continue;
                }

                $buffer .= $character;

                continue;
            }

            if ($character === "'") {
                $quoted = true;
                $buffer = '';

                continue;
            }

            if (! $inRow) {
                if ($character === '(') {
                    $inRow = true;
                    $row = [];
                    $buffer = '';

                    continue;
                }

                if ($character === ';') {
                    break;
                }

                continue;
            }

            if ($character === ',' || $character === ')') {
                if ($buffer !== null) {
                    $row[] = $this->unquotedValue($buffer);
                }
                $buffer = '';

                if ($character === ')') {
                    $rows[] = $row;
                    $inRow = false;
                }

                continue;
            }

            if ($buffer !== null) {
                $buffer .= $character;
            }
        }

        $offset = $position + 1;

        return $rows;
    }

    private function unquotedValue(string $buffer): ?string
    {
        $value = mb_trim($buffer);

        return strcasecmp($value, 'NULL') === 0 ? null : $value;
    }

    private function unescape(string $character): string
    {
        return match ($character) {
            'n' => "\n",
            'r' => "\r",
            't' => "\t",
            '0' => "\0",
            'Z' => "\x1A",
            default => $character,
        };
    }
}

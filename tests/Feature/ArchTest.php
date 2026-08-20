<?php

declare(strict_types=1);

arch('All files in the casts directory extend `CastsAttributes`')
    ->expect('App\Casts')
    ->toExtend('Illuminate\Contracts\Database\Eloquent\CastsAttributes');

arch('All files in the casts directory have suffix `Cast`')
    ->expect('App\Casts')
    ->toHaveSuffix('Cast');

arch('All files in the observers directory have suffix `Observer`')
    ->expect('App\Observers')
    ->toHaveSuffix('Observer');

arch('All files in the policies directory have suffix `Policy`')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

arch('All files in the services directory have suffix `Service`')
    ->expect('App\Services')
    ->toHaveSuffix('Service');

arch('ensures `env()` is only used in config files')
    ->expect('env')
    ->not->toBeUsed()
    ->ignoring('config');

arch('No file in the app directory uses `die`, `dd`, or `dump`.')
    ->expect('App')
    ->not->toUse(['die', 'dd', 'dump', 'ray']);

/**
 * Every PSR-4 prefix declared by the application and by each module package.
 *
 * @return array<int, array{prefix: string, directory: string}>
 */
function autoloadedPsr4Roots(): array
{
    $roots = [];
    $composerFiles = array_merge(
        [base_path('composer.json')],
        glob(base_path('modules/*/composer.json')) ?: [],
    );

    foreach ($composerFiles as $composerFile) {
        $package = json_decode((string) file_get_contents($composerFile), true, flags: JSON_THROW_ON_ERROR);
        $prefixes = array_merge(
            $package['autoload']['psr-4'] ?? [],
            $package['autoload-dev']['psr-4'] ?? [],
        );

        foreach ($prefixes as $prefix => $directories) {
            foreach ((array) $directories as $directory) {
                $path = dirname($composerFile).'/'.mb_trim($directory, '/');

                if (is_dir($path)) {
                    $roots[] = ['prefix' => mb_trim($prefix, '\\'), 'directory' => $path];
                }
            }
        }
    }

    return $roots;
}

/**
 * @return array<int, string>
 */
function phpFilesInDirectory(string $directory): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

/**
 * Reads the namespace a file declares and whether it declares a named class-like symbol.
 *
 * @return array{namespace: string|null, declaresSymbol: bool}
 */
function inspectNamespaceDeclaration(string $file): array
{
    $tokens = token_get_all((string) file_get_contents($file));
    $ignored = [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT];
    $namespace = null;
    $declaresSymbol = false;
    $previous = null;

    foreach ($tokens as $index => $token) {
        if (! is_array($token)) {
            $previous = $token;

            continue;
        }

        if (in_array($token[0], $ignored, true)) {
            continue;
        }

        if ($token[0] === T_NAMESPACE && $namespace === null) {
            $namespace = namespaceNameAt($tokens, $index);
        }

        $isSymbolKeyword = in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true);

        /** `::class` tokenises as `T_CLASS`, and an anonymous class is never autoloaded. */
        $followsDoubleColon = is_array($previous) && $previous[0] === T_DOUBLE_COLON;

        if ($isSymbolKeyword && ! $followsDoubleColon && isNamedDeclarationAt($tokens, $index)) {
            $declaresSymbol = true;
        }

        $previous = $token;
    }

    return ['namespace' => $namespace, 'declaresSymbol' => $declaresSymbol];
}

/**
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 */
function namespaceNameAt(array $tokens, int $index): ?string
{
    $name = '';
    $count = count($tokens);

    for ($position = $index + 1; $position < $count; $position++) {
        $token = $tokens[$position];

        if (is_string($token)) {
            break;
        }

        if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        if (! in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
            break;
        }

        $name .= $token[1];
    }

    return $name !== '' ? mb_trim($name, '\\') : null;
}

/**
 * Tells whether the class-like keyword at $index introduces a *named* declaration
 * rather than an anonymous class.
 *
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 */
function isNamedDeclarationAt(array $tokens, int $index): bool
{
    $count = count($tokens);

    for ($position = $index + 1; $position < $count; $position++) {
        $token = $tokens[$position];

        if (is_string($token)) {
            return false;
        }

        if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        return $token[0] === T_STRING;
    }

    return false;
}

test('every autoloaded class declares the namespace its path maps to', function (): void {
    $violations = [];

    foreach (autoloadedPsr4Roots() as $root) {
        foreach (phpFilesInDirectory($root['directory']) as $file) {
            $declaration = inspectNamespaceDeclaration($file);

            if ($declaration['namespace'] === null && ! $declaration['declaresSymbol']) {
                continue;
            }

            $relativeDirectory = mb_trim(mb_substr(dirname($file), mb_strlen($root['directory'])), '/');
            $expected = $root['prefix'].($relativeDirectory === ''
                ? ''
                : '\\'.str_replace('/', '\\', $relativeDirectory));

            if ($declaration['namespace'] !== $expected) {
                $violations[] = sprintf(
                    '%s declares `%s` but its path maps to `%s`',
                    str_replace(base_path().'/', '', $file),
                    $declaration['namespace'] ?? 'no namespace',
                    $expected,
                );
            }
        }
    }

    $this->assertSame([], $violations, sprintf(
        "%d file(s) are not autoloadable because their namespace does not match their PSR-4 path:\n- %s",
        count($violations),
        implode("\n- ", $violations),
    ));
});

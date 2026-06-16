<?php

namespace Tests\Unit\Architecture;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class ArchitectureConventionsTest extends TestCase
{
    public function test_services_and_actions_do_not_depend_on_http_requests(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Services', 'app/Actions']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (
                str_contains($contents, 'use App\\Http\\Requests\\')
                || str_contains($contents, 'use Illuminate\\Http\\Request;')
                || str_contains($contents, 'request()')
            ) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_services_are_grouped_by_bounded_context(): void
    {
        $violations = [];

        foreach (glob(base_path('app/Services/*.php')) ?: [] as $path) {
            $violations[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
        }

        $this->assertSame([], $violations);
    }

    public function test_dtos_are_grouped_by_bounded_context(): void
    {
        $violations = [];

        foreach (glob(base_path('app/DTOs/*.php')) ?: [] as $path) {
            $violations[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
        }

        $this->assertSame([], $violations);
    }

    public function test_side_effect_classes_are_grouped_by_bounded_context(): void
    {
        $violations = [];

        foreach (['app/Events', 'app/Listeners', 'app/Jobs'] as $directory) {
            foreach (glob(base_path($directory.'/*.php')) ?: [] as $path) {
                $violations[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_repository_bindings_are_registered_by_module_providers(): void
    {
        $provider = file_get_contents(base_path('app/Providers/RepositoryServiceProvider.php'));
        $moduleProviders = $this->phpFiles(['app/Providers/Modules']);
        $bindings = [];

        $this->assertStringNotContainsString('->bind(', $provider);

        foreach ($moduleProviders as $file) {
            $contents = file_get_contents($file->getPathname());
            preg_match_all('/([A-Z][A-Za-z0-9]+RepositoryInterface)::class/', $contents, $matches);

            $bindings = array_merge($bindings, $matches[1]);
        }

        sort($bindings);

        $expected = collect(glob(base_path('app/Contracts/Repositories/*RepositoryInterface.php')) ?: [])
            ->map(fn (string $path) => basename($path, '.php'))
            ->sort()
            ->values()
            ->all();

        $this->assertSame($expected, $bindings);
    }

    public function test_controllers_inject_services_and_actions_through_constructors(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Http/Controllers']) as $file) {
            $contents = file_get_contents($file->getPathname());
            preg_match_all('/public function (?!__construct)\w+\s*\([^)]*(?:Service|Action)\s+\$/m', $contents, $matches);

            if (! empty($matches[0])) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_controllers_use_form_requests_instead_of_base_http_request(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Http/Controllers']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'use Illuminate\\Http\\Request;')) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_controllers_do_not_query_models_or_validate_inline(): void
    {
        $violations = [];
        $pattern = '/([A-Z][A-Za-z0-9_]*::(query|where|create|find|findOrFail|with|onlyTrashed|lockForUpdate|sum|count)\s*\(|->validate\s*\(|request\(\))/';

        foreach ($this->phpFiles(['app/Http/Controllers']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (preg_match($pattern, $contents)) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_views_and_routes_do_not_query_models_directly(): void
    {
        $violations = [];
        $patterns = [
            '/\\\\App\\\\Models\\\\[A-Za-z0-9_]+/',
            '/use App\\\\Models\\\\[A-Za-z0-9_]+;/',
        ];

        foreach ($this->phpFiles(['routes', 'resources/views']) as $file) {
            $contents = file_get_contents($file->getPathname());

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $contents)) {
                    $violations[] = $this->relativePath($file);
                    break;
                }
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_view_composers_delegate_to_services(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/View/Composers']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'use App\\Contracts\\Repositories\\')) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_services_and_actions_do_not_call_eloquent_static_queries(): void
    {
        $violations = [];
        $pattern = '/[A-Z][A-Za-z0-9_]*::(query|where|create|find|findOrFail|firstOrCreate|with|onlyTrashed|lockForUpdate|sum|count)\s*\(/';

        foreach ($this->phpFiles(['app/Services', 'app/Actions']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (preg_match($pattern, $contents)) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_services_and_actions_do_not_persist_models_directly(): void
    {
        $violations = [];
        $patterns = [
            '/\$[a-zA-Z_][A-Za-z0-9_]*->(save|delete|update|restore|forceDelete)\s*\(/',
            '/->find[A-Za-z0-9_]*\([^;]*\)->(save|delete|update|restore|forceDelete)\s*\(/s',
        ];

        foreach ($this->phpFiles(['app/Services', 'app/Actions']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (collect($patterns)->contains(fn (string $pattern) => preg_match($pattern, $contents))) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_services_and_actions_do_not_use_repository_query_builders_directly(): void
    {
        $violations = [];
        $pattern = '/->(query|trashedQuery|newestQuery)\s*\(/';

        foreach ($this->phpFiles(['app/Services', 'app/Actions']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (preg_match($pattern, $contents)) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_services_and_actions_do_not_use_db_table_directly(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Services', 'app/Actions']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'DB::table(')) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_services_and_actions_use_transaction_closures(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Services', 'app/Actions']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (
                str_contains($contents, 'DB::beginTransaction(')
                || str_contains($contents, 'DB::commit(')
                || str_contains($contents, 'DB::rollBack(')
            ) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_order_side_effects_are_handled_by_events_listeners_or_jobs(): void
    {
        $violations = [];
        $forbidden = [
            'OrderStatusLogRepositoryInterface',
            'statusLogs->create',
            'OrderNotificationService',
            'SendOrderPlacedMailJob',
            'StockMovementService',
            'stockMovements->record',
        ];

        foreach ($this->phpFiles(['app/Services', 'app/Actions']) as $file) {
            $contents = file_get_contents($file->getPathname());

            foreach ($forbidden as $needle) {
                if (str_contains($contents, $needle)) {
                    $violations[] = $this->relativePath($file).' uses '.$needle;
                }
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_external_callback_services_use_dtos_instead_of_raw_payload_arrays(): void
    {
        $violations = [];
        $checks = [
            'app/Services/Api/GhnWebhookService.php' => [
                'accept(array $payload)',
                'isValidSecret(array $candidates)',
            ],
            'app/Services/Checkout/VnpayCallbackService.php' => [
                'handleReturn(array $payload)',
                'handleIpn(array $payload)',
                'amountMismatchContext(Order $order, array $payload)',
            ],
        ];

        foreach ($checks as $path => $forbiddenSignatures) {
            $contents = file_get_contents(base_path($path));

            foreach ($forbiddenSignatures as $signature) {
                if (str_contains($contents, $signature)) {
                    $violations[] = $path.' uses '.$signature;
                }
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_admin_resource_controllers_call_authorization(): void
    {
        $violations = [];
        $exemptControllers = [
            'app/Http/Controllers/Admin/AuthController.php',
            'app/Http/Controllers/Admin/ProductAttributeController.php',
            'app/Http/Controllers/Admin/ProductAttributeValueController.php',
        ];

        foreach ($this->phpFiles(['app/Http/Controllers/Admin']) as $file) {
            $relativePath = $this->relativePath($file);

            if (in_array($relativePath, $exemptControllers, true)) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (! str_contains($contents, '$this->authorize(')) {
                $violations[] = $relativePath;
            }
        }

        $this->assertSame([], $violations);
    }

    /**
     * @return iterable<SplFileInfo>
     */
    protected function phpFiles(array $directories): iterable
    {
        foreach ($directories as $directory) {
            if (! is_dir(base_path($directory))) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(base_path($directory))
            );

            foreach ($iterator as $file) {
                if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                    yield $file;
                }
            }
        }
    }

    protected function relativePath(SplFileInfo $file): string
    {
        return str_replace(
            DIRECTORY_SEPARATOR,
            '/',
            str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname())
        );
    }
}

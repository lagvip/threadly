<?php

namespace Tests\Unit\Architecture;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class ArchitectureConventionsTest extends TestCase
{
    public function test_actions_layer_is_not_used(): void
    {
        $actionsPath = base_path('app/Actions');

        $this->assertFalse(is_dir($actionsPath), 'Use services instead of app/Actions.');
    }

    public function test_services_do_not_depend_on_http_requests(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Services']) as $file) {
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

    public function test_console_commands_delegate_business_queries_to_services(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Console/Commands']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (preg_match('/[A-Z][A-Za-z0-9_]*::(query|where|update|create|find)\s*\(/', $contents)) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_order_release_markers_are_mass_assignable_and_cast_to_datetime(): void
    {
        $order = new \App\Models\Order;

        foreach (['stock_deducted_at', 'stock_released_at', 'voucher_released_at'] as $attribute) {
            $this->assertContains($attribute, $order->getFillable());
            $this->assertSame('datetime', $order->getCasts()[$attribute] ?? null);
        }
    }

    public function test_admin_order_details_cannot_be_created_or_deleted_independently(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertStringNotContainsString("Route::resource('order-details'", $routes);
        $this->assertFileDoesNotExist(base_path('app/Services/Admin/OrderDetails/AdminOrderDetailService.php'));
        $this->assertFileDoesNotExist(base_path('app/Http/Requests/Admin/OrderDetails/StoreOrderDetailRequest.php'));
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

    public function test_controllers_inject_services_through_constructors(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Http/Controllers']) as $file) {
            $contents = file_get_contents($file->getPathname());
            preg_match_all('/public function (?!__construct)\w+\s*\([^)]*Service\s+\$/m', $contents, $matches);

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

    public function test_mutating_admin_routes_do_not_use_get(): void
    {
        $contents = file_get_contents(base_path('routes/web.php'));
        $violations = [];
        $pattern = '/Route::get\\([^\\n]*(restore|delete|force-delete|destroy)/';

        if (preg_match_all($pattern, $contents, $matches)) {
            $violations = $matches[0];
        }

        $this->assertSame([], $violations);
    }

    public function test_image_upload_requests_whitelist_safe_bitmap_mimes(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Http/Requests']) as $file) {
            foreach (file($file->getPathname()) ?: [] as $lineNumber => $line) {
                if (! str_contains($line, "'image'")) {
                    continue;
                }

                if (! str_contains($line, 'mimes:') || str_contains($line, 'svg')) {
                    $violations[] = $this->relativePath($file).':'.($lineNumber + 1);
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

    public function test_services_do_not_call_eloquent_static_queries(): void
    {
        $violations = [];
        $pattern = '/[A-Z][A-Za-z0-9_]*::(query|where|create|find|findOrFail|firstOrCreate|with|onlyTrashed|lockForUpdate|sum|count)\s*\(/';

        foreach ($this->phpFiles(['app/Services']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (preg_match($pattern, $contents)) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_services_do_not_persist_models_directly(): void
    {
        $violations = [];
        $patterns = [
            '/\$[a-zA-Z_][A-Za-z0-9_]*->(save|delete|update|restore|forceDelete)\s*\(/',
            '/->find[A-Za-z0-9_]*\([^;]*\)->(save|delete|update|restore|forceDelete)\s*\(/s',
        ];

        foreach ($this->phpFiles(['app/Services']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (collect($patterns)->contains(fn (string $pattern) => preg_match($pattern, $contents))) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_services_do_not_use_repository_query_builders_directly(): void
    {
        $violations = [];
        $pattern = '/->(query|trashedQuery|newestQuery)\s*\(/';

        foreach ($this->phpFiles(['app/Services']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (preg_match($pattern, $contents)) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_repository_interfaces_do_not_expose_eloquent_builders(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Contracts/Repositories']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (
                str_contains($contents, 'Illuminate\\Database\\Eloquent\\Builder')
                || preg_match('/:\s*Builder\b/', $contents)
            ) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_threadly_checkout_session_keys_are_configured(): void
    {
        $keys = [
            'threadly.checkout.cart_session_key',
            'threadly.checkout.buy_now_session_key',
            'threadly.checkout.voucher_session_key',
        ];

        foreach ($keys as $key) {
            $value = config($key);

            $this->assertIsString($value, $key.' must be configured as a string.');
            $this->assertNotSame('', trim($value), $key.' must not be empty.');
        }
    }

    public function test_services_do_not_use_db_table_directly(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Services']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'DB::table(')) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_services_use_transaction_closures(): void
    {
        $violations = [];

        foreach ($this->phpFiles(['app/Services']) as $file) {
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

        foreach ($this->phpFiles(['app/Services']) as $file) {
            $contents = file_get_contents($file->getPathname());

            foreach ($forbidden as $needle) {
                if (str_contains($contents, $needle)) {
                    $violations[] = $this->relativePath($file).' uses '.$needle;
                }
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_order_status_enum_is_the_single_source_of_truth(): void
    {
        $contents = file_get_contents(base_path('app/Models/Order.php'));

        $this->assertDoesNotMatchRegularExpression('/public const STATUS_[A-Z_]+\s*=/', $contents);
        $this->assertStringContainsString('use App\\Enums\\OrderStatus;', $contents);
    }

    public function test_order_payment_and_refund_enums_are_the_single_source_of_truth(): void
    {
        $orderModel = file_get_contents(base_path('app/Models/Order.php'));
        $violations = [];

        $this->assertDoesNotMatchRegularExpression('/public const (PAYMENT|REFUND)_[A-Z_]+\s*=/', $orderModel);
        $this->assertStringContainsString('use App\\Enums\\OrderPaymentStatus;', $orderModel);
        $this->assertStringContainsString('use App\\Enums\\OrderRefundStatus;', $orderModel);
        $this->assertStringContainsString('use App\\Enums\\PaymentMethod;', $orderModel);

        foreach ($this->phpFiles(['app', 'tests']) as $file) {
            $relativePath = $this->relativePath($file);

            if ($relativePath === 'tests/Unit/Architecture/ArchitectureConventionsTest.php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (preg_match('/Order::(PAYMENT|REFUND)_[A-Z_]+/', $contents)) {
                $violations[] = $relativePath;
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_domain_enums_are_the_single_source_of_truth_for_refund_inventory_wallet_and_user_statuses(): void
    {
        $modelChecks = [
            'app/Models/RefundRequest.php' => '/public const (STATUS|TYPE)_[A-Z_]+\s*=/',
            'app/Models/InventoryReceipt.php' => '/public const STATUS_[A-Z_]+\s*=/',
            'app/Models/StockMovement.php' => '/public const TYPE_[A-Z_]+\s*=/',
            'app/Models/WalletTransaction.php' => '/public const TYPE_[A-Z_]+\s*=/',
            'app/Models/User.php' => '/public const STATUS_[A-Z_]+\s*=/',
            'app/Models/OrderRefund.php' => '/public const (STATUS|TYPE)_[A-Z_]+\s*=/',
        ];

        foreach ($modelChecks as $path => $pattern) {
            $this->assertDoesNotMatchRegularExpression($pattern, file_get_contents(base_path($path)), $path);
        }

        $violations = [];

        foreach ($this->phpFiles(['app', 'tests']) as $file) {
            $relativePath = $this->relativePath($file);

            if ($relativePath === 'tests/Unit/Architecture/ArchitectureConventionsTest.php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (preg_match('/(RefundRequest::(STATUS|TYPE)_|InventoryReceipt::STATUS_|StockMovement::TYPE_|WalletTransaction::TYPE_|User::STATUS_|OrderRefund::(STATUS|TYPE)_)/', $contents)) {
                $violations[] = $relativePath;
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_ghn_status_vocabulary_is_centralized_in_enum(): void
    {
        $violations = [];
        $pattern = "/'(ready_to_pick|delivery_fail|waiting_to_return|return_transporting|return_sorting|return_fail|returned|damage|lost)'/";

        foreach ($this->phpFiles(['app', 'resources/views', 'tests']) as $file) {
            $relativePath = $this->relativePath($file);

            if (
                in_array($relativePath, [
                    'app/Enums/GhnOrderStatus.php',
                    'tests/Unit/Architecture/ArchitectureConventionsTest.php',
                ], true)
            ) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (preg_match($pattern, $contents)) {
                $violations[] = $relativePath;
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_voucher_type_and_status_vocabulary_is_centralized_in_enums(): void
    {
        $violations = [];
        $pattern = "/'(percent|fixed|expired)'|in:percent,fixed/";

        foreach ($this->phpFiles([
            'app/Console/Commands',
            'app/Http/Requests/Admin/Vouchers',
            'app/Models',
            'app/Repositories',
            'app/Services/Admin/Vouchers',
            'app/Services/Checkout',
            'resources/views/admin/vouchers',
            'resources/views/client/checkout',
            'tests/Unit/Admin',
        ]) as $file) {
            $relativePath = $this->relativePath($file);

            if (
                in_array($relativePath, [
                    'app/Enums/VoucherStatus.php',
                    'app/Enums/VoucherType.php',
                    'tests/Unit/Architecture/ArchitectureConventionsTest.php',
                ], true)
            ) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (preg_match($pattern, $contents)) {
                $violations[] = $relativePath;
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_product_status_vocabulary_is_centralized_in_enum_for_backend_code(): void
    {
        $violations = [];
        $pattern = "/'active'|'inactive'|in:active,inactive|status = 'active'/";

        foreach ($this->phpFiles([
            'app/Http/Requests/Admin/Products',
            'app/Models',
            'app/Repositories',
            'app/Services',
        ]) as $file) {
            $relativePath = $this->relativePath($file);

            if (
                in_array($relativePath, [
                    'app/Enums/ProductStatus.php',
                    'tests/Unit/Architecture/ArchitectureConventionsTest.php',
                ], true)
            ) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (preg_match($pattern, $contents)) {
                $violations[] = $relativePath;
            }
        }

        $this->assertSame([], $violations);
    }

    public function test_product_admin_views_receive_status_vocabulary_from_page_service(): void
    {
        $violations = [];
        $pattern = "/status\s*(?:={2,3})\s*'active'|old\('status',\s*'active'\)|value=\"(?:active|inactive)\"|textContent\s*=\s*'(?:Hoạt động|Không hoạt động)'/";

        foreach ($this->phpFiles(['resources/views/admin/product']) as $file) {
            $contents = file_get_contents($file->getPathname());

            if (preg_match($pattern, $contents)) {
                $violations[] = $this->relativePath($file);
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Children before parents. Foreign key checks are also disabled because
     * a few ad tables reference each other in both directions.
     *
     * @var list<string>
     */
    private array $tables = [
        'shipment_items',
        'shipments',
        'shipping_addresses',
        'redemption_transactions',
        'refunds',
        'payment_idempotency_keys',
        'payment_events',
        'payments',
        'order_status_histories',
        'order_items',
        'orders',
        'promotion_categories',
        'promotion_products',
        'promotion_usages',
        'promotions',
        'cart_items',
        'carts',
        'inventory_movements',
        'merchandising_collection_product',
        'merchandising_collections',
        'product_images',
        'product_variants',
        'products',
        'product_categories',
        'media',
        'ad_analytic_events',
        'ad_clicks',
        'ad_views',
        'ads',
        'ad_campaign_moderation_logs',
        'ad_campaigns',
        'ad_wallet_transactions',
        'ad_wallet_top_ups',
        'ad_wallets',
        'ad_profiles',
        'ad_settings',
        'ad_pricing_tiers',
        'ad_categories',
    ];

    /**
     * Permissions that only gated the removed features.
     *
     * @var list<string>
     */
    private array $permissions = [
        'manage ad categories',
        'manage ad campaigns',
        'manage ads',
        'manage promotions',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        $this->removePermissions();
    }

    /**
     * Irreversible on purpose: the data is gone. Restore from the backup.
     */
    public function down(): void
    {
        throw new RuntimeException('drop_ads_and_storefront_tables is irreversible. Restore from backup.');
    }

    private function removePermissions(): void
    {
        $tables = config('permission.table_names');

        if (! Schema::hasTable($tables['permissions'])) {
            return;
        }

        $ids = DB::table($tables['permissions'])->whereIn('name', $this->permissions)->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table($tables['role_has_permissions'])->whereIn('permission_id', $ids)->delete();
        DB::table($tables['model_has_permissions'])->whereIn('permission_id', $ids)->delete();
        DB::table($tables['permissions'])->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

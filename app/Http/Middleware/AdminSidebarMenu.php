<?php

namespace App\Http\Middleware;

use App\Utils\ModuleUtil;
use Closure;
use Menu; // Use the Facade alias
use Spatie\Menu\Html;
use Spatie\Menu\Link;
use Spatie\Menu\Menu as SpatieMenu; // Alias the class

class AdminSidebarMenu
{
    public function handle($request, Closure $next)
    {
        if ($request->ajax()) {
            return $next($request);
        }

        Menu::macro('admin_sidebar_menu', function () {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id'));

            // Create a menu with the classes and attributes required by AdminLTE
            $menu = SpatieMenu::new()
                ->addClass('sidebar-menu tree')
                ->setAttribute('data-widget', 'tree');

            // Helper function to create the final HTML string for a link
            $link_html = function ($icon, $text) {
                return Html::raw('<i class="' . $icon . '"></i> <span>' . $text . '</span>')->render();
            };

            // Helper function for the final HTML string for a submenu
            $submenu_html = function ($icon, $text) {
                return Html::raw('<i class="' . $icon . '"></i> <span>' . $text . '</span><i class="fa fa-angle-left pull-right"></i>')->render();
            };

            // Home
            $menu->add(Link::to(action([\App\Http\Controllers\HomeController::class, 'index']), $link_html('fa fas fa-tachometer-alt', __('home.home'))));

            // User Management
            if (auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view')) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-users', __('user.user_management'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->addIf(auth()->user()->can('user.view'), Link::to(action([\App\Http\Controllers\ManageUserController::class, 'index']), $link_html('fa fas fa-user', __('user.users'))))
                        ->addIf(auth()->user()->can('roles.view'), Link::to(action([\App\Http\Controllers\RoleController::class, 'index']), $link_html('fa fas fa-briefcase', __('user.roles'))))
                        ->addIf(auth()->user()->can('user.create'), Link::to(action([\App\Http\Controllers\SalesCommissionAgentController::class, 'index']), $link_html('fa fas fa-handshake', __('lang_v1.sales_commission_agents'))))
                )->addClass('treeview');
            }

            // Contacts
            if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view')) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-address-book', __('contact.contacts'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->addIf(auth()->user()->can('supplier.view'), Link::to(action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'supplier']), $link_html('fa fas fa-star', __('report.supplier'))))
                        ->addIf(auth()->user()->can('customer.view'), Link::to(action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']), $link_html('fa fas fa-star', __('report.customer'))))
                        ->addIf(auth()->user()->can('customer.view'), Link::to(action([\App\Http\Controllers\CustomerGroupController::class, 'index']), $link_html('fa fas fa-users', __('lang_v1.customer_groups'))))
                        ->addIf(auth()->user()->can('supplier.create') || auth()->user()->can('customer.create'), Link::to(action([\App\Http\Controllers\ContactController::class, 'getImportContacts']), $link_html('fa fas fa-download', __('lang_v1.import_contacts'))))
                        ->addIf(!empty(env('GOOGLE_MAP_API_KEY')), Link::to(action([\App\Http\Controllers\ContactController::class, 'contactMap']), $link_html('fa fas fa-map-marker-alt', __('lang_v1.map'))))
                )->addClass('treeview');
            }

            // Products
            if (auth()->user()->can('product.view') || auth()->user()->can('product.create')) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-cubes', __('sale.products'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->addIf(auth()->user()->can('product.view'), Link::to(action([\App\Http\Controllers\ProductController::class, 'index']), $link_html('fa fas fa-list', __('lang_v1.list_products'))))
                        ->addIf(auth()->user()->can('product.create'), Link::to(action([\App\Http\Controllers\ProductController::class, 'create']), $link_html('fa fas fa-plus-circle', __('product.add_product'))))
                        ->addIf(auth()->user()->can('product.view'), Link::to(action([\App\Http\Controllers\LabelsController::class, 'show']), $link_html('fa fas fa-barcode', __('barcode.print_labels'))))
                        ->addIf(auth()->user()->can('product.create'), Link::to(action([\App\Http\Controllers\VariationTemplateController::class, 'index']), $link_html('fa fas fa-circle', __('product.variations'))))
                        ->addIf(auth()->user()->can('product.create'), Link::to(action([\App\Http\Controllers\ImportProductsController::class, 'index']), $link_html('fa fas fa-download', __('product.import_products'))))
                        ->addIf(auth()->user()->can('product.opening_stock'), Link::to(action([\App\Http\Controllers\ImportOpeningStockController::class, 'index']), $link_html('fa fas fa-download', __('lang_v1.import_opening_stock'))))
                        ->addIf(auth()->user()->can('product.create'), Link::to(action([\App\Http\Controllers\SellingPriceGroupController::class, 'index']), $link_html('fa fas fa-circle', __('lang_v1.selling_price_group'))))
                        ->addIf(auth()->user()->can('unit.view') || auth()->user()->can('unit.create'), Link::to(action([\App\Http\Controllers\UnitController::class, 'index']), $link_html('fa fas fa-balance-scale', __('unit.units'))))
                        ->addIf(auth()->user()->can('category.view') || auth()->user()->can('category.create'), Link::to(action([\App\Http\Controllers\TaxonomyController::class, 'index']) . '?type=product', $link_html('fa fas fa-tags', __('category.categories'))))
                        ->addIf(auth()->user()->can('brand.view') || auth()->user()->can('brand.create'), Link::to(action([\App\Http\Controllers\BrandController::class, 'index']), $link_html('fa fas fa-gem', __('brand.brands'))))
                        ->add(Link::to(action([\App\Http\Controllers\WarrantyController::class, 'index']), $link_html('fa fas fa-shield-alt', __('lang_v1.warranties'))))
                )->addClass('treeview');
            }

            // Purchases
            if (in_array('purchases', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-arrow-circle-down', __('purchase.purchases'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->addIf(!empty($common_settings['enable_purchase_order']) && (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own')), Link::to(action([\App\Http\Controllers\PurchaseOrderController::class, 'index']), $link_html('fa fas fa-list', __('lang_v1.purchase_order'))))
                        ->addIf(auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase'), Link::to(action([\App\Http\Controllers\PurchaseController::class, 'index']), $link_html('fa fas fa-list', __('purchase.list_purchase'))))
                        ->addIf(auth()->user()->can('purchase.create'), Link::to(action([\App\Http\Controllers\PurchaseController::class, 'create']), $link_html('fa fas fa-plus-circle', __('purchase.add_purchase'))))
                        ->addIf(auth()->user()->can('purchase.update'), Link::to(action([\App\Http\Controllers\PurchaseReturnController::class, 'index']), $link_html('fa fas fa-undo', __('lang_v1.list_purchase_return'))))
                )->addClass('treeview');
            }

            // Sales
            if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'view_own_sell_only'])) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-arrow-circle-up', __('sale.sale'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->addIf(!empty($pos_settings['enable_sales_order']) && ($is_admin || auth()->user()->hasAnyPermission(['so.view_own', 'so.view_all', 'so.create'])), Link::to(action([\App\Http\Controllers\SalesOrderController::class, 'index']), $link_html('fa fas fa-plus-circle', __('lang_v1.sales_order'))))
                        ->addIf($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'direct_sell.access']), Link::to(action([\App\Http\Controllers\SellController::class, 'index']), $link_html('fa fas fa-list', __('lang_v1.all_sales'))))
                        ->addIf(in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access'), Link::to(action([\App\Http\Controllers\SellController::class, 'create']), $link_html('fa fas fa-plus-circle', __('sale.add_sale'))))
                        ->addIf(auth()->user()->can('sell.create') && in_array('pos_sale', $enabled_modules) && auth()->user()->can('sell.view'), Link::to(action([\App\Http\Controllers\SellPosController::class, 'index']), $link_html('fa fas fa-list', __('sale.list_pos'))))
                        ->addIf(auth()->user()->can('sell.create') && in_array('pos_sale', $enabled_modules), Link::to(action([\App\Http\Controllers\SellPosController::class, 'create']), $link_html('fa fas fa-plus-circle', __('sale.pos_sale'))))
                        ->addIf(in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access'), Link::to(action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'draft']), $link_html('fa fas fa-plus-circle', __('lang_v1.add_draft'))))
                        ->addIf(in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['draft.view_all', 'draft.view_own'])), Link::to(action([\App\Http\Controllers\SellController::class, 'getDrafts']), $link_html('fa fas fa-pen-square', __('lang_v1.list_drafts'))))
                        ->addIf(in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access'), Link::to(action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'quotation']), $link_html('fa fas fa-plus-circle', __('lang_v1.add_quotation'))))
                        ->addIf(in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['quotation.view_all', 'quotation.view_own'])), Link::to(action([\App\Http\Controllers\SellController::class, 'getQuotations']), $link_html('fa fas fa-pen-square', __('lang_v1.list_quotations'))))
                        ->addIf(auth()->user()->can('access_sell_return'), Link::to(action([\App\Http\Controllers\SellReturnController::class, 'index']), $link_html('fa fas fa-undo', __('lang_v1.list_sell_return'))))
                        ->addIf($is_admin || auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping']), Link::to(action([\App\Http\Controllers\SellController::class, 'shipments']), $link_html('fa fas fa-truck', __('lang_v1.shipments'))))
                        ->addIf(auth()->user()->can('discount.access'), Link::to(action([\App\Http\Controllers\DiscountController::class, 'index']), $link_html('fa fas fa-percent', __('lang_v1.discounts'))))
                        ->addIf(in_array('subscription', $enabled_modules) && auth()->user()->can('direct_sell.access'), Link::to(action([\App\Http\Controllers\SellPosController::class, 'listSubscriptions']), $link_html('fa fas fa-recycle', __('lang_v1.subscriptions'))))
                        ->addIf(auth()->user()->can('sell.create'), Link::to(action([\App\Http\Controllers\ImportSalesController::class, 'index']), $link_html('fa fas fa-file-import', __('lang_v1.import_sales'))))
                )->addClass('treeview');
            }

            // Stock Transfers
            if (in_array('stock_transfers', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-truck', __('lang_v1.stock_transfers'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->addIf(auth()->user()->can('purchase.view'), Link::to(action([\App\Http\Controllers\StockTransferController::class, 'index']), $link_html('fa fas fa-list', __('lang_v1.list_stock_transfers'))))
                        ->addIf(auth()->user()->can('purchase.create'), Link::to(action([\App\Http\Controllers\StockTransferController::class, 'create']), $link_html('fa fas fa-plus-circle', __('lang_v1.add_stock_transfer'))))
                )->addClass('treeview');
            }
            
            // Stock Adjustment
            if (in_array('stock_adjustment', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-database', __('stock_adjustment.stock_adjustment'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->addIf(auth()->user()->can('purchase.view'), Link::to(action([\App\Http\Controllers\StockAdjustmentController::class, 'index']), $link_html('fa fas fa-list', __('stock_adjustment.list'))))
                        ->addIf(auth()->user()->can('purchase.create'), Link::to(action([\App\Http\Controllers\StockAdjustmentController::class, 'create']), $link_html('fa fas fa-plus-circle', __('stock_adjustment.add'))))
                )->addClass('treeview');
            }

            // Expenses
            if (in_array('expenses', $enabled_modules) && (auth()->user()->can('all_expense.access') || auth()->user()->can('view_own_expense'))) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-minus-circle', __('expense.expenses'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->add(Link::to(action([\App\Http\Controllers\ExpenseController::class, 'index']), $link_html('fa fas fa-list', __('lang_v1.list_expenses'))))
                        ->addIf(auth()->user()->can('expense.add'), Link::to(action([\App\Http\Controllers\ExpenseController::class, 'create']), $link_html('fa fas fa-plus-circle', __('expense.add_expense'))))
                        ->addIf(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'), Link::to(action([\App\Http\Controllers\ExpenseCategoryController::class, 'index']), $link_html('fa fas fa-circle', __('expense.expense_categories'))))
                )->addClass('treeview');
            }

            // Accounts
            if (auth()->user()->can('account.access') && in_array('account', $enabled_modules)) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-money-check-alt', __('lang_v1.payment_accounts'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->add(Link::to(action([\App\Http\Controllers\AccountController::class, 'index']), $link_html('fa fas fa-list', __('account.list_accounts'))))
                        ->add(Link::to(action([\App\Http\Controllers\AccountReportsController::class, 'balanceSheet']), $link_html('fa fas fa-book', __('account.balance_sheet'))))
                        ->add(Link::to(action([\App\Http\Controllers\AccountReportsController::class, 'trialBalance']), $link_html('fa fas fa-balance-scale', __('account.trial_balance'))))
                        ->add(Link::to(action([\App\Http\Controllers\AccountController::class, 'cashFlow']), $link_html('fa fas fa-exchange-alt', __('lang_v1.cash_flow'))))
                        ->add(Link::to(action([\App\Http\Controllers\AccountReportsController::class, 'paymentAccountReport']), $link_html('fa fas fa-file-alt', __('account.payment_account_report'))))
                )->addClass('treeview');
            }

            // Reports
            if (auth()->user()->can('purchase_n_sell_report.view') || auth()->user()->can('contacts_report.view')) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-chart-bar', __('report.reports'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->addIf(auth()->user()->can('profit_loss_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getProfitLoss']), $link_html('fa fas fa-file-invoice-dollar', __('report.profit_loss'))))
                        ->addIf(config('constants.show_report_606'), Link::to(action([\App\Http\Controllers\ReportController::class, 'purchaseReport']), $link_html('fa fas fa-arrow-circle-down', 'Report 606 (' . __('lang_v1.purchase') . ')')))
                        ->addIf(config('constants.show_report_607'), Link::to(action([\App\Http\Controllers\ReportController::class, 'saleReport']), $link_html('fa fas fa-arrow-circle-up', 'Report 607 (' . __('business.sale') . ')')))
                        ->addIf(auth()->user()->can('purchase_n_sell_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getPurchaseSell']), $link_html('fa fas fa-exchange-alt', __('report.purchase_sell_report'))))
                        ->addIf(auth()->user()->can('tax_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getTaxReport']), $link_html('fa fas fa-percent', __('report.tax_report'))))
                        ->addIf(auth()->user()->can('contacts_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getCustomerSuppliers']), $link_html('fa fas fa-address-book', __('report.contacts'))))
                        ->addIf(auth()->user()->can('contacts_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getCustomerGroup']), $link_html('fa fas fa-users', __('lang_v1.customer_groups_report'))))
                        ->addIf(auth()->user()->can('stock_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getStockReport']), $link_html('fa fas fa-hourglass-half', __('report.stock_report'))))
                        ->addIf(auth()->user()->can('stock_report.view') && session('business.enable_product_expiry'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getStockExpiryReport']), $link_html('fa fas fa-calendar-times', __('report.stock_expiry_report'))))
                        ->addIf(auth()->user()->can('stock_report.view') && session('business.enable_lot_number'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getLotReport']), $link_html('fa fas fa-hourglass-half', __('lang_v1.lot_report'))))
                        ->addIf(auth()->user()->can('stock_report.view') && in_array('stock_adjustment', $enabled_modules), Link::to(action([\App\Http\Controllers\ReportController::class, 'getStockAdjustmentReport']), $link_html('fa fas fa-sliders-h', __('report.stock_adjustment_report'))))
                        ->addIf(auth()->user()->can('trending_product_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getTrendingProducts']), $link_html('fa fas fa-chart-line', __('report.trending_products'))))
                        ->addIf(auth()->user()->can('purchase_n_sell_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'itemsReport']), $link_html('fa fas fa-tasks', __('lang_v1.items_report'))))
                        ->addIf(auth()->user()->can('purchase_n_sell_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getproductPurchaseReport']), $link_html('fa fas fa-arrow-circle-down', __('lang_v1.product_purchase_report'))))
                        ->addIf(auth()->user()->can('purchase_n_sell_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getproductSellReport']), $link_html('fa fas fa-arrow-circle-up', __('lang_v1.product_sell_report'))))
                        ->addIf(auth()->user()->can('purchase_n_sell_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'purchasePaymentReport']), $link_html('fa fas fa-search-dollar', __('lang_v1.purchase_payment_report'))))
                        ->addIf(auth()->user()->can('purchase_n_sell_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'sellPaymentReport']), $link_html('fa fas fa-search-dollar', __('lang_v1.sell_payment_report'))))
                        ->addIf(in_array('expenses', $enabled_modules) && auth()->user()->can('expense_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getExpenseReport']), $link_html('fa fas fa-search-minus', __('report.expense_report'))))
                        ->addIf(auth()->user()->can('register_report.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getRegisterReport']), $link_html('fa fas fa-briefcase', __('report.register_report'))))
                        ->addIf(auth()->user()->can('sales_representative.view'), Link::to(action([\App\Http\Controllers\ReportController::class, 'getSalesRepresentativeReport']), $link_html('fa fas fa-user', __('report.sales_representative'))))
                        ->addIf(in_array('tables', $enabled_modules), Link::to(action([\App\Http\Controllers\ReportController::class, 'getTableReport']), $link_html('fa fas fa-table', __('restaurant.table_report'))))
                        ->addIf(in_array('service_staff', $enabled_modules), Link::to(action([\App\Http\Controllers\ReportController::class, 'getServiceStaffReport']), $link_html('fa fas fa-user-secret', __('restaurant.service_staff_report'))))
                        ->addIf($is_admin, Link::to(action([\App\Http\Controllers\ReportController::class, 'activityLog']), $link_html('fa fas fa-user-secret', __('lang_v1.activity_log'))))
                )->addClass('treeview');
            }

            // Backup
            if (auth()->user()->can('backup')) {
                $menu->add(Link::to(action([\App\Http\Controllers\BackUpController::class, 'index']), $link_html('fa fas fa-hdd', __('lang_v1.backup'))));
            }

            // Modules
            if (auth()->user()->can('manage_modules')) {
                $menu->add(Link::to(action([\App\Http\Controllers\Install\ModulesController::class, 'index']), $link_html('fa fas fa-plug', __('lang_v1.modules'))));
            }

            // Bookings
            if (in_array('booking', $enabled_modules) && (auth()->user()->can('crud_all_bookings') || auth()->user()->can('crud_own_bookings'))) {
                $menu->add(Link::to(action([\App\Http\Controllers\Restaurant\BookingController::class, 'index']), $link_html('fas fa fa-calendar-check', __('restaurant.bookings'))));
            }

            // Kitchen
            if (in_array('kitchen', $enabled_modules)) {
                $menu->add(Link::to(action([\App\Http\Controllers\Restaurant\KitchenController::class, 'index']), $link_html('fa fas fa-fire', __('restaurant.kitchen'))));
            }
            
            // Orders
            if (in_array('service_staff', $enabled_modules)) {
                $menu->add(Link::to(action([\App\Http\Controllers\Restaurant\OrderController::class, 'index']), $link_html('fa fas fa-list-alt', __('restaurant.orders'))));
            }

            // Notification Templates
            if (auth()->user()->can('send_notifications')) {
                $menu->add(Link::to(action([\App\Http\Controllers\NotificationTemplateController::class, 'index']), $link_html('fa fas fa-envelope', __('lang_v1.notification_templates'))));
            }

            // Settings
            if (auth()->user()->can('business_settings.access') || auth()->user()->can('barcode_settings.access')) {
                $menu->submenu(
                    Link::to('#', $submenu_html('fa fas fa-cog', __('business.settings'))),
                    SpatieMenu::new()->addClass('treeview-menu')
                        ->addIf(auth()->user()->can('business_settings.access'), Link::to(action([\App\Http\Controllers\BusinessController::class, 'getBusinessSettings']), $link_html('fa fas fa-cogs', __('business.business_settings'))))
                        ->addIf(auth()->user()->can('business_settings.access'), Link::to(action([\App\Http\Controllers\BusinessLocationController::class, 'index']), $link_html('fa fas fa-map-marker', __('business.business_locations'))))
                        ->addIf(auth()->user()->can('invoice_settings.access'), Link::to(action([\App\Http\Controllers\InvoiceSchemeController::class, 'index']), $link_html('fa fas fa-file', __('invoice.invoice_settings'))))
                        ->addIf(auth()->user()->can('barcode_settings.access'), Link::to(action([\App\Http\Controllers\BarcodeController::class, 'index']), $link_html('fa fas fa-barcode', __('barcode.barcode_settings'))))
                        ->addIf(auth()->user()->can('access_printers'), Link::to(action([\App\Http\Controllers\PrinterController::class, 'index']), $link_html('fa fas fa-share-alt', __('printer.receipt_printers'))))
                        ->addIf(auth()->user()->can('tax_rate.view') || auth()->user()->can('tax_rate.create'), Link::to(action([\App\Http\Controllers\TaxRateController::class, 'index']), $link_html('fa fas fa-bolt', __('tax_rate.tax_rates'))))
                        ->addIf(in_array('tables', $enabled_modules) && auth()->user()->can('access_tables'), Link::to(action([\App\Http\Controllers\Restaurant\TableController::class, 'index']), $link_html('fa fas fa-table', __('restaurant.tables'))))
                        ->addIf(in_array('modifiers', $enabled_modules) && (auth()->user()->can('product.view') || auth()->user()->can('product.create')), Link::to(action([\App\Http\Controllers\Restaurant\ModifierSetsController::class, 'index']), $link_html('fa fas fa-pizza-slice', __('restaurant.modifiers'))))
                        ->addIf(in_array('types_of_service', $enabled_modules) && auth()->user()->can('access_types_of_service'), Link::to(action([\App\Http\Controllers\TypesOfServiceController::class, 'index']), $link_html('fa fas fa-user-circle', __('lang_v1.types_of_service'))))
                )->addClass('treeview');
            }

            return $menu;
        });

        // Add menus from modules
        $moduleUtil = new ModuleUtil;
        $moduleUtil->getModuleData('modifyAdminMenu');

        return $next($request);
    }
}
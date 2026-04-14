<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\CommerceWebController;
use App\Http\Controllers\Web\CommunicationsWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FinanceCashMovementController;
use App\Http\Controllers\Web\FinanceCategoryController;
use App\Http\Controllers\Web\FinanceController;
use App\Http\Controllers\Web\FinanceFixedAssetController;
use App\Http\Controllers\Web\InventoryFormTemplateController;
use App\Http\Controllers\Web\InventoryInboundReceiptController;
use App\Http\Controllers\Web\InventoryWebController;
use App\Http\Controllers\Web\InvoicePdfController;
use App\Http\Controllers\Web\MeasurementFormTemplateController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\OrderPdfController;
use App\Http\Controllers\Web\OrderWhatsAppController;
use App\Http\Controllers\Web\ReportingWebController;
use App\Http\Controllers\Web\StaffWebController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\TailorClientMeasurementController;
use App\Http\Controllers\Web\TailorOrderController;
use App\Http\Controllers\Web\TailorWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/espace-tailleur', TailorWorkspaceController::class)->middleware(['auth', 'verified'])->name('tailor.workspace');

Route::get('/share/orders/{order}/facture-pdf', [OrderPdfController::class, 'showShared'])
    ->name('orders.pdf.shared')
    ->middleware(['signed']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/locale', LocaleController::class)->name('locale.switch');

    Route::post('/espace-tailleur/commandes/{order}/valider', [TailorOrderController::class, 'validateOrder'])
        ->name('tailor.orders.validate');

    Route::get('/espace-tailleur/nouveau-client-mensurations', [TailorClientMeasurementController::class, 'create'])
        ->name('tailor.clients.measurements.create');
    Route::post('/espace-tailleur/nouveau-client-mensurations', [TailorClientMeasurementController::class, 'store'])
        ->name('tailor.clients.measurements.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', ClientController::class)->except(['destroy']);
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])
        ->name('clients.destroy')
        ->middleware('prevent.tailleur');

    Route::post('clients/{client}/measurements', [ClientController::class, 'storeMeasurement'])->name('clients.measurements.store');
    Route::get('clients/{client}/measurements/{measurement}/edit', [ClientController::class, 'editMeasurement'])->name('clients.measurements.edit');
    Route::patch('clients/{client}/measurements/{measurement}', [ClientController::class, 'updateMeasurement'])->name('clients.measurements.update');
    Route::delete('clients/{client}/measurements/{measurement}', [ClientController::class, 'destroyMeasurement'])->name('clients.measurements.destroy');

    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/pdf', [OrderPdfController::class, 'show'])->name('orders.pdf');

    Route::middleware('prevent.tailleur')->group(function () {
        Route::resource('measurement-templates', MeasurementFormTemplateController::class)->except(['show']);

        Route::post('orders/{order}/whatsapp/invoice', [OrderWhatsAppController::class, 'sendInvoice'])->name('orders.whatsapp.invoice');
        Route::post('orders/{order}/whatsapp/receipt', [OrderWhatsAppController::class, 'sendReceipt'])->name('orders.whatsapp.receipt');
        Route::post('orders/{order}/deduct-inventory', [OrderController::class, 'deductInventory'])->name('orders.deduct-inventory');
        Route::resource('orders', OrderController::class)->except(['show']);

        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance/parametres', [FinanceController::class, 'updateSettings'])->name('finance.settings.update');
        Route::resource('finance/categories', FinanceCategoryController::class)->except(['show'])->names('finance.categories');
        Route::resource('finance/cash-movements', FinanceCashMovementController::class)->except(['show'])->names('finance.cash-movements')->parameters(['cash-movements' => 'cash_movement']);
        Route::resource('finance/fixed-assets', FinanceFixedAssetController::class)->except(['show'])->names('finance.fixed-assets')->parameters(['fixed-assets' => 'fixed_asset']);

        Route::get('/staff', [StaffWebController::class, 'index'])->name('staff.index');
        Route::patch('/staff/employes/{employee}/salaire', [StaffWebController::class, 'updateSalary'])->name('staff.employees.salary');
        Route::resource('inventory-form-templates', InventoryFormTemplateController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class);
        Route::get('inventory/reception', [InventoryInboundReceiptController::class, 'create'])->name('inventory.reception.create');
        Route::post('inventory/reception', [InventoryInboundReceiptController::class, 'store'])->name('inventory.reception.store');
        Route::get('inventory/{item}/parametrer', [InventoryWebController::class, 'parameterizeForm'])->name('inventory.parameterize');
        Route::patch('inventory/{item}/parametrer', [InventoryWebController::class, 'parameterizeUpdate'])->name('inventory.parameterize.update');
        Route::get('inventory/{item}/actualiser', [InventoryWebController::class, 'refreshForm'])->name('inventory.refresh');
        Route::patch('inventory/{item}/actualiser', [InventoryWebController::class, 'refreshUpdate'])->name('inventory.refresh.update');
        Route::resource('inventory', InventoryWebController::class)
            ->parameters(['inventory' => 'item'])
            ->except(['show', 'edit', 'update']);
        Route::get('/commerce', [CommerceWebController::class, 'index'])->name('commerce.index');
        Route::get('/reporting', [ReportingWebController::class, 'index'])->name('reporting.index');
        Route::get('/communications', [CommunicationsWebController::class, 'index'])->name('communications.index');

        Route::get('/invoices/{invoice}/pdf', [InvoicePdfController::class, 'show'])->name('invoices.pdf');
    });
});

require __DIR__.'/auth.php';

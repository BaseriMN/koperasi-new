<?php

use App\Http\Controllers\AuditReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ModuleAccessController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountCategoryController;
use App\Http\Controllers\AccountEntryController;
use App\Http\Controllers\AccountReportController;
use Illuminate\Support\Facades\Route;



/*
| Web Routes — Sistem Pengurusan Koperasi
|--------------------------------------------------------------------------
| Akses modul dikawal oleh middleware 'module:<key>' yang membaca matrix
| dalam DB (jadual module_role). Super-user sentiasa dibenarkan.
*/

Route::get('/', fn () => redirect()->route('dashboard'));

/*
| Tetamu
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
});

/*
| Pengguna log masuk
*/
Route::middleware('auth')->group(function () {

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pengurusan Staff
    Route::middleware('module:pengurusan_staff')->group(function () {
    Route::resource('users', UserController::class);
    });

    // Pengurusan Ahli (Keahlian & Waris)
    Route::middleware('module:pengurusan_member')->group(function () {
        // Keahlian (AXXXX) + waris
        Route::resource('members', MemberController::class);

        // Lejar transaksi saham & simpanan
        Route::get('transaksi', [TransactionController::class, 'index'])->name('transaksi.index');
        Route::get('transaksi/create', [TransactionController::class, 'create'])->name('transaksi.create');
        Route::post('transaksi', [TransactionController::class, 'store'])->name('transaksi.store');

        // Pindah milik saham
        Route::get('pindah-saham', [TransactionController::class, 'shareTransferForm'])->name('saham.pindah.form');
        Route::post('pindah-saham', [TransactionController::class, 'shareTransfer'])->name('saham.pindah');

        // Pindah milik keahlian (nombor ahli kekal)
        Route::get('members/{member}/pindah-milik', [TransactionController::class, 'ownershipTransferForm'])->name('member.pindah.form');
        Route::post('members/{member}/pindah-milik', [TransactionController::class, 'ownershipTransfer'])->name('member.pindah');
    });

    // Tetapan Sistem (Peranan, Kebenaran, Akses Modul)
    Route::middleware('module:tetapan_sistem')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::resource('permissions', PermissionController::class)->except(['show']);

        // Matrix akses modul
        Route::get('tetapan/modul', [ModuleAccessController::class, 'index'])->name('tetapan.modul');
        Route::put('tetapan/modul', [ModuleAccessController::class, 'update'])->name('tetapan.modul.update');
    });

    // Permohonan Pinjaman
    Route::middleware('module:permohonan_pinjaman')->group(function () {
        Route::get('pinjaman', [LoanApplicationController::class, 'index'])->name('pinjaman.index');
        Route::get('pinjaman/create', [LoanApplicationController::class, 'create'])->name('pinjaman.create');
        Route::post('pinjaman', [LoanApplicationController::class, 'store'])->name('pinjaman.store');
        Route::post('pinjaman/{loan}/decide', [LoanApplicationController::class, 'decide'])->name('pinjaman.decide');
    });

    // Simpanan & Saham (lejar transaksi)
    Route::middleware('module:simpanan_saham')->group(function () {
        Route::get('simpanan', [TransactionController::class, 'index'])->name('simpanan.index');
        Route::get('simpanan/create', [TransactionController::class, 'create'])->name('simpanan.create');
        Route::post('simpanan', [TransactionController::class, 'store'])->name('simpanan.store');
    });

    // Mesyuarat & Minit
    Route::middleware('module:mesyuarat_minit')->group(function () {
        Route::get('mesyuarat', [MeetingController::class, 'index'])->name('mesyuarat.index');
        Route::get('mesyuarat/create', [MeetingController::class, 'create'])->name('mesyuarat.create');
        Route::post('mesyuarat', [MeetingController::class, 'store'])->name('mesyuarat.store');
    });

    // Akaun — Penyata Untung Rugi (TANPA parameter jenis — liputi kedua-dua)
    Route::middleware('module:akaun')->group(function () {
        Route::get('akaun/penyata', [AccountReportController::class, 'untungRugi'])->name('akaun.penyata');
    });
 
    // Akaun — Pendapatan & Perbelanjaan ({jenis} = pendapatan | perbelanjaan)
    // whereIn mengunci {jenis} kepada dua nilai sah sahaja di peringkat route.
    Route::middleware('module:akaun')
        ->prefix('akaun/{jenis}')
        ->whereIn('jenis', ['pendapatan', 'perbelanjaan'])
        ->group(function () {
            // Entri pendapatan/perbelanjaan
            Route::get('/', [AccountEntryController::class, 'index'])->name('akaun.entri.index');
            Route::get('entri/create', [AccountEntryController::class, 'create'])->name('akaun.entri.create');
            Route::post('entri', [AccountEntryController::class, 'store'])->name('akaun.entri.store');
            Route::get('entri/{entri}/edit', [AccountEntryController::class, 'edit'])->name('akaun.entri.edit');
            Route::put('entri/{entri}', [AccountEntryController::class, 'update'])->name('akaun.entri.update');
            Route::delete('entri/{entri}', [AccountEntryController::class, 'destroy'])->name('akaun.entri.destroy');
 
            // Pengurusan kategori dinamik
            Route::get('kategori', [AccountCategoryController::class, 'index'])->name('akaun.kategori.index');
            Route::get('kategori/create', [AccountCategoryController::class, 'create'])->name('akaun.kategori.create');
            Route::post('kategori', [AccountCategoryController::class, 'store'])->name('akaun.kategori.store');
            Route::get('kategori/{kategori}/edit', [AccountCategoryController::class, 'edit'])->name('akaun.kategori.edit');
            Route::put('kategori/{kategori}', [AccountCategoryController::class, 'update'])->name('akaun.kategori.update');
            Route::delete('kategori/{kategori}', [AccountCategoryController::class, 'destroy'])->name('akaun.kategori.destroy');
    });
    

    
    // Laporan Audit
    Route::middleware('module:laporan_audit')->group(function () {
        Route::get('audit', [AuditReportController::class, 'index'])->name('audit.index');
    });
});
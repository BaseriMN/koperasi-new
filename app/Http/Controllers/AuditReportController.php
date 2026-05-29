<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AuditReportController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->hasAnyRole(['auditor', 'super-user'])) {
            abort(403);
        }

        $simpananMasuk = (float) Transaction::where('jenis', 'simpanan')->where('arah', 'masuk')->sum('amaun');
        $simpananKeluar = (float) Transaction::where('jenis', 'simpanan')->where('arah', 'keluar')->sum('amaun');
        $sahamMasuk = (float) Transaction::where('jenis', 'saham')->where('arah', 'masuk')->sum('amaun');
        $sahamKeluar = (float) Transaction::where('jenis', 'saham')->where('arah', 'keluar')->sum('amaun');

        $stats = [
            'simpanan'        => $simpananMasuk - $simpananKeluar,
            'saham'           => $sahamMasuk - $sahamKeluar,
            'pinjaman_lulus'  => (float) Loan::where('status', 'approved')->sum('amount'),
            'rekod_transaksi' => Transaction::count(),
            'pinjaman_pending'=> Loan::where('status', 'pending')->count(),
        ];

        // Lejar terkini untuk jadual audit
        $records = Transaction::with('member')->latest()->limit(20)->get();

        return view('audit.index', compact('stats', 'records'));
    }
}

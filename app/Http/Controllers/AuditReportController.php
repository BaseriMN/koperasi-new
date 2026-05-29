<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Saving;
use Illuminate\Http\Request;

class AuditReportController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->hasAnyRole(['auditor', 'super-user'])) {
            abort(403);
        }

        $stats = [
            'simpanan'        => Saving::sum('amaun'),
            'pinjaman_lulus'  => Loan::where('status', 'approved')->sum('amount'),
            'rekod_simpanan'  => Saving::count(),
            'rekod_pinjaman'  => Loan::count(),
            'pinjaman_pending'=> Loan::where('status', 'pending')->count(),
        ];

        // Gabungan rekod untuk jadual audit (simpanan + pinjaman diluluskan)
        $records = collect();

        Saving::with('user')->latest()->limit(10)->get()->each(function ($s) use ($records) {
            $records->push([
                'ref'      => 'SAV-' . str_pad($s->id, 4, '0', STR_PAD_LEFT),
                'kategori' => ucfirst($s->jenis),
                'amaun'    => $s->amaun,
                'status'   => 'sah',
                'tarikh'   => $s->created_at,
            ]);
        });

        Loan::with('user')->where('status', 'approved')->latest()->limit(10)->get()->each(function ($l) use ($records) {
            $records->push([
                'ref'      => 'LON-' . str_pad($l->id, 4, '0', STR_PAD_LEFT),
                'kategori' => 'Pinjaman',
                'amaun'    => $l->amount,
                'status'   => 'sah',
                'tarikh'   => $l->reviewed_at ?? $l->created_at,
            ]);
        });

        $records = $records->sortByDesc('tarikh')->take(15)->values();

        return view('audit.index', compact('stats', 'records'));
    }
}

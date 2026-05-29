<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;

class LoanApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Admin & super-user nampak semua; ahli nampak permohonan sendiri sahaja
        $query = Loan::with(['user', 'reviewer'])->latest();

        // Untuk kes ni, hanya 2 pihak boleh approve
        if (! $user->hasAnyRole(['admin-koperasi', 'super-user'])) {
            $query->where('user_id', $user->id);
        }

        /*
        // Kalau orang yang approve loan tu tak boleh orang yang sama
        if ($user->hasAnyRole(['admin-koperasi', 'super-user'])) {
            $query->where('user_id', '!=', $user->id);
        }
        */

        $loans = $query->paginate(15)->withQueryString();

        $stats = [
            'pending'   => Loan::where('status', 'pending')->count(),
            'approved'  => Loan::where('status', 'approved')->whereMonth('reviewed_at', now()->month)->count(),
            'requested' => Loan::where('status', 'pending')->sum('amount'),
        ];

        $canApprove = $user->hasAnyRole(['admin-koperasi', 'super-user']);

        return view('pinjaman.index', compact('loans', 'stats', 'canApprove'));
    }

    public function create(Request $request)
    {
        if (! $request->user()->hasAnyRole(['ahli', 'super-user'])) {
            abort(403, 'Tidak dibenarkan memohon.');
        }

        return view('pinjaman.create');
    }

    public function store(Request $request)
    {
        if (! $request->user()->hasAnyRole(['ahli', 'super-user'])) {
            abort(403, 'Tidak dibenarkan memohon.');
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'tempoh' => ['required', 'integer', 'min:1', 'max:120'],
            'tujuan' => ['required', 'string', 'max:500'],
        ]);

        Loan::create([
            'user_id' => $request->user()->id,
            'amount'  => $data['amount'],
            'tempoh'  => $data['tempoh'],
            'tujuan'  => $data['tujuan'],
            'status'  => 'pending',
        ]);

        return redirect()->route('pinjaman.index')
            ->with('success', 'Permohonan pinjaman berjaya dihantar.');
    }

    public function decide(Request $request, Loan $loan)
    {
        if (! $request->user()->hasAnyRole(['pengurus', 'super-user'])) {
            abort(403, 'Tidak dibenarkan meluluskan.');
        }

        $data = $request->validate([
            'status'  => ['required', 'in:approved,rejected'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $loan->update([
            'status'      => $data['status'],
            'catatan'     => $data['catatan'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $label = $data['status'] === 'approved' ? 'diluluskan' : 'ditolak';

        return redirect()->route('pinjaman.index')
            ->with('success', "Permohonan #{$loan->id} telah {$label}.");
    }
}

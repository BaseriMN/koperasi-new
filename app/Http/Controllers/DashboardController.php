<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Saving;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $roles = $user->roles->pluck('slug')->toArray();

        // Modul dibenarkan dibaca dari matrix DB (super-user = semua)
        $allowedModules = ModuleAccess::allowedFor($user);

        $stats = [
            'ahli'             => User::where('is_active', true)->count(),
            'simpanan'         => Saving::sum('amaun'),
            'pinjaman_pending' => Loan::where('status', 'pending')->count(),
            'pinjaman_total'   => Loan::sum('amount'),
        ];

        return view('dashboard', [
            'user'           => $user,
            'roles'          => $roles,
            'allowedModules' => $allowedModules,
            'stats'          => $stats,
        ]);
    }
}

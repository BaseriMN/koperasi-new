<?php

namespace App\Http\Middleware;

use App\Support\ModuleAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Kawal akses berdasarkan matrix modul dalam DB.
     * Penggunaan: ->middleware('module:pengurusan_ahli')
     * Super-user sentiasa dibenarkan.
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! ModuleAccess::userCan($user, $moduleKey)) {
            $label = config("modules.modules.{$moduleKey}.label", 'modul ini');
            abort(403, "Peranan anda tidak mempunyai akses ke {$label}.");
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Ensurerole 
{
    public function handle(Request $request, Closure $next, string ...$roles): Response 
    {
        $user = $request->user();

        if($user === null) {
            return redirect()->route('auth')->with('warning', 'Silahkan masuk terlebih dahulu untuk melanjutkan.');
        }

        if (! in_array($user->role->value, $roles, true)) {
            abort(403, 'Peran Anda ('.$user->role->label().') tidak memiliki akses ke halaman ini.');

        }
        return $next($request);
    }
}
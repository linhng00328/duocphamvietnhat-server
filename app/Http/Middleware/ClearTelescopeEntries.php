<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClearTelescopeEntries
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(env('TELESCOPE_ENABLED', true) == true ) {
           // DB::delete("DELETE FROM telescope_entries WHERE id NOT IN (SELECT id FROM (SELECT id FROM telescope_entries ORDER BY created_at DESC LIMIT 100) t)");
        }
    
        return $next($request);
    }
}

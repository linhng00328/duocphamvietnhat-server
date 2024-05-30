<?php

namespace App\Http\Middleware;

use Closure;

class IsStaffSale
{
    public function handle($request, Closure $next)
    {
        $request->merge([
            'is_sale_staff' =>  true,
        ]);
        return $next($request);
    }
}

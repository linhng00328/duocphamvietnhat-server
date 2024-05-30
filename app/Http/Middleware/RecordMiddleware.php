<?php

namespace App\Http\Middleware;

use App\Jobs\RecordAccessHistoryJob;
use Closure;

class RecordMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // RecordAccessHistoryJob::dispatch($request);

        return $next($request);
    }
}

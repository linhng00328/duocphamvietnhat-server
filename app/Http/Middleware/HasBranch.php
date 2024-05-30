<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\MsgCode;
use Closure;


class HasBranch
{
    public function handle($request, Closure $next)
    {
        $branch_id = $request->route()->parameter('branch_id');

        if ($request->store != null) {

            $branchExists = Branch::where('store_id', $request->store->id)->where('id', $branch_id)->first();

            if ($branchExists == null) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'msg_code' => MsgCode::NO_BRANCH_EXISTS[0],
                    'msg' => MsgCode::NO_BRANCH_EXISTS[1],
                ], 404);
            }

            $request->merge([
                'branch' =>  $branchExists,
            ]);
        }

        return $next($request);
    }
}

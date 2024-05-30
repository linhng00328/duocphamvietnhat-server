<?php

namespace App\Http\Controllers\Api\User;

use App\Exports\VoucherCodesExport;
use App\Http\Controllers\Controller;
use App\Models\MsgCode;
use App\Models\Voucher;
use App\Models\VoucherCode;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VoucherCodeController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->route()->parameter('voucher_id');
        $voucher = Voucher::where('id', $id)
            ->where('store_id', $request->store->id)
            ->first();

        if ($voucher == null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_VOUCHER_EXISTS[0],
                'msg' => MsgCode::NO_VOUCHER_EXISTS[1],
            ], 400);
        }

        if ($voucher->is_use_once_code_multiple_time) {
            return response()->json([
                'code' => 200,
                'success' => true,
                'msg_code' => MsgCode::SUCCESS[0],
                'msg' => MsgCode::SUCCESS[1],
                'data' => null,
            ], 200);
        }
        $voucherCode = VoucherCode::with('customer')
            ->where('store_id', $request->store->id)
            ->where('voucher_id', $voucher->id)
            ->when($request->search, function ($query) use ($request) {
                $query->search($request->search);
            })
            ->when($request->status !== null && $request->status !== "", function ($query) use ($request) {
                $query->where('status', (int)$request->status);
            })
            ->paginate($request->limit ?: 20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $voucherCode,
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        $id = $request->route()->parameter('voucher_id');
        $voucher = Voucher::where('id', $id)
            ->where('store_id', $request->store->id)
            ->exists();

        if (!$voucher) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_VOUCHER_EXISTS[0],
                'msg' => MsgCode::NO_VOUCHER_EXISTS[1],
            ], 400);
        }

        $voucher_code_ids = $request->voucher_code_ids;

        if (!is_array($voucher_code_ids) || count($voucher_code_ids) === 0) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NUMBER_VOUCHER_CODE_INVALID[0],
                'msg' => MsgCode::NUMBER_VOUCHER_CODE_INVALID[1],
            ], 400);
        }

        VoucherCode::where('store_id', $request->store->id)
            ->where('voucher_id', $id)
            ->whereIn('id', $voucher_code_ids)
            ->update([
                'status' => 2
            ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    public function link_export(Request $request)
    {
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $base64Date = base64_encode($carbon->addMinutes(3)->format('Y-m-d H:i:s'));
        $link = url()->current();
        $md5_link = md5($link);
        $link .= '?en=' . $md5_link . '&ex=' . $base64Date;
        $link = str_replace('link_export', 'export', $link);

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $link
        ], 200);
    }

    public function export(Request $request)
    {
        try {
            $voucherCodesExport = new VoucherCodesExport;
            return Excel::download($voucherCodesExport, 'danh_sach_ma_vocher.xlsx');
        } catch (Exception $e) {
            echo ($e->getMessage());
        }
    }
}

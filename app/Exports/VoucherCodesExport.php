<?php

namespace App\Exports;

use App\Models\Store;
use App\Models\VoucherCode;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VoucherCodesExport implements FromView, WithStyles
{
    public function view(): View
    {
        $store_code = request('store_code');
        $voucher_id = request('voucher_id');

        $md5_link = request('en');
        $expired_params = request('ex');

        $link_export = url()->current();
        $link_export = str_replace('export', 'link_export', $link_export);
        $md5_encode = md5($link_export);
        $decodedExpired = base64_decode($expired_params);

        if ($md5_encode === $md5_link) {
            $carbon = Carbon::now('Asia/Ho_Chi_Minh');
            $date_expired = $carbon->parse($decodedExpired);
            $date_now = $carbon;

            if ($date_expired->gt($date_now)) {


                $store = Store::where('store_code', $store_code)
                    ->first();

                $voucher_codes = VoucherCode::with('voucher', 'customer')
                    ->where('store_id', $store ? $store->id : null)
                    ->where('voucher_id', $voucher_id)
                    ->get();


                return view('exports.voucher_codes', [
                    'voucher_codes' => $voucher_codes
                ]);
            } else {
                throw new Exception("Expired link.");
            }
        } else {
            throw new Exception("Expired link.");
        }
    }
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'f4d03f', // Set your desired background color here
                    ],
                ],
                'font' => [
                    'bold' => true, // Make the font bold
                    'color' => [
                        'rgb' => '000000', // Set your desired font color here
                    ],
                ],
            ],
        ];
    }
}

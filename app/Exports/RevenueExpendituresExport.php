<?php

namespace App\Exports;

use App\Helper\RevenueExpenditureUtils;
use App\Models\Customer;
use App\Models\RevenueExpenditure;
use App\Models\Staff;
use App\Models\Store;
use App\Models\Supplier;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueExpendituresExport implements FromView, WithStyles
{
    public function view(): View
    {
        $page = request('page') ?: 1;
        $is_revenue = request('is_revenue');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $search = request('search');
        $store_code = request('store_code');
        $branch_id = request('branch_id');
        $limit = request('limit') ?: 20;
        $md5_params = request('en');
        $expired_params = request('ex');

        $link_export = "page=" . $page . '&search=' . $search . '&limit=' . $limit . '&is_revenue=' . $is_revenue . '&date_from=' . $dateFrom . '&date_to=' . $dateTo;
        $md5_encode = md5($link_export);
        $decodedExpired = base64_decode($expired_params);

        if ($md5_encode === $md5_params) {
            $carbon = Carbon::now('Asia/Ho_Chi_Minh');
            $date_expired = $carbon->parse($decodedExpired);
            $date_now = $carbon;

            if ($date_expired->gt($date_now)) {
                //Config
                $date1 = $carbon->parse($dateFrom);
                $date2 = $carbon->parse($dateTo);

                $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
                $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';

                $branch_ids = request("branch_ids") == null ? [] : explode(',', request("branch_ids"));

                $branch_ids_input = array();
                if (count($branch_ids) > 0) {
                    $branch_ids_input =  $branch_ids;
                } else if ($branch_id != null) {
                    $branch_ids_input = [$branch_id];
                }

                $store = Store::where('store_code', $store_code)
                    ->first();

                $revenue_expenditures = RevenueExpenditure::where('store_id', $store ? $store->id : null)
                    ->whereIn('branch_id', $branch_ids_input)
                    ->where('created_at', '>=',  $dateFrom)
                    ->where('created_at', '<', $dateTo)
                    ->orderBy('id', 'desc')
                    ->when($is_revenue  !== null && $is_revenue !== '', function ($query) use ($is_revenue) {
                        $query->where('is_revenue', filter_var($is_revenue, FILTER_VALIDATE_BOOLEAN));
                    })
                    ->search($search)
                    ->when($limit, function ($query) use ($limit) {
                        $query->take($limit);
                    })
                    ->skip(($page - 1) * $limit)
                    ->get();

                foreach ($revenue_expenditures as $revenue_expenditure) {
                    $revenue_expenditure->customer  = null;
                    $revenue_expenditure->staff  = null;
                    $revenue_expenditure->supplier = null;
                    if ($revenue_expenditure->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_CUSTOMER) {
                        $revenue_expenditure->customer = Customer::where('id',  $revenue_expenditure->recipient_references_id)->first();
                    }

                    if ($revenue_expenditure->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_STAFF) {
                        $revenue_expenditure->staff = Staff::where('id',  $revenue_expenditure->recipient_references_id)->first();
                    }

                    if ($revenue_expenditure->recipient_group == RevenueExpenditureUtils::RECIPIENT_GROUP_SUPPLIER) {
                        $revenue_expenditure->supplier = Supplier::where('id',  $revenue_expenditure->recipient_references_id)->first();
                    }
                }

                return view('exports.revenue_expenditures', [
                    'revenue_expenditures' => $revenue_expenditures
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

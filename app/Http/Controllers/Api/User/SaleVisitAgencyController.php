<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\Helper;
use App\Helper\StatusDefineCode;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Staff;
use App\Models\MsgCode;
use App\Models\SaleVisitAgency;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPUnit\TextUI\Help;

class SaleVisitAgencyController extends Controller
{
    function getOneSaleVisitAgency(Request $request)
    {
        $saleVisitAgency = SaleVisitAgency::with('agency')
            ->where('id', $request->sale_visit_agency_id)
            ->first();

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $saleVisitAgency,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }
    function index(Request $request)
    {
        try {
            $fromTime = Carbon::parse($request->from_time);
            $toTime = Carbon::parse($request->to_time);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 400);
        }
        $saleVisitAgency = SaleVisitAgency::with('agency')->orderByDesc('id')
            ->where(function ($query) use ($request) {
                if ($request->staff_id != null) {
                    $query->where('staff_id', $request->staff_id);
                } else if ($request->staff != null) {
                    $query->where('staff_id', $request->staff->id);
                }
            })
            ->when($request->agency_id != null, function ($query) use ($request) {
                $query->where('sale_visit_agencies.agency_id', '=', $request->agency_id);
            })
            ->when($toTime != null, function ($query) use ($toTime, $fromTime) {
                $query->where('sale_visit_agencies.time_checkin', '<=', $toTime->format('Y-m-d 23:59:59'));
            })
            ->when($fromTime != null, function ($query) use ($fromTime) {
                $query->where('sale_visit_agencies.time_checkin', '>=', $fromTime->format('Y-m-d 00:00:01'));
            })
            ->paginate($request->limit ?? 20);

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $saleVisitAgency,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    function checkIn(Request $request)
    {
        $imageSaleVisitAgency = [];
        $now = Helper::getTimeNowCarbon();

        try {
            $timeCheck = Carbon::parse($request->time_checkin);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 400);
        }

        $agency = Agency::where([
            ['id', $request->agency_id],
            ['store_id', $request->store->id],
        ])->first();

        if (!$agency) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_AGENCY_EXISTS[0],
                'msg' => MsgCode::NO_AGENCY_EXISTS[1],
            ], 400);
        }

        // $saleVisitAgencyExist = SaleVisitAgency::where([
        //     ['store_id', $request->store->id],
        //     ['staff_id', $request->staff->id],
        //     ['agency_id', $request->agency_id],
        // ])
        //     ->whereNotNull('time_checkin')
        //     ->whereDate('time_checkin', $timeCheck)->first();

        // if ($saleVisitAgencyExist) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::STAFF_HAVE_CHECKIN_AT_THIS_AGENCY[0],
        //         'msg' => MsgCode::STAFF_HAVE_CHECKIN_AT_THIS_AGENCY[1],
        //     ], 400);
        // }

        $saleVisitAgency = SaleVisitAgency::create([
            'store_id' => $request->store->id,
            'staff_id' => $request->staff->id,
            'agency_id' => $request->agency_id,
            'time_checkin' => $timeCheck,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'note' => $request->note,
            'address_checkin' => $request->address_checkin,
            'images' => json_encode($request->images),
            'device_name' => $request->device_name,
            'is_agency_open' => $request->is_agency_open ?? false
        ]);

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $saleVisitAgency,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    function checkout(Request $request)
    {
        $timeCheckout = null;
        $timeCheckin = null;
        $imageSaleVisitAgency = [];
        $timeCheckout = null;
        $saleVisitAgency = SaleVisitAgency::where([
            ['id', $request->sale_visit_agency_id],
            ['staff_id', $request->staff->id]
        ])->first();


        if (!$saleVisitAgency) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::NO_HISTORY_SALE_VISIT_AGENCY_EXISTS[0],
                'msg' => MsgCode::NO_HISTORY_SALE_VISIT_AGENCY_EXISTS[1],
            ], 400);
        }

        try {

            $timeCheckin = Carbon::parse($saleVisitAgency->time_checkin);
            $timeCheckout = Carbon::parse($saleVisitAgency->time_checkout);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 400);
        }

        if ($timeCheckin > $timeCheckout) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::INVALID_TIME[0],
                'msg' => MsgCode::INVALID_TIME[1],
            ], 400);
        }


        $saleVisitAgency->update([
            'time_checkout' => $request->time_checkout ?? $saleVisitAgency->time_checkout,
            'device_name' => $request->device_name ?? $saleVisitAgency->device_name,
            'is_agency_open' => $request->is_agency_open ?? $saleVisitAgency->is_agency_open,
            'lat_checkout' => $request->lat_checkout ?? $saleVisitAgency->lat_checkout,
            'long_checkout' => $request->long_checkout ?? $saleVisitAgency->long_checkout,
            'time_visit' => strtotime($timeCheckout) - strtotime($timeCheckin),
            'images' => json_encode($request->images),
            'note' => $request->note ?? $saleVisitAgency->note,
            'address_checkin' => $request->address_checkin ?? $saleVisitAgency->address_checkin,
        ]);


        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $saleVisitAgency,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    function reportSaleVisitAgency(Request $request)
    {

        $fromTime = $request->from_time;
        $toTime = $request->to_time;
        $dataRes = [];
        try {
            $fromTime = Carbon::parse($request->from_time);
            $toTime = Carbon::parse($request->to_time);
        } catch (\Throwable $th) {
        }


        $saleVisitAgency = SaleVisitAgency::where([
            ['store_id', $request->store->id]
        ])
            ->when($fromTime != null || $toTime != null, function ($query) use ($fromTime, $toTime) {
                if ($fromTime) {
                    $query->where('created_at', '>=', $fromTime);
                }
                if ($toTime) {
                    $query->where('created_at', '<=', $toTime);
                }
            });

        //Config
        $carbon = Carbon::now('Asia/Ho_Chi_Minh');
        $date1 = $carbon->parse($fromTime);
        $date2 = $carbon->parse($toTime);



        $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
        $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        $date1 = $carbon->parse($dateFrom);
        $date2 = $carbon->parse($dateTo);


        //check loại charts
        $type = 'month';
        $date2Compare = clone $date2;

        if ($date2Compare->subDays(2) <= $date1) {
            $type = 'hour';
        } else 
            if ($date2Compare->subMonths(2) < $date1) {
            $type = 'day';
        } else 
            if ($date2Compare->subMonths(24) < $date1) {
            $type = 'month';
        }

        if ($date2->year - $date1->year > 2) {
            return new Exception(MsgCode::GREAT_TIME[1]);;
        }

        if ($type == 'hour') {
            for ($i = $date1; $i <= $date2; $i->addHours(1)) {
                $charts[$i->format('Y-m-d H:00:00')] = [
                    'time' => $i->format('Y-m-d H:00:00'),
                    'staff_id' => null,
                    'images_count' => 0,
                    'total_checkin' => 0,
                    'total_checkout' => 0,
                    'total_time_visit' => 0,
                ];
            }

            $saleVisitAgency = $saleVisitAgency->select(
                'staff_id',
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('count(time_checkin) as total_checkin'),
                DB::raw('count(time_checkout) as total_checkout'),
                DB::raw('sum(time_visit) as total_time_visit'),
                'images'
            )
                ->groupBy('staff_id', 'year', 'month', 'day', 'hour');
        } else if ($type == 'day') {
            for ($i = $date1; $i <= $date2; $i->addDays(1)) {
                $charts[$i->format('Y-m-d')] = [
                    'time' => $i->format('Y-m-d'),
                    'staff_id' => null,
                    'images_count' => 0,
                    'total_checkin' => 0,
                    'total_checkout' => 0,
                    'total_time_visit' => 0,
                ];
            }

            $saleVisitAgency = $saleVisitAgency->select(
                'staff_id',
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('DAY(created_at) as day'),
                DB::raw('count(time_checkin) as total_checkin'),
                DB::raw('count(time_checkout) as total_checkout'),
                DB::raw('sum(time_visit) as total_time_visit'),
                'images'
            )
                ->groupBy('staff_id', 'year', 'month', 'day');
        } else if ($type == 'month') {
            for ($i = $date1; $i <= $date2; $i->addMonths(1)) {
                $charts[$i->format('Y-m')] = [
                    'time' => $i->format('Y-m-d'),
                    'staff_id' => null,
                    'images_count' => 0,
                    'total_checkin' => 0,
                    'total_checkout' => 0,
                    'total_time_visit' => 0,
                ];
            }

            $saleVisitAgency = $saleVisitAgency->select(
                'staff_id',
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(time_checkin) as total_checkin'),
                DB::raw('count(time_checkout) as total_checkout'),
                DB::raw('sum(time_visit) as total_time_visit'),
                'images'
            )
                ->groupBy('staff_id', 'year', 'month');
        } else if ($type == 'year') {

            for ($i = $date1; $i <= $date2; $i->addYear(1)) {
                $charts[$i->format('Y')] = [
                    'time' => $i->format('Y-m-d'),
                    'staff_id' => null,
                    'images_count' => 0,
                    'total_checkin' => 0,
                    'total_checkout' => 0,
                    'total_time_visit' => 0,
                ];
            }
            $saleVisitAgency = $saleVisitAgency->select(
                'staff_id',
                DB::raw('YEAR(created_at) as year'),
                DB::raw('count(time_checkin) as total_checkin'),
                DB::raw('count(time_checkout) as total_checkout'),
                DB::raw('sum(time_visit) as total_time_visit'),
                'images'
            )
                ->groupBy('staff_id', 'year');
        }

        $originCharts = $charts;

        $saleVisitAgency = $saleVisitAgency->get();
        $listStaff = Staff::where('store_id', $request->store->id)->select('username', 'phone_number', 'store_id', 'name')->get();

        foreach ($listStaff as $staff) {
            $charts = $originCharts;
            foreach ($charts as $key => $chart) {
                $chartDatetime = new Datetime($chart['time']);
                foreach ($saleVisitAgency as $dataMain) {
                    if ($type == 'hour') {
                        $dateCreatedAt = new DateTime($dataMain->year . '-' . $dataMain->month . '-' . $dataMain->day . ' ' . $dataMain->hour . ':00:00');
                    } else if ($type == 'day') {
                        $dateCreatedAt = new Datetime($dataMain->year . '-' . $dataMain->month . '-' . $dataMain->day);
                    } else if ($type == 'month') {
                        $dateCreatedAt = new Datetime($dataMain->year . '-' . $dataMain->month);
                    } else if ($type == 'year') {
                        $dateCreatedAt = new Datetime($dataMain->year);
                    }

                    if (
                        $type == 'year' &&
                        ($chartDatetime->format('Y') == $dateCreatedAt->format('Y')) &&
                        $staff->id == $dataMain->staff_id
                    ) {

                        $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                        $charts[$key]['staff_id'] = $staff->id;
                        $charts[$key]['total_checkin'] = ($charts[$key]["total_checkin"] ?? 0) + ($dataMain->total_checkin);
                        $charts[$key]['total_checkout'] = ($charts[$key]["total_checkout"] ?? 0) + ($dataMain->total_checkout);
                        $charts[$key]['total_time_visit'] = ($charts[$key]["total_time_visit"] ?? 0) + ($dataMain->total_time_visit);
                        $charts[$key]['images_count'] = ($charts[$key]["images_count"] ?? 0) +  (is_array($dataMain->images_count) ? count($dataMain->images_count ?? 0) : 0);
                    } else if (
                        $type == 'quarter' &&
                        ($chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m')) &&
                        $staff->id == $dataMain->staff_id
                    ) {
                        $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                        $charts[$key]['staff_id'] = $staff->id;
                        $charts[$key]['total_checkin'] = ($charts[$key]["total_checkin"] ?? 0) + ($dataMain->total_checkin);
                        $charts[$key]['total_checkout'] = ($charts[$key]["total_checkout"] ?? 0) + ($dataMain->total_checkout);
                        $charts[$key]['total_time_visit'] = ($charts[$key]["total_time_visit"] ?? 0) + ($dataMain->total_time_visit);
                        $charts[$key]['images_count'] = ($charts[$key]["images_count"] ?? 0) +  (is_array($dataMain->images_count) ? count($dataMain->images_count ?? 0) : 0);
                    } else if (
                        $type == 'month' &&
                        ($chartDatetime->format('Y-m') == $dateCreatedAt->format('Y-m')) &&
                        $staff->id == $dataMain->staff_id
                    ) {
                        $charts[$key]['time'] = $chartDatetime->format('Y-m-d');
                        $charts[$key]['staff_id'] = $staff->id;
                        $charts[$key]['total_checkin'] = ($charts[$key]["total_checkin"] ?? 0) + ($dataMain->total_checkin);
                        $charts[$key]['total_checkout'] = ($charts[$key]["total_checkout"] ?? 0) + ($dataMain->total_checkout);
                        $charts[$key]['total_time_visit'] = ($charts[$key]["total_time_visit"] ?? 0) + ($dataMain->total_time_visit);
                        $charts[$key]['images_count'] = ($charts[$key]["images_count"] ?? 0) +  (is_array($dataMain->images_count) ? count($dataMain->images_count ?? 0) : 0);
                    } else if (
                        $type == 'hour' &&
                        ($chartDatetime->format('Y-m-d H:00:00') == $dateCreatedAt->format('Y-m-d H:00:00')) &&
                        $staff->id == $dataMain->staff_id
                    ) {
                        $charts[$key]['staff_id'] = $staff->id;
                        $charts[$key]['total_checkin'] = ($charts[$key]["total_checkin"] ?? 0) + ($dataMain->total_checkin);
                        $charts[$key]['total_checkout'] = ($charts[$key]["total_checkout"] ?? 0) + ($dataMain->total_checkout);
                        $charts[$key]['total_time_visit'] = ($charts[$key]["total_time_visit"] ?? 0) + ($dataMain->total_time_visit);
                        $charts[$key]['images_count'] = ($charts[$key]["images_count"] ?? 0) +  (is_array($dataMain->images_count) ? count($dataMain->images_count ?? 0) : 0);
                    } else if (
                        $key == $dateCreatedAt->format('Y-m-d') &&
                        $type == 'day' &&
                        $staff->id == $dataMain->staff_id
                    ) {
                        $charts[$key]['staff_id'] = $staff->id;
                        $charts[$key]['total_checkin'] = ($charts[$key]["total_checkin"] ?? 0) + ($dataMain->total_checkin);
                        $charts[$key]['total_checkout'] = ($charts[$key]["total_checkout"] ?? 0) + ($dataMain->total_checkout);
                        $charts[$key]['total_time_visit'] = ($charts[$key]["total_time_visit"] ?? 0) + ($dataMain->total_time_visit);
                        $charts[$key]['images_count'] = ($charts[$key]["images_count"] ?? 0) +  (is_array($dataMain->images_count) ? count($dataMain->images_count ?? 0) : 0);
                    }
                }
            }
            array_push($dataRes, [
                'staff' => $staff,
                'data' => array_values($charts)
            ]);
        }

        $dataChart = [
            'charts' => $dataRes,
            'type_chart' => $type,
            'total_form_area' => 0,
        ];

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $dataChart,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    function overviewSaleVisitAgencyIndex(Request $request)
    {

        $fromTime = $request->from_time;
        $toTime = $request->to_time;
        $dataRes = [];
        try {
            $fromTime = Carbon::parse($request->from_time);
            $toTime = Carbon::parse($request->to_time);
        } catch (\Throwable $th) {
        }

        $listStaff = Staff::leftJoin('sale_visit_agencies', 'staff.id', '=', 'sale_visit_agencies.staff_id')
            ->where('staff.store_id', $request->store->id)
            ->withCount(
                [
                    'order as order_sum_total' => function ($query) use ($toTime, $fromTime) {
                        $query->where('store_id', request('store')->id)
                            ->where('order_status', StatusDefineCode::COMPLETED)
                            ->where('payment_status', StatusDefineCode::PAID)
                            ->when($fromTime != null, function ($query) use ($fromTime) {
                                $query->where('orders.created_at', '>=', $fromTime->format('y-m-d H:i:s'));
                            })
                            ->when($toTime != null, function ($query) use ($toTime) {
                                $query->where('orders.created_at', '<=', $toTime->format('y-m-d H:i:s'));
                            })
                            ->select('orders.total_final');
                    }
                ],
            )
            ->select(
                'staff.id',
                'staff.phone_number',
                'staff.name',
                'staff.avatar_image',
                'sale_visit_agencies.time_checkout'
            )
            ->groupBy('staff.id', 'sale_visit_agencies.time_checkout')
            ->orderByDesc('sale_visit_agencies.time_checkout')
            ->get();
        //         $sum = Order::where('store_id', request('store')->id)
        //             ->where('orders.sale_by_staff_id',  $this->id)
        //             ->where('order_status', StatusDefineCode::COMPLETED)
        //             ->where('payment_status', StatusDefineCode::PAID)
        //             ->when(request('from_time') != null, function($query) {
        //                 $query->where('orders.created_at', '>=', request('from_time'));
        //             })
        //             ->when(request('from_time') != null, function($query) {
        //                 $query->where('orders.created_at', '>=', request('from_time'));
        //             })
        //             ->where('orders.created_at', '<=', $dateToDay)
        $arrStaff = [];
        $arrTemp = [];
        foreach ($listStaff as $staff) {
            if (!in_array($staff->id, $arrTemp)) {
                array_push($arrTemp, $staff->id);
                array_push($arrStaff, [
                    'id' => $staff->id,
                    'phone_number' => $staff->phone_number,
                    'staff_name' => $staff->name,
                    'sale_avatar_image' => $staff->avatar_image,
                    'total_staff_visit' => 0,
                    'total_time_visit' => 0,
                    'longitude' => 0,
                    'latitude' => 0,
                    'device_name' => 0,
                ]);
            }
        }

        $staffSaleVisitAgency = DB::table('sale_visit_agencies')
            ->where('sale_visit_agencies.store_id', $request->store->id)
            ->when($toTime != null, function ($query) use ($toTime) {
                $query->whereDate('sale_visit_agencies.time_checkout', '=', $toTime);
            })
            // ->when($fromTime != null, function ($query) use ($fromTime) {
            //     $query->where('sale_visit_agencies.time_checkin', '>=', $fromTime);
            // })
            ->select(
                'sale_visit_agencies.staff_id as staff_id',
                DB::raw('count(time_checkout) as total_staff_visit'),
                DB::raw('sum(sale_visit_agencies.time_visit) as total_time_visit'),
                'long_checkout as longitude',
                'lat_checkout as latitude'
            )
            ->groupBy('staff_id')
            ->get();

        foreach ($arrStaff as &$staff) {
            if (!empty($staffSaleVisitAgency) && count($staffSaleVisitAgency) > 0) {
                foreach ($staffSaleVisitAgency as $itemSaleAgency) {
                    if ($staff['id'] == $itemSaleAgency->staff_id) {
                        $staff['total_staff_visit'] = $itemSaleAgency->total_staff_visit;
                        $staff['total_time_visit'] = $itemSaleAgency->total_time_visit;
                        $staff['longitude'] = $itemSaleAgency->longitude;
                        $staff['latitude'] = $itemSaleAgency->latitude;
                        $staff['device_name'] = null;
                    }
                }
            } else {
                $staff['total_staff_visit'] = 0;
                $staff['total_time_visit'] = 0;
                $staff['device_name'] = null;
                $staff['longitude'] = 0;
                $staff['latitude'] = 0;
            }
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $arrStaff,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
        ], 200);
    }

    function getOneAgency(Request $request)
    {
        $saleVisitAgency = null;
        if (filter_var($request->is_latest_checkin) == true && (!is_numeric($request->agency_id) || empty($request->agency_id))) {
            $saleVisitAgency = SaleVisitAgency::where('store_id', $request->store->id)
                ->where('staff_id', $request->staff->id)
                ->orderBy('time_checkin', 'desc')
                ->first();
        }
        $agencies = Agency::with('staff_sale_visit_agency')
            ->where('agencies.store_id', $request->store->id)
            ->where('agencies.status', true)
            ->where(function ($query) use ($request, $saleVisitAgency) {
                if ($request->agency_id != null && !isset($request->is_latest_checkin)) {
                    $query->where('id', $request->agency_id);
                } else if ($saleVisitAgency != null) {
                    $query->where('id', $saleVisitAgency->agency_id);
                }
            })
            ->first();

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $agencies,
        ], 200);
    }

    function getAgency(Request $request)
    {
        $now = Helper::getTimeNowCarbon();
        $categoryIds = request("category_ids") == null ? [] : explode(',', request("category_ids"));

        $agency_type_id = request("agency_type_id");

        $agencies = Agency::with('staff_sale_visit_agency')
            ->sortByRelevance(true)
            ->when(Agency::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy($sortColumn, filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when($agency_type_id != null, function ($query) use ($agency_type_id) {
                $query->where('agency_type_id', '=',   $agency_type_id);
            })
            ->whereHas('customer', function (Builder $query) use ($request) {
                $query->where('sale_staff_id', $request->staff->id);
            })
            ->where('agencies.store_id', $request->store->id)
            ->where('agencies.status', true)
            ->orderBy('id', 'desc')
            ->search(request('search'))
            ->paginate(request('limit') ?? 20);

        // $agency = Agency::join('sale_visit_agencies', 'agencies.id', '=', 'sale_visit_agencies.agency_id')
        //     ->where([
        //         ['sale_visit_agencies.store_id', $store->id],
        //         ['sale_visit_agencies.staff_id', $staff->id]
        //     ])
        //     ->whereDate('sale_visit_agencies.created_at', $now)
        //     ->whereNotNull('sale_visit_agencies.time_checkin')
        //     ->orderBy('sale_visit_agencies.created_at', 'desc')
        //     ->first();

        $is_staff_have_checkout_agency = SaleVisitAgency::where([
            ['sale_visit_agencies.store_id', $request->store->id],
            ['sale_visit_agencies.staff_id', $request->staff->id]
        ])
            ->whereDate('sale_visit_agencies.created_at', $now)
            ->whereNotNull('sale_visit_agencies.time_checkin')
            ->orderBy('sale_visit_agencies.created_at', 'desc')
            ->first();

        $saleVisitAgencyLatest = SaleVisitAgency::where('store_id', $request->store->id)
            ->where('staff_id', $request->staff->id)
            ->whereNull('time_checkout')
            ->whereDate('time_checkin', $now)
            ->orderBy('time_checkin', 'desc')
            ->first();
        if ($saleVisitAgencyLatest != null) {
            $agencyAreCheckin = Agency::with('staff_sale_visit_agency')
                ->where('agencies.store_id', $request->store->id)
                ->where('agencies.id', $saleVisitAgencyLatest->agency_id)
                ->where('agencies.status', true)
                ->first();
        } else {
            $agencyAreCheckin = null;
        }


        if ($is_staff_have_checkout_agency == null) {
            $is_staff_have_checkout_agency = null;
        } else if ($is_staff_have_checkout_agency->time_checkout != null) {
            $is_staff_have_checkout_agency = true;
        } else {
            $is_staff_have_checkout_agency = false;
        }


        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            "is_staff_have_checkout_agency" => $is_staff_have_checkout_agency, // có nhân viên check out tại đại lí
            'agency_are_checkin' => $agencyAreCheckin, // đại lí đang đc checkin
            'data' => $agencies,
        ], 200);
    }
}

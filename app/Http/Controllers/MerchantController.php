<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {

        $ordersInRange=Order::where('created_at','>=',$request->from)->where('created_at','<=',$request->to)->get();

        $noAffiliate=$ordersInRange->whereNull('affiliate_id')->first();
        $noAffiliateCommission=$noAffiliate?$noAffiliate->commission_owed:0;
        $response=[
            'count'=>count($ordersInRange),
            'revenue'=> $ordersInRange->sum('subtotal'),
            'commission_owed'=> $ordersInRange->sum('commission_owed')-$noAffiliateCommission,
        ];

        return response()->json($response);
        // TODO: Complete this method
    }
}
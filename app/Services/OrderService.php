<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class OrderService
{
    use RefreshDatabase;
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        if (array_key_exists('order_id',$data)){
            $externalOrderId=$data['order_id'];
        }else{
            $externalOrderId=$data['external_order_id'];
        }

        $alreadyRegistered=Order::where('order_id',$externalOrderId)->exists();
        if (!$alreadyRegistered){
            $merchant=Merchant::where('domain',$data['merchant_domain'])->first();
            $alreadyAffiliate=User::where('email',$data['customer_email'])->exists();
            if (!$alreadyAffiliate){
                $this->affiliateService->register($merchant,$data['customer_email'],$data['customer_name'],0.1);
            }
            $order=new Order();
            $order->external_order_id=$externalOrderId;
            $order->merchant_id=$merchant->id;
            $order->affiliate_id=1;
            $order->subtotal=$data['subtotal_price'];
            $order->commission_owed= round($data['subtotal_price'] * 0.1, 2);
            $order->payout_status=Order::STATUS_UNPAID;
            $order->discount_code=$data['discount_code'];
            $order->customer_email=$data['customer_email'];
            $order->customer_name=$data['customer_name'];
            $order->save();

        }
    }
}

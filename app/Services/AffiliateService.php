<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Validation\ValidationException;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService,
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        AffiliateCreateException::emailInUse($email);
        $user=new User();
        $user->name=$name;
        $user->email=$email;
        $user->password=Hash::make('1234567890');
        $user->type=User::TYPE_AFFILIATE;
        $user->save();
        $affiliate=new Affiliate();
        $affiliate->user_id=$user->id;
        $affiliate->merchant_id=$merchant->id;
        $affiliate->commission_rate=$commissionRate;
        $affiliate->discount_code=$this->apiService->createDiscountCode($merchant)['code'];
        $affiliate->save();
        $affiliate=Affiliate::find($affiliate->id);
        return $affiliate;

    }

}

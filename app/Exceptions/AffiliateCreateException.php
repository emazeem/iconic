<?php

namespace App\Exceptions;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AffiliateCreateException extends \RuntimeException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function emailInUse(string $email)
    {
        $merchantExists=User::where('email',$email)->where('type',User::TYPE_MERCHANT)->exists();
        $affiliateExists=Affiliate::where('email',$email)->where('type',User::TYPE_AFFILIATE)->exists();
        if ($merchantExists || $affiliateExists) {
            $message = 'Email is already in use by a merchant or affiliate.';
            throw new self($message);
        }
    }

}

<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        /**
         * Validate alpha spaces
         */
        Validator::extend('alpha_spaces', function ($attribute, $value) {
            return preg_match('/^[\pL\s]+$/u', $value);
        });

        /**
         * Validate phone number
         */
        Validator::extend('phone', function ($attribute, $value) {
            $conditions = [];
            $conditions[] = strlen($value) >= 10;
            $conditions[] = strlen($value) <= 16;
            $conditions[] = preg_match("/[^\d]/i", $value) === 0;

            $isDigit = (bool)array_product($conditions);

            $isE123 = preg_match('/^(?:\((\+?\d+)?\)|\+?\d+) ?\d*(-?\d{2,3} ?){0,4}$/', $value) === 1 && strlen($value) <= 16;

            $conditions = [];
            $conditions[] = strpos($value, "+") === 0;
            $conditions[] = strlen($value) >= 9;
            $conditions[] = strlen($value) <= 16;
            $conditions[] = preg_match("/[^\d+]/i", $value) === 0;

            $isE164 = (bool)array_product($conditions);

            $conditions = [];
            $conditions[] = preg_match("/^(?:\+1|1)?\s?-?\(?\d{3}\)?(\s|-)?\d{3}-\d{4}$/i", $value) > 0;

            $isNANP = (bool)array_product($conditions) && strlen($value) <= 16;

            return $isE123 || $isE164 || $isNANP || $isDigit;
        });
    }
}

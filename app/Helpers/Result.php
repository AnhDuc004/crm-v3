<?php

namespace App\Helpers;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Result
 *
 * @author LENOVO 
 */
class Result
{

    public static function success($data = null)
    {
        return response()->json([
            config('constparam.error_code') => config('constparam.success'),
            config('constparam.error_mess') => config('constparam.success_mess'), config('constparam.result') => $data
        ]);
    }

    public static function fail($error_mess = null)
    {
        return response()->json([
            config('constparam.error_code') => config('constparam.fail'),
            config('constparam.error_mess') => $error_mess ?: config('constparam.fails_mess')
        ]);
    }

    public static function requestInvalid($error_mess = null)
    {
        $dataMessError = null;
        if (!empty($error_mess)) {
            $keyError =  $error_mess->keys();
            foreach ($keyError as $key) {
                $dataMessError[$key] = $error_mess->first($key);
            }
        }
        return response()->json([
            config('constparam.error_code') => config('constparam.invalid'),
            config('constparam.error_mess') =>  $dataMessError ?: config('constparam.invalid_mess')
        ]);
    }

    public static function permission()
    {
        return response()->json([
            config('constparam.error_code') => config('constparam.permission'),
            config('constparam.error_mess') => config('constparam.permission_mess')
        ]);
    }
}

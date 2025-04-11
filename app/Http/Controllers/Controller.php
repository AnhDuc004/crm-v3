<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class KlineController
 *
 * @package App\Http\Controllers
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     description="API for managing Binance Klines signals",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}

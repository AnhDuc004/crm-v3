<?php

namespace Modules\Customer\Repositories\Customer;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Result;
use Modules\Customer\Entities\Currencies;
use Modules\Customer\Repositories\Customer\CurrenciesInterface;

class CurrenciesRepository implements CurrenciesInterface
{
    const messageCodeError = ' Tiền tệ không tồn tại';

    public function findId($id)
    {
        $currencies = Currencies::find($id);
        if (!$currencies) {
            return Result::fail(static::messageCodeError);
        }
        return Result::success($currencies);
    }

    public function listAll($queryData)
    
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;

        $baseQuery = Currencies::query();
        if($search)
        {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
                                  
        }

        $currencies = $baseQuery->orderBy('id', 'desc');

        if($limit > 0){
            $currencies = $baseQuery->paginate($limit);
        }
        else {
            $currencies = $baseQuery->get();
        }

        return Result::success($currencies);
    }

    public function listSelect()
    {
        $currenciess =  Currencies::orderBy('name')->get();
        return Result::success($currenciess);
    }

    public function create($requestData)
    {
        try {
            $currencies =  new Currencies($requestData);
            $result =  $currencies->save();
            if (!$result) {
                return Result::fail('Tạo tiền tệ thất bại.');
            }
            return Result::success($currencies);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail('Tạo tiền tệ thất bại.');
        }
    }

    public function update($id, $requestData)
    {
        try {
            $currencies = Currencies::find($id);
            if (!$currencies) {
                return Result::fail(static::messageCodeError);
            }
            $currencies->fill($requestData);
            $result =  $currencies->save();
            if (!$result) {
                return Result::fail('Sửa tiền tệ thất bại.');
            }
            return Result::success($currencies);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail('Sửa tiền tệ thất bại.');
        }
    }

    public function destroy($id)
    {
        try {
            $currencies = Currencies::find($id);
            if (!$currencies) {
                return Result::fail(static::messageCodeError);
            }
            $currencies->delete();
            return Result::success();
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return Result::fail('Xóa tiền tệ thất bại.');
        }
    }
}

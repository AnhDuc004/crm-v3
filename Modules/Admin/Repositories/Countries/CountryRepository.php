<?php

namespace Modules\Admin\Repositories\Countries;
use Modules\Admin\Entities\Countries;

class CountryRepository implements CountryInterface
{

    public function findId($id)
    {
        $countries = Countries::find($id);
        if (!$countries) {
            return null;
        }
        return $countries;
    }

    public function listAll($queryData)
    {
        // $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 10;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;
        $order_name = isset($queryData["order_name"]) ? $queryData["order_name"] : 'short_name';
        $order_type = isset($queryData["order_type"]) ? $queryData["order_type"] : 'asc';

        $baseQuery = Countries::query();

        if ($search) {
            $baseQuery = $baseQuery->where('short_name', 'like', '%' . $search . '%')
                ->orWhere('long_name', 'like', '%' . $search . '%');
        }

        $countries = $baseQuery->orderBy($order_name, $order_type);

        //Riêng api cho country thì không có nhiều thay đổi, không có nhiều chỗ sử dụng, và hầu hết sử dụng đều chỉ ở mức call all
        // nên tạm thời không cần phân trang, sẽ thay đổi trong tình hình thực tế tiếp theo
        // if ($limit > 0) {
        //     $countries = $baseQuery->paginate($limit);
        // } else {
        $countries = $baseQuery->get();
        // }

        return $countries;
    }


    public function create($requestData)
    {
        $countries =  new Countries();
        $countries->fill($requestData);
        $countries->save();
        return $countries;
    }

    public function update($id, $requestData)
    {
        $countries = Countries::find($id);
        if (!$countries) {
            return null;
        }
        $countries->fill($requestData);
        $countries->save();
        return $countries;
    }

    public function destroy($id)
    {
        $countries = Countries::find($id);
        if (!$countries) {
            return null;
        }
        $countries->delete();
        return $countries;
    }
}

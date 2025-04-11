<?php

namespace Modules\Admin\Repositories\Currency;
use Modules\Admin\Entities\Currencies;

class CurrencyRepository implements CurrencyInterface
{
    public function findId($id)
    {
        $currency = Currencies::find($id);
        if (!$currency) {
            return null;
        }
        return $currency;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Currencies::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $currency = $baseQuery->paginate($limit);
        } else {
            $currency = $baseQuery->get();
        }

        return $currency;
    }

    public function listSelect() {}

    public function create($request)
    {
        $currency = new Currencies($request);
        $currency->save();
        return $currency;
    }

    public function update($id, $request)
    {
        $currency = Currencies::find($id);
        if (!$currency) {
            return null;
        }
        $currency->fill($request);
        $currency->save();
        return $currency;
    }

    public function destroy($id)
    {
        $currency = Currencies::find($id);
        if (!$currency) {
            return null;
        }
        $currency->delete();
        return $currency;
    }
}

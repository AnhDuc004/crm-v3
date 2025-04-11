<?php

namespace Modules\Inventory\Repositories\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Entities\Unit;

class UnitRespository implements UnitInterface
{

    public function findId($id)
    {
        $unit = Unit::find($id);
        return $unit;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 10;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;
        $baseQuery = Unit::query()->orderBy('created_at', 'desc');
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like', '%' . $search . '%');
        }
        if ($limit > 0) {
            $unit = $baseQuery->paginate($limit);
        } else {
            $unit = $baseQuery->get();
        }
        return $unit;
    }

    public function create($request)
    {
        $unit = new Unit($request->all());
        $unit->created_by = Auth::id();
        $unit->save();
        return $unit;
    }

    public function update($id, $request)
    {
        $unit = Unit::findOrFail($id);
        $unit->updated_by = Auth::id();
        $unit->update($request->all());
        return $unit;
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
        return $unit;
    }
}

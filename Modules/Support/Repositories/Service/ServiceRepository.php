<?php

namespace Modules\Support\Repositories\Service;

use Modules\Support\Entities\Service;

class ServiceRepository implements ServiceInterface
{
    public function findId($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return null;
        }
        return $service;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Service::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        $service = $baseQuery->orderBy('serviceid', 'desc');
        if ($limit > 0) {
            $service = $baseQuery->paginate($limit);
        } else {
            $service = $baseQuery->get();
        }
        return $service;
    }

    public function create($request)
    {
        $service = new Service($request);
        $service->save();
        return $service;
    }

    public function update($id, $request)
    {
        $service = Service::find($id);
        if (!$service) {
            return null;
        }
        $service->fill($request);
        $service->save();
        return $service;
    }

    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return null;
        }
        $service->delete();
        return $service;
    }
}

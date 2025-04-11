<?php

namespace Modules\Customer\Repositories\Service;

use Modules\Customer\Entities\Service;

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

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $baseQuery = Service::query();

        if ($limit > 0) {
            $service = $baseQuery->paginate($limit);
        } else {
            $service = $baseQuery->get();
        }

        return $service;
    }

    public function listSelect() {}

    public function create($requestData)
    {
        $service = new Service($requestData);
        $service->save();
        return $service;
    }

    public function update($id, $requestData)
    {
        $service = Service::find($id);
        if (!$service) {
            return null;
        }
        $service->fill($requestData);
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

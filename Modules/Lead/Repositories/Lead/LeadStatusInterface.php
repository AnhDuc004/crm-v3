<?php

namespace Modules\Lead\Repositories\Lead;

interface LeadStatusInterface
{
    public function findId($id);

    public function listAll($queryData);

    public function listSelect();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);

}

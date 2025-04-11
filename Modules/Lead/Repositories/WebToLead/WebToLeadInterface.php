<?php

namespace Modules\Lead\Repositories\WebToLead;

interface WebToLeadInterface
{
    public function findId($id);

    public function listAll($requestData);

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
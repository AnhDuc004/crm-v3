<?php

namespace Modules\Customer\Repositories\Service;

interface ServiceInterface
{
    public function findId($id);
    
    public function listAll($requestData);

    public function listSelect();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
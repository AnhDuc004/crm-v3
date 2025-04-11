<?php

namespace Modules\Customer\Repositories\Module;

interface ModuleInterface
{
    public function findId($id);
    
    public function listAll($requestData);

    public function listSelect();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
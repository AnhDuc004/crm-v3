<?php

namespace Modules\Customer\Repositories\SpamFilter;

interface SpamFilterInterface
{
    public function findId($id);
    
    public function listAll($requestData);

    public function listSelect();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
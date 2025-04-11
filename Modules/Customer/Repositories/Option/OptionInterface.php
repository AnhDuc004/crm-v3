<?php

namespace Modules\Customer\Repositories\Option;

interface OptionInterface
{
    public function findId($id);

    public function listAll($queryData);

    public function create($requestData);

    public function update($requestData);

    public function destroy($id);
}
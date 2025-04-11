<?php

namespace Modules\Admin\Repositories\Countries;

interface CountryInterface
{
    public function findId($id);

    public function listAll($queryData);

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
<?php

namespace Modules\Inventory\Repositories\Unit;

interface UnitInterface
{
    public function listAll($queryData);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function findId($id);
}

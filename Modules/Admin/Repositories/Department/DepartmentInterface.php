<?php

namespace Modules\Admin\Repositories\Department;

interface DepartmentInterface
{
    public function findId($id);
    
    public function listAll($request);

    public function listSelect();

    public function create($request);

    public function update($id, $request);

    public function destroy($id);
}
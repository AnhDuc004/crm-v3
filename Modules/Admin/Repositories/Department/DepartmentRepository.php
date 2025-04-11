<?php

namespace Modules\Admin\Repositories\Department;

use Illuminate\Support\Facades\Auth;
use Modules\Admin\Entities\Department;

class DepartmentRepository implements DepartmentInterface
{
    public function findId($id)
    {
        $department = Department::find($id);
        if (!$department) {
            return null;
        }
        return $department;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Department::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%')
                ->orWhere('email', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $department = $baseQuery->paginate($limit);
        } else {
            $department = $baseQuery->get();
        }
        return $department;
    }

    public function listSelect() {}

    public function create($request)
    {
        $department = new Department($request);
        $department->created_by = Auth::id();
        $department->save();
        return $department;
    }

    public function update($id, $request)
    {
        $department = Department::find($id);
        if (!$department) {
            return null;
        }
        $department->fill($request);
        $department->updated_by = Auth::id();
        $department->save();
        return $department;
    }

    public function destroy($id)
    {
        $department = Department::find($id);
        if (!$department) {
            return null;
        }
        $department->delete();
        return $department;
    }
}

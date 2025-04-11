<?php

namespace Modules\Customer\Repositories\Module;

use App\Helpers\Result;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Entities\Module;

class ModuleRepository implements ModuleInterface
{
    const messageError = 'Module không tồn tại';

    public function findId($id)
    {
        $module = Module::find($id);
        if (!$module) {
            return Result::fail(self::messageError);
        }
        return Result::success($module);
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $baseQuery = Module::query();

        if ($limit > 0) {
            $module = $baseQuery->paginate($limit);
        }else {
            $module = $baseQuery->get();
        }

        return Result::success($module);
    }

    public function listSelect()
    {

    }

    public function create($requestData)
    {
        try {
            $module = new Module($requestData);
            $module->save();

            return Result::success($module);

        } catch (\Exception $e) {
            
            Log::error($e->getMessage());
            return Result::fail('Thêm module thất bại');
        }
    }

    public function update($id, $requestData)
    {
        try {
            
            $module = Module::find($id);
            if (!$module) {
                return Result::fail(self::messageError);
            }
            $module->fill($requestData);
            $module->save();
            return Result::success($module);

        } catch (\Exception $e) {
            
            Log::error($e->getMessage());
            return Result::fail('Sửa module thất bại');
        }

    }

    public function destroy($id)
    {
        try {
            $module = Module::find($id);
            if (!$module) {
                return Result::fail(self::messageError);
            }
            $module->delete();
            return Result::success('Xoá module thành công');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail('Xoá module thất bại');
        }
    }
}
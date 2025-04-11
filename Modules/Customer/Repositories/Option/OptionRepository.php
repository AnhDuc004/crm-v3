<?php

namespace Modules\Customer\Repositories\Option;

use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Option;

class OptionRepository implements OptionInterface
{

    public function findId($id)
    {
        $option = Option::find($id);
        if (!$option) {
            return null;
        }
        return $option;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;
        $baseQuery = Option::query();

        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like', '%' . $search . '%');
        }

        if ($limit > 0) {
            $option = $baseQuery->paginate($limit);
        } else {
            $option = $baseQuery->get();
        }

        return $option;
    }


    public function create($requestData)
    {
        if (isset($requestData['options'])) {
            foreach ($requestData['options'] as $option) {
                $name = $option['name'];
                $value = $option['value'];
                $autoload = $option['autoload'];
                $option =  new Option($requestData);
                $option->name = $name;
                $option->value = $value;
                $option->autoload = $autoload;
                $option->save();
            }
            return $option;
        }
    }

    public function update($requestData)
    {
        if (isset($requestData['options'])) {
            foreach ($requestData['options'] as $option) {
                $optionId = isset($option['id']) ? $option['id'] : 0;
                $options = Option::findorNew($optionId);
                $name = $option['name'];
                $value = $option['value'];
                $autoload = $option['autoload'];
                $options->name = $name;
                $options->value = $value;
                $options->autoload = $autoload;
                $options->fill($option);
                $options->save();
            }
        }
        return $option;
    }

    public function destroy($id)
    {
        $option = Option::find($id);
        if (!$option) {
            return null;
        }
        $option->delete();
        return $option;
    }
}

<?php

namespace Modules\Customer\Repositories\Tags;

use Modules\Customer\Entities\Tag;
use Illuminate\Support\Facades\Auth;

class TagRepository implements TagInterface
{
    public function findId($id)
    {
        $tag = Tag::find($id);
        return $tag;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $search = isset($queryData["search"]) ? $queryData["search"] : null;
        $order_name = isset($queryData["order_name"]) ? $queryData["order_name"] : 'id';
        $order_type = isset($queryData["order_type"]) ? $queryData["order_type"] : 'desc';
        $baseQuery = Tag::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like', '%' . $search . '%');
        }
        $tag = $baseQuery->orderBy($order_name, $order_type)->with([]);
        if ($limit > 0) {
            $tag = $baseQuery->paginate($limit);
        } else {
            $tag = $baseQuery->get();
        }
        return $tag;
    }

    public function create($requestData)
    {
        $tag =  new Tag($requestData);
        $tag->created_by = Auth::id();
        $tag->save();
        return $tag;
    }

    public function update($id, $requestData)
    {
        $tag = Tag::find($id);
        $tag->fill($requestData);
        $tag->updated_by = Auth::id();
        $tag->save();
        return $tag;
    }

    public function destroy($id)
    {
        $tag = Tag::find($id);
        $tag->leads()->detach();
        $tag->delete();
        return $tag;
    }
}

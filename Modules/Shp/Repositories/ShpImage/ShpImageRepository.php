<?php

namespace Modules\Shp\Repositories\ShpImage;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Shp\Entities\ShpImgae;

class ShpImageRepository implements ShpImageInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $shpId = $queryData['shp_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;

        $query = ShpImgae::with('product');

        if ($shpId) {
            $query->where('shp_id', $shpId);
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }
    public function findById($id)
    {
        $shpImgae = ShpImgae::with('product')->find($id);
        if (!$shpImgae) {
            return null;
        }
        return $shpImgae;
    }

    public function create(array $data)
    {
        if (isset($data['image_file']) && $data['image_file']->isValid()) {
            $path = $data['image_file']->store('shp_images', 'public');
            $data['image_url'] = Storage::url($path);
        }

        $shpImage = new ShpImgae($data);
        $shpImage->fill($data);
        $shpImage->created_by = Auth::id();
        $shpImage->save();

        return ShpImgae::with('product')->find($shpImage->id);
    }

    public function update($id, array $data)
    {
        $shpImgae = ShpImgae::find($id);

        if (!$shpImgae) {
            return null;
        }

        if (isset($data['image_file'])) {
            if ($shpImgae->image_url) {
                $oldPath = str_replace('/storage/', 'public/', $shpImgae->image_url);
                Storage::delete($oldPath);
            }

            $path = $data['image_file']->store('shp_images', 'public');
            $data['image_url'] = Storage::url($path);
            unset($data['image_file']);
        }

        $shpImgae->fill($data);
        $shpImgae->updated_by = Auth::id();
        $shpImgae->save();

        return ShpImgae::with('product')->find($shpImgae->id);
    }

    public function delete($id)
    {
        $shpImgae = ShpImgae::find($id);
        if (!$shpImgae) {
            return null;
        }
        $shpImgae->delete();
        return $shpImgae;
    }
}

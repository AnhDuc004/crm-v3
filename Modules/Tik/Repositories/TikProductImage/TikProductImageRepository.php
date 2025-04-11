<?php

namespace Modules\Tik\Repositories\TikProductImage;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Tik\Entities\TikProductImage;

class TikProductImageRepository implements TikProductImageInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $product_id = isset($queryData['product_id']) ? $queryData['product_id'] : null;
        $query = TikProductImage::with('product');

        if ($product_id) {
            $query->where('product_id', 'like', '%' . $product_id . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $image = TikProductImage::with('product')->find($id);
        if (!$image) {
            return null;
        }
        return $image;
    }

    public function create(array $data)
    {
        $image = new TikProductImage();
        $image->fill($data);
        $image->created_by = Auth::id();

        if (isset($data['images']) && is_array($data['images'])) {
            $imageUrls = [];
            $thumbUrls = [];

            foreach ($data['images'] as $img) {
                $imagePath = $img->store('public/tik_product_images');
                $imageUrls[] = asset(str_replace('public/', 'storage/', $imagePath));

                $thumbnailPath = $img->store('public/tik_product_images/thumbnails');
                $thumbUrls[] = asset(str_replace('public/', 'storage/', $thumbnailPath));
            }

            $image->url_list = json_encode($imageUrls);
            $image->thumb_url_list = json_encode($thumbUrls);
        }

        $image->save();
        $image = TikProductImage::with('product')->find($image->id);
        return $image;
    }

    public function update($id, array $data)
    {
        $image = TikProductImage::with('product')->find($id);
        if (!$image) {
            return null;
        }

        $image->fill($data);
        $image->updated_by = Auth::id();
        $image->save();
        return $image;
    }

    public function updateWithImages($id, array $data)
    {
        $image = TikProductImage::find($id);
        if (!$image) {
            return null;
        }

        $image->fill($data);

        if (isset($data['images'])) {
            $existingUrls = json_decode($image->url_list, true) ?? [];
            $existingThumbs = json_decode($image->thumb_url_list, true) ?? [];

            foreach (array_merge($existingUrls, $existingThumbs) as $oldImage) {
                $filePath = str_replace(asset('storage/'), 'public/', $oldImage);
                Storage::delete($filePath);
            }

            $newUrls = [];
            $newThumbs = [];

            foreach ($data['images'] as $file) {
                $imagePath = $file->store('public/tik_product_images');
                $imageUrl = asset(str_replace('public/', 'storage/', $imagePath));

                $thumbnailPath = $file->store('public/tik_product_images/thumbnails');
                $thumbnailUrl = asset(str_replace('public/', 'storage/', $thumbnailPath));

                $newUrls[] = $imageUrl;
                $newThumbs[] = $thumbnailUrl;
            }

            $image->url_list = json_encode($newUrls);
            $image->thumb_url_list = json_encode($newThumbs);
        }

        $image->updated_by = Auth::id();
        $image->save();

        return $image;
    }

    public function delete($id)
    {
        $image = TikProductImage::find($id);
        if (!$image) {
            return false;
        }

        $urlList = json_decode($image->url_list, true) ?? [];
        $thumbUrlList = json_decode($image->thumb_url_list, true) ?? [];

        foreach (array_merge($urlList, $thumbUrlList) as $url) {
            $filePath = str_replace(asset('storage/'), 'public/', $url);
            Storage::delete($filePath);
        }

        return $image->delete();
    }
}

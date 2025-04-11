<?php

namespace Modules\Tik\Repositories\TikProductCertification;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Tik\Entities\TikProductCertification;

class TikProductCertificationRepository implements TikProductCertificationInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['name']) ? $queryData['name'] : null;
        $product_id = isset($queryData['product_id']) ? $queryData['product_id'] : null;
        $query = TikProductCertification::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($product_id) {
            $query->where('product_id', 'like', '%' . $product_id . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $certification = TikProductCertification::find($id);
        if (!$certification) {
            return null;
        }
        return $certification;
    }

    public function create(array $data, $files = null, $images = null)
    {
        $uploadedFiles = [];
        $uploadedImages = [];

        if ($files) {
            foreach ($files as $file) {
                $uploadedFiles[] = $file->store('certifications/files');
            }
        }

        if ($images) {
            foreach ($images as $image) {
                $uploadedImages[] = $image->store('certifications/images');
            }
        }

        $data['files'] = json_encode($uploadedFiles);
        $data['images'] = json_encode($uploadedImages);

        $certification = new TikProductCertification($data);
        $certification->created_by = Auth::id();
        $certification->save();

        return $certification;
    }

    public function update($id, array $data)
    {
        $certification = TikProductCertification::find($id);
        if (!$certification) {
            return null;
        }

        // Lưu thay đổi vào database
        $certification->fill($data);
        $certification->updated_by = Auth::id();
        $certification->save();
        return $certification;
    }


    public function uploadFiles($id, $files = null, $images = null)
    {
        $certification = TikProductCertification::find($id);
        if (!$certification) {
            return response()->json(['message' => 'Không tìm thấy chứng nhận'], 404);
        }

        $uploadedFiles = is_array($certification->files) ? $certification->files : json_decode($certification->files, true) ?? [];
        $uploadedImages = is_array($certification->images) ? $certification->images : json_decode($certification->images, true) ?? [];

        // Xóa files cũ nếu có file mới
        if ($files && count($files) > 0) {
            foreach ($uploadedFiles as $oldFile) {
                Storage::delete($oldFile);
            }
            $uploadedFiles = [];

            foreach ($files as $file) {
                $path = $file->store('certifications/files');
                $uploadedFiles[] = $path;
            }
        }

        // Xóa images cũ nếu có ảnh mới
        if ($images && count($images) > 0) {
            foreach ($uploadedImages as $oldImage) {
                Storage::delete($oldImage);
            }
            $uploadedImages = [];

            foreach ($images as $image) {
                $path = $image->store('certifications/images');
                $uploadedImages[] = $path;
            }
        }

        $certification->update([
            'files' => json_encode($uploadedFiles),
            'images' => json_encode($uploadedImages),
        ]);

        return  $certification;
    }

    public function delete($id)
    {
        $certification = TikProductCertification::find($id);

        if (!$certification) {
            return null;
        }

        if ($certification->files) {
            $files = json_decode($certification->files, true);
            foreach ($files as $file) {
                Storage::delete($file);
            }
        }
        if ($certification->images) {
            $images = json_decode($certification->images, true);
            foreach ($images as $image) {
                Storage::delete($image);
            }
        }
        $certification->delete();

        return $certification;
    }
}

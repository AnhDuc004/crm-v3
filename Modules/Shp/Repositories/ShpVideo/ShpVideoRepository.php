<?php

namespace Modules\Shp\Repositories\ShpVideo;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Shp\Entities\ShpVideo;
use FFMpeg;
use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg as LaravelFFMpeg;

class ShpVideoRepository implements ShpVideoInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $shpId = $queryData['shp_id'] ?? null;
        $productId = $queryData['product_id'] ?? null;

        $query = ShpVideo::with('product');

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
        $shpVideo = ShpVideo::with('product')->find($id);
        if (!$shpVideo) {
            return null;
        }
        return $shpVideo;
    }

    public function create(array $data)
    {
        if (isset($data['video_file'])) {
            $videoPath = $data['video_file']->store('videos', 'public');
            $data['video_url'] = Storage::url($videoPath);
        } else {
            return response()->json(['error' => 'No video file provided'], 400);
        }

        $absolutePath = Storage::path($videoPath);

        $ffmpeg = LaravelFFMpeg::fromDisk('public')->open($videoPath);
        $duration = $ffmpeg->getDurationInSeconds();

        $thumbnailPath = 'thumbnails/' . pathinfo($videoPath, PATHINFO_FILENAME) . '.jpg';
        $ffmpeg->getFrameFromSeconds(1)
            ->export()
            ->toDisk('public')
            ->save($thumbnailPath);

        $data['thumbnail_url'] = Storage::url($thumbnailPath);
        $data['duration'] = $duration;
        $data['created_by'] = Auth::id();

        $shpVideo = ShpVideo::create($data);

        return $shpVideo;
    }

    public function update($id, array $data)
    {
        $shpVideo = ShpVideo::find($id);

        if (!$shpVideo) {
            return null;
        }

        // Nếu có file video mới
        if (isset($data['video_file'])) {
            try {
                // Xóa video cũ nếu tồn tại
                if (!empty($shpVideo->video_url)) {
                    $oldVideoPath = str_replace(Storage::url(''), '', $shpVideo->video_url);
                    Storage::disk('public')->delete($oldVideoPath);
                }

                // Lưu video mới
                $videoPath = $data['video_file']->store('videos', 'public');
                $data['video_url'] = Storage::url($videoPath);

                // Mở video với FFMpeg
                $ffmpeg = LaravelFFMpeg::fromDisk('public')->open($videoPath);
                $duration = $ffmpeg->getDurationInSeconds();

                // Xóa thumbnail cũ nếu có
                if (!empty($shpVideo->thumbnail_url)) {
                    $oldThumbnailPath = str_replace(Storage::url(''), '', $shpVideo->thumbnail_url);
                    Storage::disk('public')->delete($oldThumbnailPath);
                }

                // Tạo thumbnail mới
                $thumbnailPath = 'thumbnails/' . pathinfo($videoPath, PATHINFO_FILENAME) . '.jpg';
                $ffmpeg->getFrameFromSeconds(1)
                    ->export()
                    ->toDisk('public')
                    ->save($thumbnailPath);

                $data['thumbnail_url'] = Storage::url($thumbnailPath);
                $data['duration'] = $duration;
            } catch (\Exception $e) {
                return response()->json(['error' => 'Lỗi xử lý video: ' . $e->getMessage()], 500);
            }
        }

        $data['updated_by'] = Auth::id();
        $shpVideo->update($data);

        return $shpVideo;
    }

    public function delete($id)
    {
        $shpVideo = ShpVideo::find($id);

        if (!$shpVideo) {
            return null;
        }

        if (!empty($shpVideo->video_url)) {
            $videoPath = str_replace(Storage::url(''), '', $shpVideo->video_url);
            Storage::disk('public')->delete($videoPath);
        }

        if (!empty($shpVideo->thumbnail_url)) {
            $thumbnailPath = str_replace(Storage::url(''), '', $shpVideo->thumbnail_url);
            Storage::disk('public')->delete($thumbnailPath);
        }

        $shpVideo->delete();

        return $shpVideo;
    }
}

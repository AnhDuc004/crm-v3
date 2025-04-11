<?php

namespace Modules\Tik\Repositories\TikFile;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Tik\Entities\TikFile;

class TikFileRepository implements TikFileInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;

        $search = isset($queryData['file_name']) ? $queryData['file_name'] : null;
        $query = TikFile::query();

        if ($search) {
            $query->where('file_name', 'like', '%' . $search . '%');
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $file = TikFile::find($id);
        if (!$file) {
            return null;
        }
        return $file;
    }

    public function create(array $data)
    {
        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            $file = $data['file'];
            $folder = 'uploads';

            // Lấy tên file gốc và loại file
            $originalName = $file->getClientOriginalName();
            $fileType = $file->getClientMimeType();

            // Tạo tên file mới để tránh trùng lặp
            $fileName = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newFileName = $fileName . '_' . time() . '.' . $extension;

            // Upload file vào storage
            $path = $file->storeAs($folder, $newFileName, 'public');

            $fileUrl = asset('storage/' . $path);

            // Tạo file_id unique
            $fileId = 'file_' . uniqid();

            // Tạo record trong database
            return TikFile::create([
                'file_id' => $fileId,
                'file_name' => $originalName,
                'file_type' => $fileType,
                'file_url' => $fileUrl,
                'created_by' => Auth::id(),
            ]);
        }

        return TikFile::create([
            'file_id' => $data['file_id'],
            'file_name' => $data['file_name'],
            'file_type' => $data['file_type'],
            'file_url' => $data['file_url'],
            'created_by' => Auth::id(),
        ]);
    }

    public function update($id, array $data)
    {
        Log::info('Bắt đầu update file với ID: ' . $id);
        Log::info('Data nhận vào:', $data);

        try {
            $tikFile = TikFile::findOrFail($id);
            Log::info('Tìm thấy file trong database:', $tikFile->toArray());

            if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
                Log::info('Phát hiện file mới được upload');
                $file = $data['file'];
                $folder = 'uploads';

                // Kiểm tra và xóa file cũ
                $oldFilePath = str_replace(asset('storage/'), '', $tikFile->file_url);
                Log::info('Đường dẫn file cũ: ' . $oldFilePath);

                if (Storage::disk('public')->exists($oldFilePath)) {
                    Log::info('File cũ tồn tại, tiến hành xóa');
                    Storage::disk('public')->delete($oldFilePath);
                    Log::info('Đã xóa file cũ thành công');
                } else {
                    Log::warning('Không tìm thấy file cũ trong storage');
                }

                // Xử lý file mới
                $originalName = $file->getClientOriginalName();
                $fileType = $file->getClientMimeType();
                Log::info('Thông tin file mới:', [
                    'original_name' => $originalName,
                    'file_type' => $fileType,
                    'file_size' => $file->getSize(),
                ]);

                // Tạo tên file mới
                $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $newFileName = $fileName . '_' . time() . '.' . $extension;
                Log::info('Tên file mới: ' . $newFileName);

                // Upload file mới
                $path = $file->storeAs($folder, $newFileName, 'public');
                Log::info('Đã upload file mới thành công. Path: ' . $path);

                $fileUrl = asset('storage/' . $path);
                Log::info('URL file mới: ' . $fileUrl);

                // Cập nhật database
                $updateData = [
                    'file_name' => $originalName,
                    'file_type' => $fileType,
                    'file_url' => $fileUrl,
                    'updated_by' => Auth::id(),
                ];
                Log::info('Dữ liệu cập nhật database:', $updateData);

                $tikFile->update($updateData);
                Log::info('Đã cập nhật database thành công');
            } else {
                Log::info('Không có file mới, chỉ cập nhật thông tin');

                $updateData = [
                    'file_name' => $data['file_name'] ?? $tikFile->file_name,
                    'file_type' => $data['file_type'] ?? $tikFile->file_type,
                    'file_url' => $data['file_url'] ?? $tikFile->file_url,
                    'updated_by' => Auth::id(),
                ];
                Log::info('Dữ liệu cập nhật database:', $updateData);

                $tikFile->update($updateData);
                Log::info('Đã cập nhật database thành công');
            }

            Log::info('Kết thúc update file thành công');
            return $tikFile->fresh();
        } catch (\Exception $e) {
            Log::error('Lỗi khi update file: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    public function delete($id)
    {
        $file = TikFile::findOrFail($id);
        // Lấy đường dẫn file từ URL
        $filePath = str_replace(asset('storage/'), '', $file->file_url);

        // Kiểm tra và xóa file trong storage
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        } else {
        }

        // Xóa record trong database
        $file->delete();

        return $file;
    }
}

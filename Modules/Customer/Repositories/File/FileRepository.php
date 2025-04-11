<?php

namespace Modules\Customer\Repositories\File;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Modules\Customer\Entities\File;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class FileRepository implements FileInterface
{
    use LogActivityTrait;

    public function findId($id)
    {
        $file = File::find($id);
        return $file;
    }

    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = File::leftJoin('customers', 'customers.id', '=', 'files.rel_id')->where('customers.id', '=', $id)->where('files.rel_type', '=', 'customer');

        if ($search) {
            $baseQuery = $baseQuery->where('files.file_name', 'like', '%' . $search . '%');
        }
        $file = $baseQuery->select('files.*');
        if ($limit > 0) {
            $file = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $file = $baseQuery->get();
        }

        return $file;
    }

    public function getListByLead($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 10;
        $baseQuery = File::where('rel_id', '=', $id)
            ->where('rel_type', '=', 'lead')
            ->where('staff_id', Auth::user()->id);
        if (!$baseQuery) {
            return null;
        }
        $file = $baseQuery->orderBy('id', 'desc');
        if ($limit > 0) {
            $file = $baseQuery->paginate($limit);
        } else {
            $file = $baseQuery->get();
        }

        return $file;
    }

    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $baseQuery = File::query();
        if ($limit > 0) {
            $file = $baseQuery->paginate($limit);
        } else {
            $file = $baseQuery->get();
        }

        return $file;
    }

    public function create($request)
    {
        $file = new File($request);
        $file->save();
        return $file;
    }

    public function update($id, $request)
    {
        $file = File::find($id);
        $file->fill($request);
        $file->save();
        return $file;
    }

    public function destroy($id)
    {
        $file = File::find($id);
        $file->delete();
        return $file;
    }

    public function uploadFileByLead($id, $request)
    {
        $file = new File($request);
        $file->dateadded = Carbon::now();
        $file->rel_id = $id;
        $file->rel_type = 'lead';
        $file->visible_to_customer = '0';
        $file->staff_id = Auth::user()->id;
        $file->task_comment_id = '0';
        $fileUpLoad = $request['file_name'];
        $fileName = $fileUpLoad->getClientOriginalName();
        $fileType = $fileUpLoad->getMimeType();
        $file->file_name = $fileName;
        $file->filetype = $fileType;
        $fileUpLoad->move('uploads/file', $fileName);
        $file->save();
        $this->createLeadActivity($id, ActivityKey::UPLOAD_FILE_BY_LEAD);
        return $file;
    }

    public function uploadFileByCustomer($id, $request)
    {
        $file = new File($request);
        $file->dateadded = Carbon::now();
        $file->rel_id = $id;
        $file->rel_type = 'customer';
        $file->visible_to_customer = '0';
        $file->staff_id = Auth::user()->id;
        $file->task_comment_id = '0';
        if (isset($request['file_name'])) {
            $fileUpLoad = $request['file_name'];
            $fileName = $fileUpLoad->getClientOriginalName();
            $fileType = $fileUpLoad->getMimeType();
            $file->file_name = $fileName;
            $file->filetype = $fileType;
            $fileUpLoad->move('uploads/file', $fileName);
            $file->save();
        }
        return $file;
    }
    public function changeVisibleToCustomer($id, $request)
    {
        $visible = $request['visible_to_customer'];
        $file = File::find($id);
        $file->visible_to_customer = $visible;
        $file->save();
        return $file;
    }
    public function getListByContract($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = File::where('rel_id', '=', $id)->where('rel_type', '=', 'contract');

        if ($search) {
            $baseQuery = $baseQuery->where('files.file_name', 'like', '%' . $search . '%');
        }
        $file = $baseQuery->select('files.*');
        if ($limit > 0) {
            $file = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $file = $baseQuery->get();
        }
        return $file;
    }
    public function uploadFileByContract($id, $request)
    {
        $file = new File();
        $file->rel_id = $id;
        $file->rel_type = 'contract';
        $file->visible_to_customer = 0;
        $file->staff_id = Auth::user()->id;
        $file->task_comment_id = 0;
        $fileUpLoad = $request['file_name'];
        $fileName = $fileUpLoad->getClientOriginalName();
        $fileType = $fileUpLoad->getMimeType();
        $file->file_name = $fileName;
        $file->file_type = $fileType;
        $fileUpLoad->move('uploads/file', $fileName);
        $file->save();
        return $file;
    }

    public function uploadFileByProposal($id, $request)
    {
        $file = new File($request);
        $file->dateadded = Carbon::now();
        $file->rel_id = $id;
        $file->rel_type = 'proposal';
        $file->visible_to_customer = '0';
        $file->staff_id = Auth::user()->id;
        $file->task_comment_id = '0';
        $fileUpLoad = $request['file_name'];
        $fileName = $fileUpLoad->getClientOriginalName();
        $fileType = $fileUpLoad->getMimeType();
        $file->file_name = $fileName;
        $file->filetype = $fileType;
        $fileUpLoad->move('uploads/file', $fileName);
        $file->save();
        return $file;
    }

    public function getListByProposal($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = File::where('rel_id', '=', $id)->where('rel_type', '=', 'proposal');


        if ($search) {
            $baseQuery = $baseQuery->where('files.file_name', 'like', '%' . $search . '%');
        }
        $file = $baseQuery->select('files.*');
        if ($limit > 0) {
            $file = $baseQuery->paginate($limit);
        } else {
            $file = $baseQuery->get();
        }

        return $file;
    }

    public function uploadFileByEstimate($id, $request)
    {
        $file = new File($request);
        $file->dateadded = Carbon::now();
        $file->rel_id = $id;
        $file->rel_type = 'estimate';
        $file->visible_to_customer = '0';
        $file->staff_id = Auth::user()->id;
        $file->task_comment_id = '0';
        $fileUpLoad = $request['file_name'];
        $fileName = $fileUpLoad->getClientOriginalName();
        $fileType = $fileUpLoad->getMimeType();
        $file->file_name = $fileName;
        $file->filetype = $fileType;
        $fileUpLoad->move('uploads/file', $fileName);
        $file->save();
        return $file;
    }

    public function getListByEstimate($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = File::where('rel_id', '=', $id)->where('rel_type', '=', 'estimate');

        if ($search) {
            $baseQuery = $baseQuery->where('files.file_name', 'like', '%' . $search . '%');
        }
        $file = $baseQuery->select('files.*');
        if ($limit > 0) {
            $file = $baseQuery->paginate($limit);
        } else {
            $file = $baseQuery->get();
        }

        return $file;
    }

    public function uploadFileByInvoice($id, $request)
    {
        $file = new File($request);
        $file->dateadded = Carbon::now();
        $file->rel_id = $id;
        $file->rel_type = 'invoice';
        $file->visible_to_customer = '0';
        $file->staff_id = Auth::user()->id;
        $file->task_comment_id = '0';
        $fileUpLoad = $request['file_name'];
        $fileName = $fileUpLoad->getClientOriginalName();
        $fileType = $fileUpLoad->getMimeType();
        $file->file_name = $fileName;
        $file->filetype = $fileType;
        $fileUpLoad->move('uploads/file', $fileName);
        $file->save();
        return $file;
    }

    public function getListByInvoice($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = File::where('rel_id', '=', $id)->where('rel_type', '=', 'invoice');

        if ($search) {
            $baseQuery = $baseQuery->where('files.file_name', 'like', '%' . $search . '%');
        }
        $file = $baseQuery->select('files.*');
        if ($limit > 0) {
            $file = $baseQuery->paginate($limit);
        } else {
            $file = $baseQuery->get();
        }

        return $file;
    }

    public function uploadFileByTask($id, $request)
    {
        $oldFiles = File::where('rel_id', $id)->where('rel_type', 'task')->get();

        foreach ($oldFiles as $oldFile) {
            $filePath = public_path('uploads/file/' . $oldFile->file_name);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $oldFile->delete();
        }

        if (isset($request['file_name']) && is_array($request['file_name'])) {
            foreach ($request['file_name'] as $fileUpLoad) {
                if ($fileUpLoad instanceof UploadedFile) {
                    $fileName = time() . '_' . $fileUpLoad->getClientOriginalName();
                    $fileType = $fileUpLoad->getMimeType();
                    $fileUpLoad->move(public_path('uploads/file'), $fileName);
                    $file = new File();
                    $file->rel_id = $id;
                    $file->rel_type = 'task';
                    $file->visible_to_customer = '0';
                    $file->staff_id = Auth::id();
                    $file->task_comment_id = '0';
                    $file->file_name = $fileName;
                    $file->file_type = $fileType;
                    $file->created_by = Auth::id();
                    $file->save();
                }
            }
        }
        return $file;
    }

    public function getListByTask($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = File::where('rel_id', '=', $id)->where('rel_type', '=', 'task')->where('staff_id', Auth::id());

        if ($search) {
            $baseQuery = $baseQuery->where('files.file_name', 'like', '%' . $search . '%');
        }
        $file = $baseQuery->select('files.*');
        if ($limit > 0) {
            $file = $baseQuery->paginate($limit);
        } else {
            $file = $baseQuery->get();
        }
        return $file;
    }
}

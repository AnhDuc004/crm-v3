<?php

namespace Modules\Customer\Repositories\Notes;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Modules\Customer\Entities\Note;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\Customer;
use Modules\Support\Entities\Ticket;

class NoteRepository implements NoteInterface
{
    use LogActivityTrait;

    // List note theo id
    public function findId($id)
    {
        $note = Note::find($id);
        return $note;
    }

    // List note theo customer
    public function getListByCustomer($id,  $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 10;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $search = isset($request['search']) ? $request['search'] : null;

        $baseQuery = Note::where('rel_id', '=', $id)
            ->where('rel_type', '=', 'customer');

        if ($search) {
            $baseQuery = $baseQuery->where('notes.description', 'like', '%' . $search . '%');
        }

        $note = $baseQuery->with('customer')
            ->select('notes.*')
            ->orderBy('notes.created_at', 'desc');

        if ($limit > 0) {
            $note = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $note = $baseQuery->get();
        }
        return $note;
    }

    // List note
    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $startDate = isset($request['startDate']) ? $request['startDate'] : null;
        $endDate = isset($request['endDate']) ? $request['endDate'] : null;
        $orderName = isset($request['orderName']) ? $request['orderName'] : 'id';
        $orderType = isset($request['orderType']) ? $request['orderType'] : 'desc';
        $baseQuery = Note::query();
        if (!empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        if (!empty($startDate) && empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('created_at', '>=', $startDate);
        }
        if (empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('created_at', '<=', $endDate);
        }
        $note = $baseQuery->orderBy($orderName, $orderType);
        if ($limit > 0) {
            $note = $baseQuery->paginate($limit);
        } else {
            $note = $baseQuery->get();
        }
        return $note;
    }

    // Thêm mới note theo customer
    public function createByCustomer($id, $request)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        $note = new Note($request);
        $note->rel_id = $id;
        $note->rel_type = 'customer';
        $note->created_by = Auth::id();
        $note->save();
        return $note;
    }

    // Cập nhật note
    public function update($id, $request)
    {
        $note = Note::find($id);
        $note->fill($request);
        if ($note->rel_type == 'lead') {
            $this->createLeadActivity($note->rel_id, ActivityKey::UPDATE_NOTE_BY_LEAD);
        }
        $note->updated_by = Auth::id();
        $note->save();
        $data = Note::where('id', $note->id)->get();
        return $data;
    }

    // Xóa note
    public function destroy($id)
    {
        $note = Note::find($id);
        if ($note->rel_type == 'lead') {
            $this->createLeadActivity($note->rel_id, ActivityKey::DELETE_NOTE_BY_LEAD);
        }
        $note->delete();
        return $note;
    }

    // get note theo estimaste
    public function getByEstimaste($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $baseQuery = Note::query();
        $baseQuery = $baseQuery
            ->where('rel_type', 'estimate')
            ->where('rel_id', $id)
            ->where('created_by', Auth::user()->id);
        $note = $baseQuery->with('estimate')->orderBy('id', 'desc');
        if ($limit > 0) {
            $note = $baseQuery->paginate($limit);
        } else {
            $note = $baseQuery->get();
        }
        return $note;
    }

    // create note theo estimaste
    public function createByEstimaste($id, $request)
    {
        $note = new Note($request);
        $note->rel_id = $id;
        $note->rel_type = 'estimate';
        $note->created_by = Auth::id();
        $note->save();
        return $note;
    }

    //get note theo lead
    public function getByLead($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 10;

        $baseQuery = Note::query();
        $baseQuery->where('rel_type', 'lead')->where('rel_id', $id)->with('lead:name')->orderBy('id', 'desc');

        if ($limit > 0) {
            $note = $baseQuery->paginate($limit);
        } else {
            $note = $baseQuery->get();
        }
        return $note;
    }

    // create note theo lead
    public function createByLead($id, $request)
    {
        $note = new Note($request);
        $note->rel_id = $id;
        $note->rel_type = 'lead';
        $note->created_by = Auth::id();
        $note->save();
        $this->createLeadActivity($id, ActivityKey::CREATE_NOTE_BY_LEAD);
        return $note;
    }

    // List note theo contract
    public function getListByContract($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Note::where('rel_id', '=', $id)->where('rel_type', '=', 'contract');
        if ($search) {
            $baseQuery = $baseQuery->where('notes.description', 'like', '%' . $search . '%');
        }
        $note = $baseQuery->with('contract')->select('notes.*')->orderBy('notes.created_at', 'desc');
        if ($limit > 0) {
            $note = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $note = $baseQuery->get();
        }
        return $note;
    }

    // Thêm mới note theo contract
    public function createByContract($id, $request)
    {
        $note = new Note();
        $note->rel_id = $id;
        $note->rel_type = 'contract';
        $note->description = $request['description'];
        $note->date_contacted = Carbon::now();
        $note->created_by = Auth::id();
        $note->save();
        return $note;
    }

    // List note theo ticket
    public function getListByTicket($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Note::where('rel_id', '=', $id)->where('rel_type', '=', 'ticket')->where('created_by', Auth::id());
        if ($search) {
            $baseQuery = $baseQuery->where('notes.description', 'like', '%' . $search . '%');
        }
        $note = $baseQuery->select('notes.*')->orderBy('notes.created_at', 'desc');
        if ($limit > 0) {
            $note = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $note = $baseQuery->get();
        }
        return $note;
    }
    // Thêm mới note theo ticket
    public function createByTicket($id, $request)
    {
        $ticket = Ticket::where('id', '=', $id)->first();

        $note = new Note($request);
        $note->rel_id = $id;
        $note->rel_type = 'ticket';
        $note->created_by = Auth::id();
        $note->save();
        return $note;
    }

    //get Note theo Proposal
    public function getByProposal($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Note::query();
        $baseQuery = $baseQuery->where('rel_type', 'proposal')->where('rel_id', $id)->where('created_by', Auth::id());
        if ($search) {
            $baseQuery = $baseQuery->where('description', 'like', '%' . $search . '%');
        }
        $note = $baseQuery->orderBy('id', 'desc');
        if ($limit > 0) {
            $note = $baseQuery->paginate($limit);
        } else {
            $note = $baseQuery->get();
        }
        return $note;
    }

    // create note theo Proposal
    public function createByProposal($id, $request)
    {
        $note = new Note($request);
        $note->rel_id = $id;
        $note->rel_type = 'proposal';
        $note->created_by = Auth::id();
        $note->save();
        return $note;
    }

    // get Note theo Invoice
    public function getByInvoice($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Note::query();
        $baseQuery = $baseQuery->where('rel_type', 'invoice')->where('rel_id', $id)->where('created_by', Auth::id());
        if ($search) {
            $baseQuery = $baseQuery->where('description', 'like', '%' . $search . '%');
        }
        $note = $baseQuery->orderBy('id', 'desc');
        if ($limit > 0) {
            $note = $baseQuery->paginate($limit);
        } else {
            $note = $baseQuery->get();
        }
        return $note;
    }

    // create Note theo Invoice
    public function createByInvoice($id, $request)
    {
        $note = new Note($request);
        $note->rel_id = $id;
        $note->rel_type = 'invoice';
        $note->created_by = Auth::id();
        $note->save();
        return $note;
    }

    // get Note theo Invoice
    public function getByStaff($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 10;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Note::query();
        $baseQuery = $baseQuery->where('rel_type', 'staff')->where('rel_id', $id)->where('created_by', Auth::id());
        if ($search) {
            $baseQuery = $baseQuery->where('description', 'like', '%' . $search . '%');
        }
        $note = $baseQuery->with('staff')->orderBy('id', 'desc');
        if ($limit > 0) {
            $note = $baseQuery->paginate($limit);
        } elseif (!$note) {
            return null;
        } else {
            $note = $baseQuery->get();
        }
        return $note;
    }

    // create Note theo Invoice
    public function createByStaff($id, $request)
    {
        $note = new Note($request);
        $note->rel_id = $id;
        $note->rel_type = 'staff';
        $note->created_by = Auth::id();
        $note->save();
        return $note;
    }
}

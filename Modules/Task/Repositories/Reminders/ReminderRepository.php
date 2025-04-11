<?php

namespace Modules\Task\Repositories\Reminders;

use Modules\Task\Entities\Reminder;
use App\Helpers\Result;
use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Entities\Customer;
use Modules\Task\Entities\Task;

class ReminderRepository implements ReminderInterface
{
    use LogActivityTrait;
    const errorMess = 'Nhắc nhở không tồn tại';
    const errorCreateMess = "Thêm mới nhắc nhở thất bại";
    const errorUpdateMess = "Cập nhật nhắc nhở thất bại";
    const errorDeleteMess = "Xóa nhắc nhở thất bại";
    const errorPaymentMess = "Không thể thanh toán nhắc nhở";
    const successDeleteMess = "Xoá lời nhắc thành công";

    public function findId($id)
    {
        $reminder = Reminder::find($id);
        $reminder = $reminder->with('customer', 'staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc')->get();

        if (!$reminder) {
            return Result::fail(self::errorMess);
        }

        return Result::success($reminder);
    }

    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Reminder::leftJoin('customers', 'customers.id', '=', 'reminders.rel_id')
            ->where('customers.id', '=', $id)
            ->where('reminders.rel_type', '=', 'customer');

        if (!$baseQuery) {
            return Result::fail(static::errorMess);
        }
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('reminders.description', 'like',  '%' . $search . '%')
                        ->orWhere('reminders.date', 'like',  '%' . $search . '%');
                }
            );
        }
        $reminder = $baseQuery->with('customer', 'staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc');
        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }

    public function getListByExpense($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Reminder::where('rel_id', '=', $id)
            ->where('rel_type', '=', 'expense');

        if (!$baseQuery) {
            return Result::fail(static::errorMess);
        }
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('reminders.description', 'like',  '%' . $search . '%')
                        ->orWhere('reminders.date', 'like',  '%' . $search . '%');
                }
            );
        }
        $reminder = $baseQuery->with('customer', 'staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc')->get();
        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }

    public function getListByLead($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Reminder::where('rel_id', '=', $id)
            ->where('rel_type', '=', 'lead');

        if (!$baseQuery) {
            return Result::fail(static::errorMess);
        }
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('reminders.description', 'like',  '%' . $search . '%')
                        ->orWhere('reminders.date', 'like',  '%' . $search . '%');
                }
            );
        }
        $reminder = $baseQuery->with('customer', 'staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc')->get();
        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }

    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $startDate = isset($request['startDate']) ? $request['startDate'] : null;
        $endDate = isset($request['endDate']) ? $request['endDate'] : null;
        $orderName = isset($request['orderName']) ? $request['orderName'] : 'id';
        $orderType = isset($request['orderType']) ? $request['orderType'] : 'desc';

        $description = isset($request['description']) ? $request['description'] : null;
        $date = isset($request['date']) ? $request['date'] : null;
        $creator = isset($request['creator']) ? $request['creator'] : null;

        $baseQuery = Reminder::query();

        if (!empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereBetween('date', [$startDate, $endDate]);
        }

        if (!empty($startDate) && empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('date', '>=', $startDate);
        }

        if (empty($startDate) && !empty($endDate)) {
            $baseQuery = $baseQuery->whereDate('date', '<=', $endDate);
        }

        if ($description) {
            $baseQuery = $baseQuery->where('description', $description);
        }

        if ($date) {
            $baseQuery = $baseQuery->where('date', $date);
        }

        if ($creator) {
            $baseQuery = $baseQuery->where('creator', $creator);
        }
        $reminder = $baseQuery->orderBy($orderName, $orderType);

        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }

    public function listSelect() {}

    public function createByCustomer($id, $request)
    {
        $reminder = new Reminder($request);
        $reminder->rel_id = $id;
        $reminder->rel_type = 'customer';
        $reminder->created_by = Auth::id();
        $reminder->save();
        $reminder->with('customer', 'staffs')->get();
        return $reminder;
    }

    public function createByLead($id, $request)
    {
        $reminder = new Reminder($request);
        $reminder->rel_id = $id;
        $reminder->rel_type = 'lead';
        $reminder->created_by = Auth::id();
        $reminder->save();
        $this->createLeadActivity($id, ActivityKey::CREATE_REMINDER_BY_LEAD);
        $reminder->with('staffs')->get();
        return $reminder;
    }

    public function createByExpense($id, $request)
    {
        try {
            $reminder = Reminder::where('rel_id', '=', $id)->where('rel_type', '=', 'expense');
            $reminder = new Reminder($request);
            $reminder->rel_id = $id;
            $reminder->rel_type = 'expense';
            $reminder->created_by = Auth::user()->id;
            $reminder->save();
            $data = Reminder::where('id', $reminder->id)->get();
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function update($id, $request)
    {
        try {
            $reminder = Reminder::find($id);

            if (!$reminder) {
                return Result::fail(self::errorMess);
            }
            $reminder->fill($request);
            $reminder->updated_by = Auth::user()->id;
            $reminder->save();
            if ($reminder->rel_type == 'lead') {
                $this->createLeadActivity($reminder->rel_id, ActivityKey::UPDATE_REMINDER_BY_LEAD);
            }
            $task = Task::where('id', $reminder->rel_id)->first();
            if ($task->rel_type == 'project') {
                $this->createProjectActivity($task->rel_id, ActivityKey::UPDATE_REMINDER_BY_TASK_IN_PROJECT);
            }
            if ($task->rel_type == 'lead') {
                $this->createLeadActivity($task->rel_id, ActivityKey::UPDATE_REMINDER_BY_LEAD);
            }
            $data = Reminder::where('id', $reminder->id)->with('customer', 'staffs')->get();
            return Result::success($data);
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return Result::fail(static::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        try {
            $reminder = Reminder::find($id);

            if (!$reminder) {
                return Result::fail(self::errorMess);
            }

            $task = Task::where('id', $reminder->rel_id)->first();
            if ($reminder->rel_type == 'lead') {
                $this->createLeadActivity($reminder->rel_id, ActivityKey::DELETE_REMINDER_BY_LEAD);
            }
            if ($task->rel_type == 'project') {
                $this->createProjectActivity($task->rel_id, ActivityKey::DELETE_REMINDER_BY_TASK_IN_PROJECT);
            }
            if ($task->rel_type == 'lead') {
                $this->createLeadActivity($task->rel_id, ActivityKey::DELETE_REMINDER_BY_TASK_IN_LEAD);
            }
            $reminder->delete();

            return Result::success(static::successDeleteMess);
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return Result::fail(static::errorDeleteMess);
        }
    }

    public function getByEstimaste($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Reminder::query();
        $baseQuery = $baseQuery->where('rel_type', 'estimate')
            ->where('rel_id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('description', 'like', '%' . $search . '%')
                ->orWhere('date', 'like', '%' . $search . '%');
        }
        $reminder = $baseQuery->with('customer', 'staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc')->get();

        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }

    public function getListByTicket($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Reminder::query();
        $baseQuery = $baseQuery->where('rel_type', 'ticket')
            ->where('rel_id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('description', 'like', '%' . $search . '%')
                ->orWhere('date', 'like', '%' . $search . '%');
        }
        $reminder = $baseQuery->with('customer', 'staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc')->get();

        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }

    public function createByTicket($id, $request)
    {
        try {
            $reminder = new Reminder($request);
            $reminder->rel_id = $id;
            $reminder->rel_type = 'ticket';
            $reminder->created_by = Auth::user()->id;
            $reminder->save();
            $data = Reminder::where('id', $reminder->id)->with('staffs')->get();
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }
    public function getListByProposal($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Reminder::query();
        $baseQuery = $baseQuery->where('rel_type', 'proposal')
            ->where('rel_id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('description', 'like', '%' . $search . '%')
                ->orWhere('date', 'like', '%' . $search . '%');
        }
        $reminder = $baseQuery->with('customer', 'staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc')->get();

        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }

    public function createByProposal($id, $request)
    {
        try {
            $reminder = new Reminder($request);
            $reminder->rel_id = $id;
            $reminder->rel_type = 'proposal';
            $reminder->created_by = Auth::user()->id;
            $reminder->save();
            $data = Reminder::where('id', $reminder->id)->with('staffs')->get();
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function createByEstimate($id, $request)
    {
        try {
            $reminder = new Reminder($request);
            $reminder->rel_id = $id;
            $reminder->rel_type = 'estimate';
            $reminder->created_by = Auth::user()->id;
            $reminder->save();
            $data = Reminder::where('id', $reminder->id)->with('staffs')->get();
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function getListByInvoice($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Reminder::query();
        $baseQuery = $baseQuery->where('rel_type', 'invoice')
            ->where('rel_id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('description', 'like', '%' . $search . '%')
                ->orWhere('date', 'like', '%' . $search . '%');
        }
        $reminder = $baseQuery->with('customer', 'staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc')->get();

        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }

    public function createByInvoice($id, $request)
    {
        try {
            $reminder = new Reminder($request);
            $reminder->rel_id = $id;
            $reminder->rel_type = 'invoice';
            $reminder->created_by = Auth::user()->id;
            $reminder->save();
            $data = Reminder::where('id', $reminder->id)->with('staffs')->get();
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function createByTask($id, $request)
    {
        $reminder = new Reminder($request);
        $reminder->rel_id = $id;
        $reminder->rel_type = 'task';
        $reminder->created_by = Auth::user()->id;
        $reminder->save();
        $task = Task::find($id);
        if ($task->rel_type == 'project') {
            $this->createProjectActivity($task->rel_id, ActivityKey::CREATE_REMINDER_BY_TASK_IN_PROJECT);
        }
        if ($task->rel_type == 'lead') {
            $this->createLeadActivity($task->rel_id, ActivityKey::CREATE_REMINDER_BY_TASK_IN_LEAD);
        }
        $data = Reminder::where('id', $reminder->id)->with('staffs')->get();
        return $data;
    }

    public function getListByTask($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Reminder::query();
        $baseQuery = $baseQuery->where('rel_type', 'task')
            ->where('rel_id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('description', 'like', '%' . $search . '%')
                ->orWhere('date', 'like', '%' . $search . '%');
        }
        $reminder = $baseQuery->with('staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc')->get();

        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }

    public function createByCreditNote($id, $request)
    {
        try {
            $reminder = new Reminder($request);
            $reminder->rel_id = $id;
            $reminder->rel_type = 'creditnotes';
            $reminder->created_by = Auth::user()->id;
            $reminder->save();
            $data = Reminder::where('id', $reminder->id)->with('staffs')->get();
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function getListByCreditNote($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Reminder::query();
        $baseQuery = $baseQuery->where('rel_type', 'creditnotes')
            ->where('rel_id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('description', 'like', '%' . $search . '%')
                ->orWhere('date', 'like', '%' . $search . '%');
        }
        $reminder = $baseQuery->with('staffs')->select('reminders.*')->orderBy('reminders.created_at', 'desc')->get();

        if ($limit > 0) {
            $reminder = $baseQuery->paginate($limit);
        } else {
            $reminder = $baseQuery->get();
        }

        return Result::success($reminder);
    }
}

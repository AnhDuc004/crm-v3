<?php

namespace Modules\Support\Repositories\TicketsPriority;

use App\Helpers\Result;
use Illuminate\Support\Facades\Log;
use Modules\Support\Entities\TicketsPriorities;

class TicketsPriorityRepository implements TicketsPriorityInterface
{
    public function findId($id)
    {
        $ticketsPriority = TicketsPriorities::find($id);
        if (!$ticketsPriority) {
            return null;
        }
        return $ticketsPriority;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = TicketsPriorities::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $ticketsPriority = $baseQuery->paginate($limit);
        } else {
            $ticketsPriority = $baseQuery->get();
        }

        return $ticketsPriority;
    }

    public function listSelect() {}

    public function create($request)
    {
        $ticketsPriority = new TicketsPriorities($request);
        $ticketsPriority->save();
        return $ticketsPriority;
    }

    public function update($id, $request)
    {
        $ticketsPriority = TicketsPriorities::find($id);
        if (!$ticketsPriority) {
            return null;
        }
        $ticketsPriority->fill($request);
        $ticketsPriority->save();
        return $ticketsPriority;
    }

    public function destroy($id)
    {
        $ticketsPriority = TicketsPriorities::find($id);
        if (!$ticketsPriority) {
            return null;
        }
        $ticketsPriority->delete();
        return $ticketsPriority;
    }
}

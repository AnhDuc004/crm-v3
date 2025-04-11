<?php

namespace Modules\Support\Repositories\TicketsStatus;

use App\Helpers\Result;
use Illuminate\Support\Facades\Log;
use Modules\Support\Entities\TicketsStatus;

class TicketsStatusRepository implements TicketsStatusInterface
{
    public function findId($id)
    {
        $ticketsStatus = TicketsStatus::find($id);
        if (!$ticketsStatus) {
            return null;
        }
        return $ticketsStatus;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = TicketsStatus::query()->leftJoin('tickets', 'tickets.status', '=', 'tickets_status.ticketstatusid')
            ->select('tickets_status.ticketstatusid', 'tickets_status.name')
            ->selectRaw('COUNT(tickets.status) as statusCount')
            ->groupBy('tickets_status.ticketstatusid', 'tickets_status.name');
        if ($search) {
            $baseQuery = $baseQuery->where('tickets_status.name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $ticketsStatus = $baseQuery->paginate($limit);
        } else {
            $ticketsStatus = $baseQuery->get();
        }

        return $ticketsStatus;
    }

    public function listSelect() {}

    public function create($request)
    {
        $ticketsStatus = new TicketsStatus($request);
        $ticketsStatus->save();
        return Result::success($ticketsStatus);
    }

    public function update($id, $request)
    {
        $ticketsStatus = TicketsStatus::find($id);
        if (!$ticketsStatus) {
            return null;
        }
        $ticketsStatus->fill($request);
        $ticketsStatus->save();
        return $ticketsStatus;
    }

    public function destroy($id)
    {
        $ticketsStatus = TicketsStatus::find($id);
        if (!$ticketsStatus) {
            return null;
        }
        $ticketsStatus->delete();
        return $ticketsStatus;
    }
}

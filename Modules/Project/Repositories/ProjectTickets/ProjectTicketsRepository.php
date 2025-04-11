<?php

namespace Modules\Project\Repositories\ProjectTickets;
use Modules\Project\Entities\ProjectTickets;
use Modules\Customer\Entities\Tag;
use Modules\Support\Entities\TicketsStatus;

class ProjectTicketsRepository implements ProjectTicketsInterface
{
    public function listAll($request)
    {

        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $baseQuery = ProjectTickets::query();
        $search = isset($request["search"]) ? $request["search"] : null;
        $projectTickets = $baseQuery->with('project', 'tags')->orderBy('ticketid', 'desc');

        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like', '%' . $search . '%');
        }
        if ($limit > 0) {
            $projectTickets = $baseQuery->paginate($limit);
        } else {
            $projectTickets = $baseQuery->get();
        }

        return $projectTickets;
    }
    public function create($id, $request)
    {
        $project_tickets =  new ProjectTickets($request);
        $project_tickets->project_id = $id;
        $project_tickets->save();
        if (isset($request['tag'])) {
            foreach ($request['tag'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $project_tickets->tags()->attach($tag->id, ['rel_type' => $tag['rel_type'], 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $project_tickets->tags()->attach($tg->id, ['rel_type' => 'tickets', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        $data = ProjectTickets::where('ticketid', $project_tickets->ticketid)->with('project', 'tags')->first();
        return $data;
    }

    public function destroy($id)
    {
        $projectTickets = ProjectTickets::find($id);
        if (!$projectTickets) {
            return null;
        }
        $result = $projectTickets->delete();
        return $result;
    }

    public function listByProject($project_id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $status = isset($request["status"]) ? $request["status"] : null;
        $baseQuery  = ProjectTickets::leftJoin('projects', 'tickets.project_id', '=', 'projects.id')
            ->leftJoin('departments', 'tickets.department', '=', 'departments.departmentid')
            ->leftJoin('contacts', 'tickets.contactid', '=', 'contacts.id')
            ->where('projects.id', $project_id)
            ->with('project.customer:clientId,company', 'tags', 'departments:departmentid,name', 'contact.customer:clientId,company')
            ->select('tickets.*');

        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('tickets.subject', 'like',  '%' . $search . '%')
                        ->orWhere('departments.name', 'like',  '%' . $search . '%')
                        ->orWhere('contacts.firstname', 'like',  '%' . $search . '%')
                        ->orWhere('contacts.lastname', 'like',  '%' . $search . '%');
                }
            );
        }
        if ($status) {
            $baseQuery = $baseQuery->where('tickets.status', 'like',  '%' . $status . '%');
        }
        if ($limit > 0) {
            $project_tickets = $baseQuery->paginate($limit);
        } else {
            $project_tickets = $baseQuery->get();
        }
        return $project_tickets;
    }

    public function update($id, $request)
    {
        $projectTickets = ProjectTickets::find($id);
        if (!$projectTickets) {
            return null;
        }

        $projectTickets->fill($request);
        $projectTickets->save();

        return $projectTickets;
    }

    public function countByStatus($id)
    {
        $status = TicketsStatus::leftJoin('tickets', 'tickets.ticketid', '=', 'tickets_status.ticketstatusid')
            ->where('tickets.project_id', '=', $id)
            ->select('tickets_status.name')
            ->selectRaw('COUNT(tickets.status) as statusCount')
            ->groupBy('tickets_status.name')->get();
        return $status;
    }
}

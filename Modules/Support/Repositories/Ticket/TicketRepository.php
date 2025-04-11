<?php

namespace Modules\Support\Repositories\Ticket;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\Contact;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Tag;
use Modules\Support\Entities\Ticket;
use Modules\Support\Entities\TicketsAttachments;
use Modules\Support\Entities\TicketsStatus;

class TicketRepository implements TicketInterface
{
    // List ticket theo id
    public function findId($id)
    {
        $ticket = Ticket::with(
            'contact:id,first_name,last_name',
            'tags',
            'departments:departmentid,name',
            'staff:id,first_name,last_name',
            'project:id,name',
            'file',
            'services',
            'ticketPriority'
        )->find($id);
        if (!$ticket) {
            return null;
        }
        return $ticket;
    }

    // List ticket theo customer
    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Ticket::leftJoin('contacts', 'contacts.id', '=', 'tickets.contactid')
            ->leftJoin('customers', 'customers.id', '=', 'contacts.customerId')
            ->leftJoin('departments', 'departments.departmentid', '=', 'tickets.department')
            ->where('customers.id', '=', $id);
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('tickets.subject', 'like',  '%' . $search . '%')
                        ->orWhere('contacts.firstname', 'like',  '%' . $search . '%')
                        ->orWhere('contacts.lastname', 'like',  '%' . $search . '%')
                        ->orWhere('contracts_types.name', 'like',  '%' . $search . '%')
                        ->orWhere('departments.name', 'like',  '%' . $search . '%');
                }
            );
        }
        $ticket = $baseQuery->with(
            'contact:id,firstname,lastname',
            'tags',
            'departments:departmentid,name',
            'staff:staffId,firstname,lastname',
            'project:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues',
            'file',
            'services'
        )->select('tickets.*')->orderBy('tickets.date', 'desc');
        if ($limit > 0) {
            $ticket = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $ticket = $baseQuery->get();
        }

        return $ticket;
    }

    // List ticket
    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Ticket::query();
        if (!$baseQuery) {
            return null;
        }
        if ($search) {
            $baseQuery = $baseQuery->leftJoin('contacts', 'contacts.id', '=', 'tickets.contactid')
                ->leftJoin('departments', 'departments.departmentid', '=', 'tickets.department')
                ->leftJoin('services', 'services.serviceid', '=', 'tickets.service')->where(
                    function ($q) use ($search) {
                        $q->where('tickets.subject', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.first_name', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.last_name', 'like',  '%' . $search . '%')
                            ->orWhere('departments.name', 'like',  '%' . $search . '%')
                            ->orWhere('services.name', 'like',  '%' . $search . '%');
                    }
                );
        }
        $ticket = $baseQuery->with(
            'contact:id,first_name,last_name',
            'tags',
            'departments:departmentid,name',
            'staff:id,first_name,last_name',
            'project:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues',
            'file',
            'services'
        )->select('tickets.*')->orderBy('tickets.date', 'desc');
        if ($limit > 0) {
            $ticket = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $ticket = $baseQuery->get();
        }

        return $ticket;
    }

    // Thêm mới ticket theo customer
    public function createByCustomer($id, $request)
    {
        $contact = Contact::where('customer_id', $id)->first();
        if (!$contact) {
            return null;
        }
        $ticket =  new Ticket($request);
        $ticket->date = Carbon::now();
        $ticket->contactid = $contact->id;
        $ticket->save();
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $ticket->tags()->attach($tag['id'], ['rel_type' => 'ticket', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name',  $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $ticket->tags()->attach($tg->id, ['rel_type' => 'ticket', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $ticket->id;
                $customFields->field_to = "tickets";
                $customFields->save();
            }
        }
        if (isset($request['file_name'])) {
            foreach ($request['file_name'] as $fileUpLoad) {
                $attachment =  new TicketsAttachments($request);
                $attachment->ticket_id = $ticket->id;
                $fileName = $fileUpLoad->getClientOriginalName();
                $fileType = $fileUpLoad->getMimeType();
                $attachment->file_name = $fileName;
                $attachment->file_type = $fileType;
                $fileUpLoad->move('uploads/ticket', $fileName);
                $attachment->save();
            }
        }
        $data = Ticket::where('id', $ticket->id)->with(
            'contact:id,firstname,lastname',
            'tags',
            'departments:departmentid,name',
            'staff:staffId,firstname,lastname',
            'project:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        return $data;
    }

    // Cập nhật ticket
    public function update($id, $request)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return null;
        }
        $ticket->fill($request);
        $ticket->save();
        $ticket->taggable()->delete();
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $ticket->tags()->attach($tag['id'], ['rel_type' => 'ticket', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name',  $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $ticket->tags()->attach($tg->id, ['rel_type' => 'ticket', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findorNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id =  $ticket->id;
                $customFieldsValues->field_to = "tickets";
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id',  $ticket->id)->whereNotIn('id', $customFields)->delete();
        }
        if (isset($request['file_name'])) {
            foreach ($request['file_name'] as $fileUpLoad) {
                $attachment =  new TicketsAttachments($request);
                $attachment->ticket_id = $ticket->id;
                $fileName = $fileUpLoad->getClientOriginalName();
                $fileType = $fileUpLoad->getMimeType();
                $attachment->file_name = $fileName;
                $attachment->file_type = $fileType;
                $fileUpLoad->move('uploads/ticket', $fileName);
                $attachment->save();
            }
        }
        $data = Ticket::where('id', $ticket->id)->with('contact', 'tags', 'departments', 'staff', 'project')->get();
        return $data;
    }

    // Xóa ticket
    public function destroy($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return null;
        }
        $ticket->delete();
        return $ticket;
    }

    // Đếm số lượng ticket
    public function count()
    {
        $status = TicketsStatus::leftJoin('tickets', 'tickets_status.ticketstatusid', '=', 'tickets.status')
            ->select('tickets_status.ticketstatusid', 'tickets_status.name')
            ->selectRaw('COUNT(tickets.status) as count')
            ->groupBy('tickets_status.ticketstatusid', 'tickets_status.name')
            ->get();
        return $status;
    }

    // Thêm mới ticket
    public function create($request)
    {
        $ticket =  new Ticket($request);
        $ticket->date = Carbon::now();
        $ticket->save();
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $ticket->tags()->attach($tag['id'], ['rel_type' => 'ticket', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name',  $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $ticket->tags()->attach($tg->id, ['rel_type' => 'ticket', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $ticket->id;
                $customFields->field_to = "tickets";
                $customFields->save();
            }
        }
        if (isset($request['file_name'])) {
            foreach ($request['file_name'] as $fileUpLoad) {
                $attachment =  new TicketsAttachments($request);
                $attachment->ticket_id = $ticket->id;
                $fileName = $fileUpLoad->getClientOriginalName();
                $fileType = $fileUpLoad->getMimeType();
                $attachment->file_name = $fileName;
                $attachment->file_type = $fileType;
                $fileUpLoad->move('uploads/ticket', $fileName);
                $attachment->save();
            }
        }
        $data = Ticket::where('id', $ticket->id)->with(
            'contact:id,firstname,lastname',
            'tags',
            'departments:departmentid,name',
            'staff:staffId,firstname,lastname',
            'project:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        return $data;
    }

    // Filter trong ticket
    public function filterByTicket($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $status =  isset($request["status"]) ? json_decode($request["status"]) : null;
        $member =  isset($request["member"]) ? $request["member"] : null;
        $ticket = Ticket::query();
        $ticket = $ticket
            ->when(!empty($status), function ($query) use ($status) {
                return $query->whereIn('status', $status);
            })
            ->when(!empty($member), function ($query) use ($member) {
                if ($member === 1) {
                    return $query->where('assigned', Auth::user()->staffid);
                }
            });
        $ticket = $ticket
            ->with(
                'contact:id,firstname,lastname',
                'tags',
                'departments:departmentid,name',
                'staff:staffId,firstname,lastname',
                'project:id,name',
                'customFields:id,field_to,name',
                'customFieldsValues'
            )->select('tickets.*')->distinct()->orderBy('tickets.date', 'desc');
        if ($limit > 0) {
            $ticket = $ticket->paginate($limit, ['*'], 'page', $page);
        } else {
            $ticket = $ticket->get();
        }
        return $ticket;
    }

    public function getListByProject($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Ticket::where('project_id', '=', $id);
        if (!$baseQuery) {
            return null;
        }
        if ($search) {
            $baseQuery = $baseQuery->where('subject', 'like', '%' . $search . '%');
        }
        $ticket = $baseQuery->with(
            'contact:id,firstname,lastname',
            'tags',
            'departments:departmentid,name',
            'staff:staffid,firstname,lastname',
            'project:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->select('tickets.*')->orderBy('tickets.date', 'desc');
        if ($limit > 0) {
            $ticket = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $ticket = $baseQuery->get();
        }

        return $ticket;
    }

    public function createByProject($id, $request)
    {
        $requestData = is_array($request) ? $request : $request->toArray();
        $ticket = new Ticket();
        $ticket->fill($requestData);
        $ticket->date = Carbon::now();
        $ticket->project_id = $id;
        $ticket->save();

        // Xử lý tags
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $ticket->tags()->attach($tag['id'], ['rel_type' => 'ticket', 'tag_order' => $tag['tag_order'] ?? null]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $ticket->tags()->attach($tg->id, ['rel_type' => 'ticket', 'tag_order' => $tag['tag_order'] ?? null]);
                }
            }
        }

        // Xử lý customFieldsValues
        if (!empty($request['customFieldsValues']) && is_array($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $ticket->id;
                $customFields->field_to = "tickets";
                $customFields->save();
            }
        }

        // Xử lý customFields
        if (!empty($request['customFields']) && is_array($request['customFields'])) {
            foreach ($request['customFields'] as $cfValues) {
                $customField = new CustomField($cfValues);
                $customField->id = $ticket->id;
                $customField->field_to = "tickets";
                $customField->save();
            }
        }

        // Xử lý file_name
        if (!empty($requestData['file_name']) && is_array($requestData['file_name'])) {
            foreach ($requestData['file_name'] as $fileUpload) {
                if ($fileUpload instanceof UploadedFile && $fileUpload->isValid()) {
                    $attachment = new TicketsAttachments();
                    $attachment->ticket_id = $ticket->id;
                    $fileName = time() . '_' . $fileUpload->getClientOriginalName();
                    $fileType = $fileUpload->getMimeType();

                    // Kiểm tra loại file
                    $allowedFileTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    if (!in_array($fileType, $allowedFileTypes)) {
                        return response()->json(['error' => 'File type not allowed'], 422);
                    }

                    // Kiểm tra kích thước file
                    if ($fileUpload->getSize() > 5000000) { // 5MB
                        return response()->json(['error' => 'File too large'], 422);
                    }

                    $attachment->file_name = $fileName;
                    $attachment->file_type = $fileType;

                    // Lưu file sử dụng Storage
                    $filePath = $fileUpload->storeAs('ticket', $fileName, 'public');
                    $attachment->file_path = $filePath; // Lưu đường dẫn vào cơ sở dữ liệu

                    $attachment->save();
                }
            }
        }

        $data = Ticket::where('id', $ticket->id)
            ->with(
                'contact:id,first_name,last_name',
                'tags',
                'departments:id,name',
                'staff:id,first_name,last_name',
                'project:id,name',
                'customFields:id,field_to,name',
                'customFieldsValues'
            )
            ->first();
        return $data;
    }

    public function countByProject($project_id)
    {
        $status = TicketsStatus::leftJoin('tickets', 'tickets_status.ticketstatusid', '=', 'tickets.status')
            ->select('tickets_status.ticketstatusid', 'tickets_status.name')
            ->selectRaw('COUNT(tickets.status) as count')
            ->groupBy('tickets_status.ticketstatusid', 'tickets_status.name')
            ->where('tickets.project_id', $project_id)
            ->get();
        return $status;
    }
}

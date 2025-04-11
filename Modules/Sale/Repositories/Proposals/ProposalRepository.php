<?php

namespace Modules\Sale\Repositories\Proposals;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;
use Modules\Sale\Entities\Itemable;
use Modules\Sale\Entities\Proposal;
use Modules\Sale\Entities\ProposalComments;
use Modules\Sale\Entities\ItemTax;

class ProposalRepository implements ProposalInterface
{
    use LogActivityTrait;

    public function findId($id)
    {
        $data = Proposal::where('id', $id)->with('itemable.itemTax', 'tags', 'customFields:id,field_to,name', 'customFieldsValues')->get();
        return $data;
    }

    public function getListByCustomer($id, $requestData)
    {
        $limit = isset($requestData['limit']) && ctype_digit($requestData['limit']) ? $requestData['limit'] : 0;
        $search = isset($requestData['search']) ? $requestData['search'] : null;
        $page = isset($requestData['page']) && ctype_digit($requestData['page']) ? (int) $requestData['page'] : 1;
        $baseQuery = Proposal::leftJoin('customers', 'customers.id', '=', 'proposals.rel_id')->where('customers.id', '=', $id)->where('proposals.rel_type', '=', 'customer');

        if ($search) {
            $baseQuery = $baseQuery->where(function ($q) use ($search) {
                $q->where('proposals.subject', 'like', '%' . $search . '%')
                    ->orWhere('proposals.total', 'like', '%' . $search . '%')
                    ->orWhere('proposals.date', 'like', '%' . $search . '%')
                    ->orWhere('proposals.open_till', 'like', '%' . $search . '%');
            });
        }
        $proposal = $baseQuery->with('customer:id,company', 'tags:id,name', 'customFields:id,field_to,name', 'customFieldsValues')->select('proposals.*')->orderBy('proposals.created_at', 'desc');

        if ($limit > 0) {
            $proposal = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $proposal = $baseQuery->get();
        }

        return $proposal;
    }

    public function getListByLead($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Proposal::where('proposals.rel_id', '=', $id)->where('proposals.rel_type', '=', 'lead');

        if ($search) {
            $baseQuery = $baseQuery
                ->where('subject', 'like', '%' . $search . '%')
                ->orWhere('total', 'like', '%' . $search . '%')
                ->orWhere('date', 'like', '%' . $search . '%')
                ->orWhere('open_till', 'like', '%' . $search . '%');
        }

        if ($limit > 0) {
            $proposal = $baseQuery->paginate($limit);
        } else {
            $proposal = $baseQuery->get();
        }

        return $proposal;
    }

    public function findItemable($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $baseQuery = Proposal::join('itemable', 'itemable.rel_id', '=', 'proposals.id')->where('itemable.rel_id', '=', $id)->where('itemable.rel_type', '=', 'proposal');

        $proposal = $baseQuery->with('itemable')->select('proposals.*');

        if ($limit > 0) {
            $proposal = $baseQuery->paginate($limit);
        } else {
            $proposal = $baseQuery->get();
        }
        return $proposal;
    }

    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 10;
        $search = $request['search'] ?? null;
        $subject = $request['subject'] ?? null;
        $total = $request['total'] ?? null;
        $date = $request['date'] ?? null;
        $openTill = $request['open_till'] ?? null;
        $tags = $request['tags'] ?? null;
        $to = $request['to'] ?? null;
        $baseQuery = Proposal::query();
        if ($subject) {
            $baseQuery->where('subject', 'like', '%' . $subject . '%');
        }
        if ($search) {
            $baseQuery->where(function ($query) use ($search) {
                $query
                    ->where('subject', 'like', '%' . $search . '%')
                    ->orWhere('total', 'like', '%' . $search . '%')
                    ->orWhere('date', 'like', '%' . $search . '%')
                    ->orWhere('open_till', 'like', '%' . $search . '%');
            });
        }
        if ($total) {
            $baseQuery->where('total', $total);
        }
        if ($date) {
            $baseQuery->where('date', $date);
        }
        if ($openTill) {
            $baseQuery->where('open_till', $openTill);
        }
        if ($tags) {
            $baseQuery->join('taggables', 'taggables.rel_id', '=', 'proposals.id')->where('taggables.rel_type', 'proposal')->join('tags', 'tags.id', '=', 'taggables.tag_id')->where('tags.name', $tags);
        }
        if ($to) {
            $baseQuery->where('to', $to);
        }

        $proposal = $baseQuery->with('tags', 'itemable', 'items', 'leads', 'customFields:id,field_to,name', 'customFieldsValues')->select('proposals.*')->orderBy('proposals.created_at', 'desc')->distinct()->paginate($limit);
        return $proposal;
    }

    public function listSelect() {}

    public function create($request)
    {
        $propo = new Proposal($request);
        // $propo->hash = md5($request['hash']);
        $propo->fill($request);
        $propo->date = Carbon::now();
        $propo->created_by = Auth::id();
        $propo->save();
        $this->createSaleActivity($propo->id, 1, ActivityKey::CREATE_PROPOSAL);

        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->field_id = $propo->id;
                $customFields->rel_id = $propo->id;
                $customFields->field_to = 'proposal';
                $customFields->save();
            }
        }

        if (isset($request['itemable'])) {
            foreach ($request['itemable'] as $item) {
                $itemable = new Itemable($item);
                $itemable->rel_id = $propo->id;
                $itemable->rel_type = 'proposal';
                $itemable->created_by = Auth::user()->id;
                $itemable->save();
                if (isset($item['customFieldsValues'])) {
                    foreach ($item['customFieldsValues'] as $cfValues) {
                        $customFields = new CustomFieldValue($cfValues);
                        $customFields->rel_id = $propo->id;
                        $customFields->value = $cfValues['value'];
                        $customFields->field_to = 'proposal';
                        $customFields->save();
                    }
                }
                if (isset($item['item_tax'])) {
                    foreach ($item['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->itemid = $itemable->id;
                        $itemTax->rel_type = 'proposal';
                        $itemTax->rel_id = $propo->id;
                        $itemTax->created_by = Auth::user()->staffid;
                        $itemTax->save();
                    }
                }
            }
        }

        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                $tagOrder = isset($tag['tag_order']) ? $tag['tag_order'] : null;

                if (isset($tag['id'])) {
                    $propo->tags()->attach($tag['id'], ['rel_type' => 'proposal', 'tag_order' => $tagOrder]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $propo->tags()->attach($tg->id, ['rel_type' => 'proposal', 'tag_order' => $tagOrder]);
                }
            }
        }
        $data = Proposal::where('id', $propo->id)->with('itemable.itemTax', 'tags', 'customFields:id,field_to,name', 'customFieldsValues')->get();
        return $data;
    }

    public function update($id, $request)
    {
        $customFields = [];
        $propo = Proposal::find($id);
        if (!$propo) {
            return null;
        }
        $propo->fill($request);
        $propo->updated_by = Auth::user()->staffid;
        $propo->save();
        if ($propo->rel_type = 'lead') {
            $this->createLeadActivity($propo->rel_id, ActivityKey::UPDATE_PROPOSAL_BY_LEAD);
        }
        $this->createSaleActivity($id, 1, ActivityKey::UPDATE_PROPOSAL);
        $propo->itemable()->delete();
        $itemableId = [];
        if (isset($request['itemable'])) {
            foreach ($request['itemable'] as $itemable) {
                $item = isset($itemable['id']) ? $itemable['id'] : 0;
                $itemableNew = Itemable::findOrNew($item);
                $itemableNew->fill($itemable);
                $itemableNew->rel_id = $propo->id;
                $itemableNew->rel_type = 'proposal';
                $itemableNew->save();
                $itemableId[] = $itemableNew->id;
                if (isset($itemable['customFieldsValues'])) {
                    foreach ($itemable['customFieldsValues'] as $cValues) {
                        $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                        $customFieldsValues = CustomFieldValue::findorNew($cfvId);
                        $customFieldsValues->fill($cValues);
                        $customFieldsValues->field_id = $cValues['field_id'];
                        $customFieldsValues->rel_id = $itemableNew->id;
                        $customFieldsValues->value = $cValues['value'];
                        $customFieldsValues->field_to = 'items';
                        $customFieldsValues->save();
                        $customFields[] = $customFieldsValues->id;
                    }
                    CustomFieldValue::where('rel_id', $itemableNew->id)->whereNotIn('id', $customFields)->delete();
                }
                if (isset($item['item_tax'])) {
                    foreach ($item['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->itemid = $itemable->id;
                        $itemTax->rel_type = 'proposal';
                        $itemTax->rel_id = $propo->id;
                        $itemTax->save();
                    }
                }
            }
        }

        $propo->taggable()->delete();
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                $tagOrder = isset($tag['tag_order']) ? $tag['tag_order'] : null;

                if (isset($tag['id'])) {
                    $propo->tags()->attach($tag['id'], ['rel_type' => 'proposal', 'tag_order' => $tagOrder]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $propo->tags()->attach($tg->id, ['rel_type' => 'proposal', 'tag_order' => $tagOrder]);
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findorNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $propo->id;
                $customFieldsValues->field_to = 'proposal';
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id', $propo->id)->whereNotIn('id', $customFields)->delete();
        }
        Itemable::where('rel_id', $propo->id)->whereNotIn('id', $itemableId)->delete();
        $data = Proposal::where('id', $propo->id)->with('itemable.itemTax', 'tags', 'customFields:id,field_to,name', 'customFieldsValues')->get();
        return $data;
    }

    public function destroy($id)
    {
        $propo = Proposal::find(id: $id);
        if (!$propo) {
            return null;
        }
        if ($propo->rel_type = 'lead') {
            $this->createLeadActivity($propo->rel_id, ActivityKey::DELETE_PROPOSAL_BY_LEAD);
        }
        $this->createSaleActivity($id, 1, ActivityKey::DELETE_PROPOSAL);
        $propo->itemable()->delete();
        $propo->proposalComments()->delete();
        $propo->delete();
        return $propo;
    }

    public function changeStatus($id, $status)
    {
        $proposal = Proposal::where('id', $id)->first();
        $proposal->last_status_change = Carbon::now();
        $proposal->status = $status;
        $proposal->save();
        $this->createSaleActivity($id, 1, ActivityKey::CHANGE_STATUS_BY_PROPOSAL);
        return $proposal;
    }

    public function createByLead($id, $request)
    {
        $propo = new Proposal($request);
        $propo->hash = md5($request['hash']);
        $propo->datecreated = Carbon::now();
        $propo->date = date('Y-m-d');
        $propo->rel_id = $id;
        $propo->rel_type = 'lead';
        $propo->created_by = Auth::user()->staffid;
        $propo->save();
        if (isset($request['itemable'])) {
            foreach ($request['itemable'] as $item) {
                $itemable = new Itemable($item);
                $itemable->rel_id = $propo->id;
                $itemable->rel_type = 'proposal';
                $propo->itemable()->save($itemable);
            }
        }
        if (isset($request['tag'])) {
            foreach ($request['tag'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $propo->tags()->attach($tag['id'], ['rel_type' => 'proposal', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $propo->tags()->attach($tg->id, ['rel_type' => 'proposal', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        $this->createLeadActivity($id, ActivityKey::CREATE_PROPOSAL_BY_LEAD);
        $data = Proposal::where('id', $propo->id)->with('tags', 'itemable')->get();
        return $data;
    }

    public function createByComment($id, $request)
    {
        $comment = new ProposalComments($request);
        $comment->dateadded = Carbon::now();
        $comment->staffid = Auth::user()->staffid;
        $comment->created_by = Auth::user()->staffid;
        $comment->proposalid = $id;
        $comment->save();
        $this->createSaleActivity($id, 1, ActivityKey::CREATE_COMMENT_BY_PROPOSAL);
        $data = ProposalComments::where('id', $comment->id)->with('staff')->get();
        return $data;
    }

    public function getListByComment($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $baseQuery = ProposalComments::where('proposalid', '=', $id);

        $comment = $baseQuery->with('staff')->select('proposal_comments.*');

        if ($limit > 0) {
            $comment = $baseQuery->paginate($limit);
        } else {
            $comment = $baseQuery->get();
        }
        return $comment;
    }

    public function updateByComment($id, $request)
    {
        $comment = ProposalComments::find($id);

        $comment->fill($request);
        $comment->updated_by = Auth::user()->staffid;
        $this->createSaleActivity($comment->proposalid, 1, ActivityKey::UPDATE_COMMENT_BY_PROPOSAL);
        $comment->save();
        $data = ProposalComments::where('id', $comment->id)->with('staff')->get();
        return $data;
    }
    public function destroyByComment($id)
    {
        $comment = ProposalComments::find($id);

        $this->createSaleActivity($comment->proposalid, 1, ActivityKey::DELETE_COMMENT_BY_PROPOSAL);
        $comment->delete();
        return $comment;
    }

    public function copyData($id)
    {
        //copy dữ liệu Proposal
        $proposal = Proposal::find($id);

        $newProposal = $proposal->replicate();
        $newProposal->created_by = Auth::user()->staffid;
        $this->createSaleActivity($id, 1, ActivityKey::COPY_PROPOSAL);
        $newProposal->save();
        //copy Tag
        $tag = Taggables::where('rel_type', 'proposal')->where('rel_id', $id)->first();
        if ($tag) {
            $newTag = $tag->replicate();
            $newTag->rel_id = $newProposal->id;
            $newTag->save();
        }
        //copy Itemable
        $itemable = Itemable::where('rel_id', $id)->first();
        if ($itemable) {
            $newItemAble = $itemable->replicate();
            $newItemAble->rel_id = $newProposal->id;
            $newItemAble->save();
        }
        //copy Customfield
        $customField = CustomFieldValue::where('rel_id', $id)->first();
        if ($customField) {
            $newCustomField = $customField->replicate();
            $newCustomField->rel_id = $newProposal->id;
            $newCustomField->save();
        }

        $data = Proposal::where('id', $newProposal->id)->with('itemable', 'tags', 'customFieldsValues:id,value')->get();
        return $data;
    }

    public function filterByProposal($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 0;
        $status = isset($request['status']) ? json_decode($request['status']) : null;
        $year = isset($request['year']) ? json_decode($request['year']) : null;
        $assigned = isset($request['assigned']) ? json_decode($request['assigned']) : null;
        $expired = isset($request['expired']) ? (int) $request['expired'] : 0;
        $lead = isset($request['lead']) ? (int) $request['lead'] : 0;
        $customer = isset($request['customer']) ? (int) $request['customer'] : 0;
        $proposal = Proposal::leftJoin('staff', 'staff.id', '=', 'proposals.assigned')->leftJoin('leads', 'leads.id', '=', 'proposals.rel_id')->leftJoin('customers', 'customers.id', '=', 'proposals.rel_id');
        $proposal = $proposal
            ->when(!empty($status), function ($query) use ($status) {
                return $query->whereIn('proposals.status', $status);
            })
            ->when(!empty($year), function ($query) use ($year) {
                return $query->whereIn(DB::raw('year(proposals.date)'), $year);
            })
            ->when(!empty($assigned), function ($query) use ($assigned) {
                return $query->whereIn('proposals.assigned', $assigned);
            })
            ->when(!empty($expired), function ($query) use ($expired) {
                if ($expired === 1) {
                    return $query->where('proposals.open_till', '>', Carbon::now()->toDateString());
                }
            })
            ->when(!empty($lead), function ($query) use ($lead) {
                if ($lead === 1) {
                    return $query->whereExists(function ($query) {
                        $query->select('leads.id')->from('leads')->whereRaw('leads.id = proposals.rel_id')->where('proposals.rel_type', '=', 'lead');
                    });
                }
            })
            ->when(!empty($customer), function ($query) use ($customer) {
                if ($customer === 1) {
                    return $query->whereExists(function ($query) {
                        $query->select('customers.id')->from('customers')->whereRaw('customers.id = proposals.rel_id')->where('proposals.rel_type', '=', 'customer');
                    });
                }
            });

        $proposal = $proposal->with('customer:id,company', 'tags:id,name', 'customFields:id,field_to,name', 'customFieldsValues')->select('proposals.*')->distinct()->orderBy('proposals.created_at', 'desc');
        if ($limit > 0) {
            $proposal = $proposal->paginate($limit, ['*'], 'page', $page);
        } else {
            $proposal = $proposal->get();
        }
        return $proposal;
    }

    public function createByCustomer($id, $request)
    {
        $propo = new Proposal($request);
        $propo->hash = md5($request['hash']);
        $propo->datecreated = Carbon::now();
        $propo->date = date('Y-m-d');
        $propo->rel_id = $id;
        $propo->rel_type = 'customer';
        $propo->created_by = Auth::user()->staffid;
        $propo->save();
        $this->createSaleActivity($id, 1, ActivityKey::CREATE_PROPOSAL_BY_CUSTOMER);
        if (isset($request['itemable'])) {
            foreach ($request['itemable'] as $item) {
                $itemable = new Itemable($item);
                $itemable->rel_id = $propo->id;
                $itemable->rel_type = 'proposal';
                $propo->itemable()->save($itemable);
            }
        }
        if (isset($request['tag'])) {
            foreach ($request['tag'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $propo->tags()->attach($tag['id'], ['rel_type' => 'proposal', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $propo->tags()->attach($tg->id, ['rel_type' => 'proposal', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        $data = Proposal::where('id', $propo->id)->with('tags', 'itemable')->get();
        return $data;
    }

    public function count()
    {
        $counts = Proposal::select('status', DB::raw('COUNT(*) as count'))
            ->whereIn('status', [0, 1])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $result = [
            'status_0' => $counts[0] ?? 0,
            'status_1' => $counts[1] ?? 0,
        ];
        return $result;
    }
}

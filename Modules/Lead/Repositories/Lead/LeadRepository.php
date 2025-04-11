<?php

namespace Modules\Lead\Repositories\Lead;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Illuminate\Support\Facades\Auth;
use Modules\Lead\Entities\Lead;
use Modules\Customer\Entities\Contact;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Tag;
use Modules\Lead\Entities\LeadSource;
use Modules\Lead\Entities\LeadStatus;

class LeadRepository implements LeadInterface
{
    use LogActivityTrait;

    public function findId($id, $request)
    {
        $limit['proposal'] = isset($request['limit_proposal']) ? $request['limit_proposal'] : 0;
        $limit['task'] = isset($request['limit_task']) ? $request['limit_task'] : 0;
        $limit['reminder'] = isset($request['limit_reminder']) ? $request['limit_reminder'] : 0;

        $lead = Lead::with([
            'leadStatus',
            'leadSource',
            'proposals' => function ($query) use ($limit) {
                $query->paginate($limit['proposal']);
            },
            'tasks' => function ($query) use ($limit) {
                $query->paginate($limit['task']);
            },
            'reminders' => function ($query) use ($limit) {
                $query->paginate($limit['reminder']);
            },
            'notes',
            'leadActivityLog',
            'assigned:id,email,first_name,last_name,profile_image',
            'tags',
            'customFields:id,field_to,name',
            'customFieldsValues',
            'customer:id,company',
        ])->find($id);

        if (!$lead) {
            return null;
        }
        return $lead;
    }

    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $assigned = isset($request['assigned']) ? $request['assigned'] : null;
        $status = isset($request['status']) ? json_decode($request['status']) : null;
        $source = isset($request['source']) ? $request['source'] : null;
        $search = isset($request['search']) ? $request['search'] : '';
        $orderName = isset($request['orderName']) ? $request['orderName'] : 'id';
        $orderType = isset($request['orderType']) ? $request['orderType'] : 'desc';
        $baseQuery = Lead::query()
            ->leftJoin('customers', 'customers.id', '=', 'leads.customer_id')
            ->where(function ($query) {
                $query->select('customers.id')->from('customers')->whereRaw('customers.id = leads.customer_id');
            });
        if ($search) {
            $baseQuery = $baseQuery
                ->where('leads.name', 'like', '%' . $search . '%')
                ->orWhere('leads.email', 'like', '%' . $search . '%')
                ->orWhere('leads.phone_number', 'like', '%' . $search . '%')
                ->orWhere('leads.assigned', $search)
                ->orWhere('leads.source', $search);
        }
        if ($assigned) {
            $baseQuery = $baseQuery->where('assigned', $assigned);
        }
        if ($status && is_array($status)) {
            $baseQuery = $baseQuery->whereIn('leads.status', $status);
        }

        if ($source) {
            $baseQuery = $baseQuery->where('source', $source);
        }
        $lead = $baseQuery
            ->with([
                'leadStatus',
                'leadSource',
                'assigned:id,email,first_name,last_name,profile_image',
                'tags',
                'customFields:id,field_to,name',
                'customFieldsValues'
            ])
            ->select('leads.*')
            ->when($orderName === 'created_at', function ($query) use ($orderType) {
                return $query->orderBy('created_at', $orderType);
            }, function ($query) use ($orderName, $orderType) {
                return $query->orderBy($orderName, $orderType)->orderBy('created_at', 'desc');
            });

        if ($limit > 0) {
            $lead = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $lead = $baseQuery->get();
        }
        return $lead;
    }

    public function listSelect()
    {
        $leads = Lead::orderBy('name')->get();
        return $leads;
    }

    protected function handleLeadStatus($lead, $statusData)
    {
        if (!isset($statusData['id'])) {
            $status = new LeadStatus($statusData);
            $status->name = $statusData['name'];
            $status->save();
            $lead->status = $status->id;
            $lead->save();
        }
    }

    protected function handleLeadSource($lead, $sourceData)
    {
        if (!isset($sourceData['id'])) {
            $source = new LeadSource($sourceData);
            $source->name = $sourceData['name'];
            $source->save();
            $lead->source = $source->id;
            $lead->save();
        }
    }

    protected function handleTags($lead, $tags)
    {
        foreach ($tags as $tag) {
            if (isset($tag['id'])) {
                $attachData = [
                    'rel_type' => 'lead',
                    'tag_order' => $tag['tag_order'] ?? null,
                ];
                $lead->tags()->syncWithoutDetaching([$tag['id'] => $attachData]);
            } else {
                if (!isset($tag['name']) || empty(trim($tag['name']))) {
                    continue;
                }

                $tagModel = Tag::firstOrCreate(
                    ['name' => $tag['name']],
                    ['name' => $tag['name']]
                );

                $attachData = [
                    'rel_type' => 'lead',
                    'tag_order' => $tag['tag_order'] ?? null,
                ];
                $lead->tags()->syncWithoutDetaching([$tagModel->id => $attachData]);
            }
        }
    }

    protected function handleCustomFields($lead, $customFieldsValues)
    {
        foreach ($customFieldsValues as $cfValues) {
            $customField = new CustomFieldValue($cfValues);
            $customField->rel_id = $lead->id;
            $customField->field_to = 'leads';
            $customField->save();
        }
    }

    public function create($request)
    {
        $lead = new Lead($request);
        if (isset($request['contacted_today'])) {
            $lead->dateassigned = null;
        }
        $lead->created_by = Auth::id();
        $lead->save();

        if (isset($request['lead_status'])) {
            $this->handleLeadStatus($lead, $request['lead_status']);
        }

        if (isset($request['lead_source'])) {
            $this->handleLeadSource($lead, $request['lead_source']);
        }

        if (isset($request['tags'])) {
            $this->handleTags($lead, $request['tags']);
        }

        if (isset($request['customFieldValue'])) {
            $this->handleCustomFields($lead, $request['customFieldValue']);
        }

        $this->createLeadActivity($lead->id, ActivityKey::CREATE_LEAD);

        $data = Lead::where('id', $lead->id)
            ->with('leadStatus', 'leadSource', 'tags', 'customFields:id,field_to,name', 'customFieldsValues')
            ->get();
        return $data;
    }

    public function update($id, $request)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return null;
        }
        $lead->fill($request);
        $lead->updated_by = Auth::user()->id;
        $lead->save();

        if (isset($request['lead_status'])) {
            $status = $request['lead_status'];

            if (!isset($status['id'])) {
                $ls = new LeadStatus($request['lead_status']);
                $ls->name = $status['name'];
                $ls->save();

                $lead->status = $ls->id;
                $lead->save();
            }
        }

        if (isset($request['lead_source'])) {
            $source = $request['lead_source'];

            if (!isset($source['id'])) {
                $lsc = new LeadSource($request['lead_source']);
                $lsc->name = $source['name'];
                $lsc->save();

                $lead->source = $lsc->id;
                $lead->save();
            }
        }

        $lead->taggable()->delete();
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $lead->tags()->attach($tag['id'], ['rel_type' => 'lead', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $lead->tags()->attach($tg->id, ['rel_type' => 'lead', 'tag_order' => $tag['tag_order']]);
                }
            }
        }

        $customFields = [];
        if (isset($request['customFieldValue'])) {
            foreach ($request['customFieldValue'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findorNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $lead->id;
                $customFieldsValues->field_to = 'leads';
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id', $lead->id)
                ->whereNotIn('id', $customFields)
                ->delete();
        }
        $this->createLeadActivity($id, ActivityKey::UPDATE_LEAD);

        $data = Lead::where('id', $lead->id)
            ->with('leadStatus', 'leadSource', 'customFields:id,field_to,name', 'customFieldsValues', 'tags')
            ->get();
        return $data;
    }

    public function destroy($id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return null;
        }
        $this->createLeadActivity($id, ActivityKey::DELETE_LEAD);
        $lead->tasks()->delete();
        $lead->tags()->detach();
        $lead->leadActivityLog()->delete();
        $lead->notes()->delete();
        $lead->reminders()->delete();
        $lead->proposals()->delete();
        $lead->delete();
        return $lead;
    }

    public function changeStatus($id, $status)
    {
        $lead = Lead::where('id', $id)->first();
        $lead->lastest_status_change = date('Y-m-d H:i:s');
        $lead->status = $status;
        $lead->save();
        $this->createLeadActivity($id, ActivityKey::CHANGE_STATUS_BY_LEAD);

        $data = Lead::where('id', $lead->id)
            ->with('leadStatus', 'leadSource', 'tags')
            ->get();
        return $data;
    }

    public function convertToCustomer($id, $request)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return null;
        }
        $this->createLeadActivity($id, ActivityKey::CONVERT_CUSTOMER_BY_LEAD);
        $customer = new Customer();
        $email = isset($request['email']) ? $request['email'] : null;
        $company = isset($request['company']) ? $request['company'] : null;
        $phone_number = isset($request['phone_number']) ? $request['phone_number'] : null;
        $website = isset($request['website']) ? $request['website'] : null;
        $address = isset($request['address']) ? $request['address'] : null;
        $city = isset($request['city']) ? $request['city'] : null;
        $state = isset($request['state']) ? $request['state'] : null;
        $country = isset($request['country']) ? $request['country'] : 0;
        $zip = isset($request['zip']) ? $request['zip'] : null;
        $customer->id = $id;
        $customer->company = $company;
        if ($phone_number) {
            $customer->phone_number = $phone_number;
        }
        if ($email) {
            $customer->email = $email;
        }
        if ($website) {
            $customer->website = $website;
        }
        if ($address) {
            $customer->address = $address;
            $customer->billing_street = $address;
        }
        if ($city) {
            $customer->city = $city;
            $customer->billing_city = $city;
        }
        if ($state) {
            $customer->state = $state;
            $customer->billing_state = $state;
        }
        if ($country) {
            $customer->country = $country;
            $customer->billing_country = $country;
        }
        if ($zip) {
            $customer->zip = $zip;
            $customer->billing_zip = $zip;
        }
        $customer->save();
        if (isset($request['contacts']) && is_array($request['contacts'])) {
            foreach ($request['contacts'] as $contactData) {
                $contact = new Contact();
                $contact->first_name = isset($contactData['first_name']) ? $contactData['first_name'] : null;
                $contact->last_name = isset($contactData['last_name']) ? $contactData['last_name'] : null;
                $contact->email = isset($contactData['email']) ? $contactData['email'] : null;
                $contact->fill($contactData);
                if (!empty($contactData['title'])) {
                    $contact->title = $contactData['title'];
                }
                $contact->save();
            }
        }
        if (isset($request['customFieldsValues']) && is_array($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $customer->id;
                $customFields->field_id = 3;
                $customFields->field_to = "customer";
                $customFields->save();
            }
        }

        return Customer::with(['contacts', 'customFieldsValues'])->find($customer->id);
    }

    public function countLeadBySources($id)
    {
        $count = Lead::where('source', $id)->count();
        return $count;
    }

    public function countLeadByStatus($id)
    {
        $count = Lead::where('status', $id)->count();
        return $count;
    }
}

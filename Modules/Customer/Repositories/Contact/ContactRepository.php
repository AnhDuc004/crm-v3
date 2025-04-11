<?php

namespace Modules\Customer\Repositories\Contact;

use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\Contact;
use Illuminate\Support\Facades\Storage;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomFieldValue;

class ContactRepository implements ContactInterface
{
    public function findId($id)
    {
        $contact = Contact::where('id', $id)->get();
        if (!$contact) {
            return null;
        }
        return $contact;
    }

    public function getListByCustomer($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Contact::leftJoin('customers', 'customers.id', '=', 'contacts.customer_id')
            ->where('customers.id', '=', $id);
        if (!$baseQuery) {
            return null;
        }
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('contacts.first_name', 'like', '%' . $search . '%')
                        ->orWhere('contacts.last_name', 'like', '%' . $search . '%')
                        ->orWhere('contacts.email', 'like', '%' . $search . '%');
                }
            );
        }
        $contact = $baseQuery->with(
            'customer:id,company',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->select('contacts.*')->orderBy('contacts.created_at', 'desc');
        if ($limit > 0) {
            $contact = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $contact = $baseQuery->get();
        }
        return $contact;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $customerId = isset($request["customerId"]) ? $request["customerId"] : null;
        if ($customerId) {
            $baseQuery = Contact::leftJoin('customers', 'customers.Id', '=', 'contacts.customerId')
                ->where('customers.Id', '=', $customerId);
        }
        $baseQuery = Contact::query();
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('firstname', 'like', '%' . $search . '%')
                        ->orWhere('lastname', 'like', '%' . $search . '%')
                        ->orWhere('phonenumber', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                }
            );
        }
        $contact = $baseQuery->with('customer')->select('contacts.*')->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $contact = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $contact = $baseQuery->get();
        }
        return $contact;
    }

    public function create($id, $request)
    {
        $file = $request['profile_image'] ?? null;
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        $contact = new Contact($request);
        $contact->created_by = Auth::id();
        $contact->customer_id = $id;

        if ($file && $file->isValid()) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = "contact";
            $filePath = Storage::putFileAs($path, $file, $filename);

            $contact->profile_image = str_replace("public/", "storage/", $filePath); // Đường dẫn cho trình duyệt
        }
        $contact->save();
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $contact->id;
                $customFields->field_id = 3;
                $customFields->field_to = "contacts";
                $customFields->save();
            }
        }
        $data = Contact::where('id', $contact->id)->with(
            'customer:id,company',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        return $data;
    }

    public function update($id, $request)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return null;
        }
        $file = $request['profile_image'] ?? null;
        if ($file && $file->isValid()) {
            if ($contact->profile_image) {
                Storage::delete(str_replace("storage/", "public/", $contact->profile_image));
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $path = "contact";
            $filePath = Storage::putFileAs($path, $file, $filename);

            $contact->profile_image = str_replace("public/", "storage/", $filePath);
        }

        unset($request['profile_image']);
        $contact->fill($request);
        $contact->updated_by = Auth::user()->id;
        $contact->save();

        // Cập nhật custom fields
        if (isset($request['customFieldsValues'])) {
            $customFields = [];
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = $cValues['id'] ?? 0;
                $customFieldsValues = CustomFieldValue::findOrNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $contact->id;
                $customFieldsValues->field_to = "contacts";
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id', $contact->id)->whereNotIn('id', $customFields)->delete();
        }
        $data = Contact::where('id', $contact->id)->with(
            'customer:id,company',
            'customFieldsValues'
        )->get();
        return $data;
    }

    public function destroy($id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return null;
        }
        $contact->delete();
        return $contact;
    }

    public function toggleActive($id)
    {
        $contact = Contact::find($id);
        $contact->active = !$contact->active;
        $contact->save();
        return $contact;
    }
}

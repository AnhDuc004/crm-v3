<?php

namespace Modules\Sale\Repositories\CreditNotes;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Sale\Entities\CreditNotes;
use Modules\Sale\Entities\Itemable;
use Modules\Sale\Entities\ItemTax;

class CreditNotesRepository implements CreditNotesInterface
{
    use LogActivityTrait;

    public function findId($id)
    {
        $CreditNotes =  CreditNotes::with(
            'project:id,name',
            'customer:id,company',
            'customFields:id,field_to,name',
            'customFieldsValues',
            'itemable'
        )->find($id);
        if (!$CreditNotes) {
            return null;
        }
        return $CreditNotes;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $search = isset($request["search"]) ? $request["search"] : null;
        $rel = isset($request["rel"]) ? $request["rel"] : null;
        $relId = isset($request["relId"]) ? $request["relId"] : null;
        $baseQuery = CreditNotes::query();
        if ($search) {
            $baseQuery = $baseQuery
                ->leftJoin('customers', 'customers.id', '=', 'credit_notes.customer_id')
                ->leftJoin('projects', 'projects.id', '=', 'credit_notes.project_id')
                ->orWhere('projects.name', 'like', '%' . $search . '%')
                ->orWhere('customers.company', 'like', '%' . $search . '%')
                ->orWhere('total', 'like', '%' . $search . '%')
                ->orWhere('date', 'like', '%' . $search . '%')
                ->orWhere('reference_no', 'like', '%' . $search . '%');
        }
        if ($rel && $relId) {
            $baseQuery = $baseQuery->where($rel, $relId);
        }
        $creditNotes = $baseQuery->with(
            'customer:id,company',
            'project:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->select('credit_notes.*')->orderBy('credit_notes.created_at', 'desc');

        if ($limit > 0) {
            $creditNotes = $baseQuery->paginate($limit);
        } else {
            $creditNotes = $baseQuery->get();
        }
        return $creditNotes;
    }

    public function create($request)
    {
        $creditNotes_ = new CreditNotes($request);
        $amount = $creditNotes_->subtotal - $creditNotes_->adjustment - $creditNotes_->discount_total;
        if ($amount == $creditNotes_->total) {
            $creditNotes_->remaining_amount = 0;
        } else {
            $creditNotes_->remaining_amount = $amount;
        }
        $creditNotes_->save();
        $this->createSaleActivity($creditNotes_->id, 5, ActivityKey::CREATE_CREDIT_NOTE);
        if (isset($pItem['itemable'])) {
            foreach ($request['itemable'] as $pItem) {
                $item = new Itemable();
                $item->rel_id = $creditNotes_->id;
                $item->rel_type = 'creditnotes';
                $item->description = $pItem['description'];
                $item->long_description = $pItem['long_description'];
                $item->qty = $pItem['qty'];
                $item->rate = $pItem['rate'];
                $item->unit = $pItem['unit'];
                $item->item_order = $pItem['item_order'];
                $item->save();
                if (isset($pItem['customFieldsValues'])) {
                    foreach ($pItem['customFieldsValues'] as $cfValues) {
                        $customFields = new CustomFieldValue($cfValues);
                        $customFields->field_id = $cfValues['fieldid'];
                        $customFields->rel_id = $item->id;
                        $customFields->value = $cfValues['value'];
                        $customFields->field_to = "items";
                        $customFields->save();
                    }
                }
                if (isset($item['item_tax'])) {
                    foreach ($item['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->itemid = $creditNotes_->id;
                        $itemTax->rel_type = 'creditnotes';
                        $itemTax->rel_id = $item->id;
                        $itemTax->save();
                    }
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $creditNotes_->id;
                $customFields->field_to = "credit_note";
                $customFields->save();
            }
        }
        $data = CreditNotes::where('id', $creditNotes_->id)->with(
            'customer:id,company',
            'project:id,name',
            'itemable',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function update($id, $request)
    {
        $creditNotes = CreditNotes::find($id);
        if (!$creditNotes) {
            return null;
        }

        // Cập nhật thông tin credit note
        $creditNotes->fill($request);
        $creditNotes->save();
        // Tạo sale activity
        $this->createSaleActivity($id, 5, ActivityKey::UPDATE_CREDIT_NOTE);
        // Xóa itemable cũ
        $creditNotes->itemable()->delete();
        $itemableId = [];
        if (isset($request['itemable'])) {
            foreach ($request['itemable'] as $itemable) {
                $item = isset($itemable['id']) ? $itemable['id'] : 0;
                $itemableNew = Itemable::findOrNew($item);
                $itemableNew->fill($itemable);
                $itemableNew->rel_id = $creditNotes->id;
                $itemableNew->rel_type = 'creditnotes';
                $itemableNew->save();

                $itemableId[] = $itemableNew->id;
                // Xử lý customFieldsValues của itemable
                if (isset($itemable['customFieldsValues'])) {
                    $customFields = [];
                    foreach ($itemable['customFieldsValues'] as $cValues) {
                        $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                        $customFieldsValues = CustomFieldValue::findOrNew($cfvId);
                        $customFieldsValues->fill($cValues);
                        $customFieldsValues->field_id = $cValues['field_id'];
                        $customFieldsValues->rel_id = $itemableNew->id;
                        $customFieldsValues->value = $cValues['value'];
                        $customFieldsValues->field_to = "items";
                        $customFieldsValues->save();
                        $customFields[] = $customFieldsValues->id;
                    }

                    CustomFieldValue::where('rel_id', $itemableNew->id)
                        ->whereNotIn('id', $customFields)
                        ->delete();
                }

                // Xử lý item tax
                if (isset($itemable['item_tax'])) {
                    foreach ($itemable['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->item_id = $creditNotes->id;
                        $itemTax->rel_type = 'creditnotes';
                        $itemTax->rel_id = $itemableNew->id;
                        $itemTax->save();
                    }
                }
            }
        }

        // Xóa itemable không còn tồn tại
        Itemable::where('rel_id', $creditNotes->id)->whereNotIn('id', $itemableId)->delete();

        // Cập nhật CustomFieldsValues của CreditNotes
        if (isset($request['customFieldsValues'])) {
            $customFields = [];
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findOrNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $creditNotes->id;
                $customFieldsValues->field_to = "credit_note";
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }

            CustomFieldValue::where('rel_id', $creditNotes->id)
                ->whereNotIn('id', $customFields)
                ->delete();
        }

        // Lấy dữ liệu trả về
        $data = CreditNotes::where('id', $creditNotes->id)->with(
            'customer:id,company',
            'project:id,name',
            'itemable',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        return $data;
    }

    public function destroy($id)
    {
        $CreditNotes = CreditNotes::find($id);
        if (!$CreditNotes) {
            return null;
        }
        $this->createSaleActivity($id, 5, ActivityKey::DELETE_CREDIT_NOTE);
        $data = $CreditNotes->delete();
        return $data;
    }

    public function filterByCreditNote($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 0;
        $status = isset($request["status"]) ? json_decode($request["status"]) : null;
        $year = isset($request["year"]) ? json_decode($request["status"]) : null;
        $creditNotes = CreditNotes::query();
        $creditNotes = $creditNotes
            ->when(!empty($status), function ($query) use ($status) {
                return $query->whereIn('status', $status);
            })
            ->when(!empty($year), function ($query) use ($year) {
                return $query->whereIn(DB::raw("year(date)"), $year);
            });
        $creditNotes = $creditNotes->with(
            'project:id,name',
            'customer:clientId,company',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->select('creditnotes.*')->distinct()->orderBy('creditnotes.datecreated', 'desc');
        if ($limit > 0) {
            $creditNotes = $creditNotes->paginate($limit, ['*'], 'page', $page);
        } else {
            $creditNotes = $creditNotes->get();
        }
        return $creditNotes;
    }

    public function createByCustomer($id, $request)
    {
        $creditNotes_ = new CreditNotes($request);
        $creditNotes_->customer_id = $id;
        $amount = $creditNotes_->subtotal - $creditNotes_->adjustment - $creditNotes_->discount_total;
        if ($amount == $creditNotes_->total) {
            $creditNotes_->remaining_amount = 0;
        } else {
            $creditNotes_->remaining_amount = $amount;
        }
        $creditNotes_->save();
        $this->createSaleActivity($creditNotes_->id, 5, ActivityKey::CREATE_CREDIT_NOTE_BY_CUSTOMER);
        if (isset($request['itemable'])) {
            foreach ($request['itemable'] as $pItem) {
                $item = new Itemable();
                $item->rel_id = $creditNotes_->id;
                $item->rel_type = 'creditnotes';
                $item->description = $pItem['description'];
                $item->long_description = $pItem['long_description'];
                $item->qty = $pItem['qty'];
                $item->rate = $pItem['rate'];
                $item->unit = $pItem['unit'];
                $item->item_order = $pItem['item_order'];
                $item->save();
                if (isset($pItem['customFieldsValues'])) {
                    foreach ($pItem['customFieldsValues'] as $cfValues) {
                        $customFields = new CustomFieldValue($cfValues);
                        $customFields->field_id = $cfValues['field_id'];
                        $customFields->rel_id = $item->id;
                        $customFields->value = $cfValues['value'];
                        $customFields->field_to = "items";
                        $customFields->save();
                    }
                }
                if (isset($item['item_tax'])) {
                    foreach ($item['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->itemid = $creditNotes_->id;
                        $itemTax->rel_type = 'creditnotes';
                        $itemTax->rel_id = $item->id;
                        $itemTax->save();
                    }
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $creditNotes_->id;
                $customFields->field_to = "credit_note";
                $customFields->save();
            }
        }
        $data = CreditNotes::where('id', $creditNotes_->id)->with(
            'customer:id,company',
            'project:id,name',
            'itemable',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && filter_var($request['limit'], FILTER_VALIDATE_INT) ? (int) $request['limit'] : 10;
        $page = isset($request['page']) && filter_var($request['page'], FILTER_VALIDATE_INT) ? (int) $request['page'] : 1;
        $search = isset($request['search']) ? $request['search'] : null;

        $baseQuery = CreditNotes::where('customer_id', $id)
            ->with(
                'project:id,name',
                'customer:id,company',
                'customFields:id,field_to,name',
                'customFieldsValues',
                'itemable'
            );
        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('total', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('date', 'like', '%' . $search . '%');
            });
        }
        $creditNotes = $limit > 0
            ? $baseQuery->paginate($limit, ['*'], 'page', $page)
            : $baseQuery->get();

        return $creditNotes;
    }
}

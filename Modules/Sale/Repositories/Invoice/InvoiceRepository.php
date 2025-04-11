<?php

namespace Modules\Sale\Repositories\Invoice;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;
use Modules\Expense\Entities\Expenses;
use Modules\Sale\Entities\Invoice;
use Modules\Sale\Entities\Itemable;
use Modules\Sale\Entities\Payment;
use Modules\Sale\Entities\Estimate;
use Modules\Sale\Entities\ItemTax;
use Modules\Sale\Entities\Proposal;
use Modules\Sale\Entities\SalesActivity;

class InvoiceRepository implements InvoiceInterface
{
    use LogActivityTrait;
    // List invoice theo customer
    public function getListByCustomer($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;

        // Lấy thông tin user
        $user = Auth::user();
        $userRoles = $user->roles->pluck('id')->toArray();
        $specialRoles = [6, 8, 10]; // Các role có quyền xem tất cả

        // Khởi tạo query
        $baseQuery = Invoice::leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->where('customers.id', '=', $id);

        // Nếu user không có quyền xem tất cả, lọc theo customerId của họ
        if (!array_intersect($userRoles, $specialRoles)) {
            $customerId = Customer::where('created_by', $user->id)->value('id');
            if ($customerId) {
                $baseQuery->where('invoices.customer_id', $customerId);
            } else {
                return collect(); // Trả về danh sách rỗng nếu user không có customerId
            }
        }

        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('invoices.due_date', 'like', '%' . $search . '%');
            });
        }

        $baseQuery = $baseQuery->with([
            'record',
            'customer:id,company',
            'project:id,name',
            'tags',
            'itemable.itemTax',
            'customFields:id,field_to,name',
            'customFieldsValues'
        ])->select('invoices.*')->orderBy('invoices.created_at', 'desc');

        if ($limit > 0) {
            $invoice = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $invoice = $baseQuery->get();
        }

        return $invoice;
    }

    // List invoice
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $search = isset($request["search"]) ? $request["search"] : null;

        // Lấy thông tin user
        $user = Auth::user();
        $userRoles = $user->roles->pluck('id')->toArray();
        $specialRoles = [6, 8, 10]; // Các role có quyền xem tất cả

        // 
        $baseQuery = Invoice::query();

        // Nếu user không có quyền xem tất cả, lọc theo customer_id của họ
        if (!array_intersect($userRoles, $specialRoles)) {
            $customerId = Customer::where('created_by', $user->id)->value('id');
            if ($customerId) {
                $baseQuery->where('invoices.customer_id', $customerId);
            } else {
                return collect(); // Trả về danh sách rỗng nếu user không có customerId
            }
        }

        // Thêm điều kiện tìm kiếm nếu có
        if ($search) {
            $baseQuery->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                ->leftJoin('projects', 'projects.id', '=', 'invoices.project_id')
                ->where(function ($q) use ($search) {
                    $q->where('customers.company', 'like', '%' . $search . '%')
                        ->orWhere('projects.name', 'like', '%' . $search . '%')
                        ->orWhere('invoices.total', 'like', '%' . $search . '%')
                        ->orWhere('invoices.total_tax', 'like', '%' . $search . '%')
                        ->orWhere('invoices.date', 'like', '%' . $search . '%')
                        ->orWhere('invoices.due_date', 'like', '%' . $search . '%');
                });
        }

        // Nạp sẵn các quan hệ cần thiết
        $baseQuery = $baseQuery->with([
            'record',
            'customer:id,company',
            'project:id,name',
            'tags',
            'itemable.itemTax',
            'customFields:id,field_to,name',
            'customFieldsValues'
        ])->select('invoices.*')->orderBy('invoices.created_at', 'desc');

        // Kiểm tra giới hạn phân trang
        if ($limit > 0) {
            $invoice = $baseQuery->paginate($limit);
        } else {
            $invoice = $baseQuery->get();
        }
        return $invoice;
    }

    // Thêm mới invoice theo customer
    public function createByCustomer($id, $request)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        $invoice = new Invoice($request);
        $invoice->id = $customer->id;
        $invoice->hash = md5($request['hash']);
        $invoice->datecreated = Carbon::now();
        $invoice->created_by = Auth::user()->id;
        $invoice->save();
        $this->createSaleActivity($invoice->id, 3, ActivityKey::CREATE_INVOICE_BY_CUSTOMER);
        if (isset($request['tag'])) {
            foreach ($request['tag'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $invoice->tags()->attach($tag['id'], ['rel_type' => 'invoice', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name',  $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $invoice->tags()->attach($tg->id, ['rel_type' => 'invoice', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        if (isset($request['item'])) {
            foreach ($request['item'] as $pItem) {
                $item = new Itemable($pItem);
                $item->rel_id = $invoice->id;
                $item->rel_type = 'invoice';
                $item->description = $pItem['description'];
                $item->long_description = $pItem['long_description'];
                $item->qty = $pItem['qty'];
                $item->rate = $pItem['rate'];
                $item->unit = $pItem['unit'];
                $item->item_order = $pItem['item_order'];
                $item->created_by = Auth::user()->id;
                $item->save();
                if (isset($pItem['customFieldsValues'])) {
                    foreach ($pItem['customFieldsValues'] as $cfValues) {
                        $customFields = new CustomFieldValue($cfValues);
                        $customFields->fieldid = $cfValues['fieldid'];
                        $customFields->rel_id = $item->id;
                        $customFields->value = $cfValues['value'];
                        $customFields->field_to = "items";
                        $customFields->save();
                    }
                }
                if (isset($item['item_tax'])) {
                    foreach ($item['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->itemid = $item->id;
                        $itemTax->rel_type = 'invoice';
                        $itemTax->rel_id = $invoice->id;
                        $itemTax->created_by = Auth::user()->id;
                        $itemTax->save();
                    }
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $invoice->id;
                $customFields->field_to = "invoice";
                $customFields->save();
            }
        }
        $data = Invoice::where('id', $invoice->id)->with(
            'record',
            'customer:id,company',
            'project:id,name',
            'tags',
            'item.itemTax',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        if (!$data) {
            return null;
        }
        return $data;
    }

    // Cập nhật invoice
    public function update($id, $request)
    {
        // Khai báo biến customFields
        $customFields = [];

        // Tìm hóa đơn theo ID
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return null;
        }
        $invoice->fill($request);
        $invoice->updated_by = Auth::user()->id;
        $invoice->save();
        $this->createSaleActivity($id, 3, ActivityKey::UPDATE_INVOICE);
        $invoice->taggable()->delete();

        // Xử lý thẻ mới nếu có
        if (isset($request['tags'])) {
            $invoice->tags()->detach();

            foreach ($request['tags'] as $tag) {
                if (isset($tag['id']) && isset($tag['tag_order'])) {
                    $invoice->tags()->attach($tag['id'], ['rel_type' => $tag['rel_type'], 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if (!$tg) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $invoice->tags()->attach($tg->id, ['rel_type' => 'invoice', 'tag_order' => isset($tag['tag_order']) ? $tag['tag_order'] : 0]);
                }
            }
        }

        // Xử lý các mặt hàng (items) nếu có
        $invoice_id = [];
        if (isset($request['itemable']) && $request['itemable']) {
            foreach ($request['itemable'] as $itemable) {
                $item = isset($itemable['id']) ? $itemable['id'] : 0;
                $itemableNew = Itemable::findOrNew($item);
                $itemableNew->fill($itemable);
                $itemableNew->rel_id = $invoice->id;
                $itemableNew->rel_type = 'invoice';
                $itemableNew->updated_by = Auth::user()->id;
                $itemableNew->save();
                $invoice_id[] = $itemableNew->id;

                // Xử lý các giá trị trường tùy chỉnh (custom fields)
                if (isset($itemable['customFieldsValues'])) {
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
                    CustomFieldValue::where('rel_id', $itemableNew->id)->whereNotIn('id', $customFields)->delete();
                }

                // Xử lý thuế của item nếu có
                if (isset($itemable['item_tax'])) {
                    foreach ($itemable['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->item_id = $invoice->id;
                        $itemTax->rel_type = 'invoice';
                        $itemTax->rel_id = $itemableNew->id;
                        $itemTax->updated_by = Auth::user()->id;
                        $itemTax->save();
                    }
                }
            }
            Itemable::where('rel_id', $invoice->id)->whereNotIn('id', $invoice_id)->delete();
        }

        // Xử lý các giá trị trường tùy chỉnh chung nếu có
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findOrNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $invoice->id;
                $customFieldsValues->field_to = "invoice";
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id', $invoice->id)->whereNotIn('id', $customFields)->delete();
        }

        // Kiểm tra và xử lý customFields nếu có
        if (isset($request['customFields']) && $request['customFields']) {
            foreach ($request['customFields'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : null;
                CustomField::updateOrCreate(
                    ['id' => $cfvId],
                    array_merge($cValues, ['field_to' => 'invoice'])
                );
            }
        }
        Log::debug($customFields);
        // Lấy dữ liệu hóa đơn sau khi cập nhật
        $data = Invoice::where('id', $invoice->id)->with(
            'record',
            'customer:id,company',
            'project:id,name',
            'tags',
            'itemable.itemTax',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();

        // Kiểm tra dữ liệu sau khi lấy
        if (!$data) {
            return null;
        }
        return $data;
    }

    // Xóa invoice
    public function destroy($id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return null;
        }
        $this->createSaleActivity($id, 3, ActivityKey::DELETE_INVOICE);
        $invoice->itemable()->delete();
        $invoice->delete();
        return $invoice;
    }

    // List invoice theo project
    public function getListByProject($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $search = isset($request["search"]) ? $request["search"] : null;

        // Lấy thông tin user hiện tại
        $user = Auth::user();
        $userRoles = $user->roles->pluck('id')->toArray();
        $specialRoles = [6, 8, 10]; // Các role có quyền xem tất cả

        // Query
        $baseQuery = Invoice::leftJoin('projects', 'projects.id', '=', 'invoices.project_id')
            ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->leftJoin('staff', 'staff.id', '=', 'invoices.sale_agent')
            ->where('projects.id', $id);

        // Nếu user không có role đặc biệt, chỉ cho xem invoice của chính họ
        if (!array_intersect($userRoles, $specialRoles)) {
            $baseQuery->where('invoices.sale_agent', $user->id);
        }

        // Áp dụng tìm kiếm nếu có
        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('invoices.due_date', 'like', '%' . $search . '%')
                    ->orWhere('customers.company', 'like', '%' . $search . '%')
                    ->orWhere('projects.name', 'like', '%' . $search . '%');
            });
        }

        // Lấy dữ liệu với các quan hệ
        $baseQuery->with('project:id,name', 'customer:id,company', 'tags:id,name')->select('invoices.*');

        // Phân trang hoặc lấy toàn bộ
        return ($limit > 0) ? $baseQuery->paginate($limit) : $baseQuery->get();
    }

    // List tiền invoice theo project ( filter theo năm)
    public function getListByYearProject($id, $request)
    {
        // status = 2 <=> OverDue
        // status = 4 <=> Paid
        $year = json_decode($request["year"]);
        $currency =  isset($request["currency"]) ? $request["currency"] : null;
        $overDue = DB::table('invoices')->leftJoin('projects', 'projects.id', '=', 'invoices.project_id')->where('projects.id', $id)
            ->whereIn(DB::raw("year(invoices.date)"), $year)
            ->where('invoices.status', 4)
            ->where('invoices.currency', $currency)
            ->sum('invoices.total');
        $paid = DB::table('invoices')->leftJoin('projects', 'projects.id', '=', 'invoices.project_id')->where('projects.id', $id)
            ->leftJoin('payment', 'payment.invoice_id', '=', 'invoices.id')
            ->whereIn(DB::raw("year(invoices.date)"), $year)
            ->where('invoices.currency', $currency)
            ->whereExists(function ($query) {
                $query->select("payment.invoice_id")
                    ->from('payment')
                    ->whereRaw('payment.invoice_id = invoices.id');
            })
            ->where('invoices.status', 2)
            ->sum('payment.amount');
        $outstanding = DB::table('invoices')->leftJoin('projects', 'projects.id', '=', 'invoices.project_id')->where('projects.id', $id)
            ->leftJoin('payment', 'payment.invoice_id', '=', 'invoices.id')
            ->whereIn(DB::raw("year(invoices.date)"), $year)
            ->where('invoices.currency', $currency)
            ->whereExists(function ($query) {
                $query->select("payment.invoice_id")
                    ->from('payment')
                    ->whereRaw('payment.invoice_id = invoices.id');
            })
            ->where('invoices.status', 1)
            ->orwhere('invoices.status', 3)
            ->sum('invoices.total');
        $data = ['overDue' => $overDue, 'paid' => $paid, 'outstanding' => $outstanding];
        return $data;
    }

    // List tiền invoice theo customer ( filter theo năm)
    public function getListByYearCustomer($id, $request)
    {
        $year = isset($request["year"]) && ctype_digit($request["year"]) ? (int)$request["year"] : date('Y');
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int)$request["limit"] : 10;

        $baseQuery = Invoice::where('invoices.customer_id', $id)
            ->whereYear('invoices.created_at', $year);

        if (!$baseQuery) {
            return null;
        }

        $baseQuery = $baseQuery->with(
            'customer:id,company',
            'project:id,name',
            'tags:id,name'
        )->select('invoices.*')->orderBy('invoices.created_at', 'desc');

        if ($limit > 0) {
            $invoices = $baseQuery->paginate($limit);
        } else {
            $invoices = $baseQuery->get();
        }
        return $invoices;
    }


    // Tính tổng invoice theo status
    public function countInvoiceByProject($id)
    {
        $total = Invoice::where('project_id', $id)->count();
        $unpaid = Invoice::where('project_id', $id)->where('status', 1)->count();
        $paid = Invoice::where('project_id', $id)->where('status', 2)->count();
        $partially = Invoice::where('project_id', $id)->where('status', 3)->count();
        $overDue = Invoice::where('project_id', $id)->where('status', 4)->count();
        $draft = Invoice::where('project_id', $id)->where('status', 5)->count();
        return ['total' => $total, 'unpaid' => $unpaid, 'paid' => $paid, 'partially' => $partially, 'overdue' => $overDue, 'draft' => $draft];
    }

    //Tạo mới invoice
    public function create($request)
    {
        $invoice = new Invoice($request);
        $invoice->hash = md5($request['hash']);
        $invoice->created_by = Auth::id();
        $invoice->save();
        $this->createSaleActivity($invoice->id, 3, ActivityKey::CREATE_INVOICE);
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $invoice->tags()->attach($tag['id'], ['rel_type' => 'invoice', 'tag_order' => $tag['tag_order'] ?? null]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $invoice->tags()->attach($tg->id, ['rel_type' => 'invoice', 'tag_order' => $tag['tag_order'] ?? null]);
                }
            }
        }

        if (isset($request['itemable'])) {
            foreach ($request['itemable'] as $pItem) {
                $item = new Itemable();
                $item->rel_id = $invoice->id;
                $item->rel_type = 'invoice';
                $item->description = $pItem['description'];
                $item->long_description = $pItem['long_description'];
                $item->qty = $pItem['qty'];
                $item->rate = $pItem['rate'];
                $item->unit = $pItem['unit'];
                $item->item_order = $pItem['item_order'];
                $item->created_by = Auth::user()->id;
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
                if (isset($pItem['item_tax']) && !empty($pItem['item_tax'])) {
                    foreach ($pItem['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->item_id = $item->id;
                        $itemTax->rel_type = 'invoice';
                        $itemTax->rel_id = $invoice->id;
                        $itemTax->created_by = Auth::user()->id;
                        $itemTax->save();
                    }
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $invoice->id;
                $customFields->field_to = "invoice";
                $customFields->save();
            }
        }
        if (isset($request['customFields'])) {
            foreach ($request['customFields'] as $cfValues) {
                $customField = new CustomField($cfValues);
                $customField->id = $invoice->id;
                $customField->field_to = "invoice";
                $customField->save();
            }
        }
        $data = Invoice::where('id', $invoice->id)->with(
            'record',
            'customer:id,company',
            'project:id,name',
            'tags',
            'itemable.itemTax',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        Log::debug($data);

        if (!$data) {
            return null;
        }
        return $data;
    }

    //Get danh sách RecuringInvoice
    public function getListRecuringInvoice($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;

        $baseQuery = Invoice::query();
        if ($search) {
            $baseQuery = $baseQuery->join('customers', 'customers.id', '=', 'invoices.id')->orWhere('customers.company', 'like', '%' . $search . '%')
                ->join('projects', 'projects.id', '=', 'invoices.project_id')->orWhere('projects.name', $search)
                ->orWhere('total', 'like',  '%' . $search . '%');
        }
        $invoice = $baseQuery->with('record', 'customer', 'project', 'tags');

        if ($limit > 0) {
            $invoice = $baseQuery->paginate($limit);
        } else {
            $invoice = $baseQuery->whereNotIn('recurring', [0])->get();
        }

        return $invoice;
    }

    //Tìm kiếm invoice theo id
    public function findId($id)
    {
        $invoice = Invoice::with(
            'record',
            'customer:id,company',
            'project:id,name',
            'tags',
            'itemable.itemTax',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->find($id);
        if (!$invoice) {
            return null;
        }
        return $invoice;
    }

    //Thanh toán invoice
    public function payment($id, $request)
    {
        $invoice = Invoice::find($id);
        $amountInvoices = $invoice->total;
        if (!$invoice) {
            return null;
        }

        $payment = new Payment($request);
        $payment->invoice_id = $id;
        $payment->daterecorded = Carbon::now();
        $payment->save();
        $this->createSaleActivity($id, 3, ActivityKey::CREATE_PAYMENT_BY_INVOICE);
        $amountPayment = Payment::where('invoice_id', $id)->sum('payment.amount');
        if ($amountPayment < $amountInvoices) {
            $invoice->status = 3;
            $invoice->save();
            return $invoice;
        } else {
            $invoice->status = 2;
            $invoice->save();
            return $invoice;
        }
    }

    // Thống kê số hóa đơn của 1 khách hàng
    public function invoiceWithCustomers($id, $request)
    {
        $invoices = Invoice::where('customerId', '=', $id)->whereNotIn('status', [2, 3, 5])->get();
        $expense = Expenses::where('customerId', '=', $id)->get();
        $merges = isset($request["id"]) ? json_decode($request["id"]) : null;
        $cancel = isset($request["cancel"]) ? $request["cancel"] : null;
        if ($cancel === 1) {
            foreach ($invoices as $invoice) {
                if (isset($merges)) {
                    foreach ($merges as $merge) {
                        if ($invoice['id'] === $merge) {
                            $invoice->whereIn('id', $merges)->update(array('status' => 5));
                        }
                    }
                }
            }
        } else if (($cancel === 0)) {
            if (isset($merges)) {
                Invoice::whereIn('id', $merges)->delete();
            }
        }
        return ['invoice' => $invoices, 'expense' => $expense];
    }

    // Convert estimate sang invoice
    public function convertEstimateToInvoice($id, $request)
    {
        $invoice = $this->create($request);
        $newInvoice = $invoice->original['result'][0]->id;
        $estimate = Estimate::find($id);
        $estimate->invoice_id = $newInvoice;
        $estimate->invoiced_date = Carbon::now();
        $estimate->created_by = Auth::user()->id;
        $estimate->status = 5;
        $estimate->save();
        $this->createSaleActivity($invoice->id, 3, ActivityKey::CONVERT_ESTIMATE_BY_INVOICE);
        return $estimate;
    }

    //Convert proposal sang invoice
    public function convertProposalToInvoice($id, $request)
    {
        $invoice = $this->create($request);
        $newInvoice = $invoice->original['result'][0]->id;
        $proposal = Proposal::find($id);
        $proposal->invoice_id = $newInvoice;
        $proposal->status = 6;
        $proposal->created_by = Auth::user()->id;
        $proposal->save();
        $this->createSaleActivity($invoice->id, 3, ActivityKey::CONVERT_PROPOSAL_BY_INVOICE);
        return $proposal;
    }

    //Copy data invoice
    public function copyData($id, $request)
    {
        //copy dữ liệu Proposal
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return null;
        }
        $newInvoice = $invoice->replicate();
        $newInvoice->save();
        $this->createSaleActivity($id, 3, ActivityKey::COPY_INVOICE);
        //copy Tag
        $tag = Taggables::where('rel_type', 'invoice')
            ->where('rel_id', $id)->first();
        if ($tag) {
            $newTag = $tag->replicate();
            $newTag->rel_id = $newInvoice->id;
            $newTag->save();
        }
        //copy Itemable
        $itemable = Itemable::where('rel_id', $id)->first();
        if ($itemable) {
            $newItemAble = $itemable->replicate();
            $newItemAble->rel_id = $newInvoice->id;
            $newItemAble->save();
        }
        //copy Customfield
        $customField = CustomFieldValue::where('rel_id', $id)->first();
        if ($customField) {
            $newCustomField = $customField->replicate();
            $newCustomField->rel_id = $newInvoice->id;
            $newCustomField->save();
        }
        $data = Proposal::where('id', $newInvoice->id)->with('itemable', 'tags', 'customFields:id,field_to,name', 'customFieldsValues')->get();
        return $data;
    }

    public function filterByInvoice($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 0;
        $notSent =  isset($request["notSent"]) ? (int)$request["notSent"] : 0;
        $record =  isset($request["record"]) ? (int)$request["record"] : 0;
        $status =  isset($request["status"]) ? json_decode($request["status"]) : null;
        $recurring =  isset($request["recurring"]) ? $request["recurring"] : 0;
        $year =  isset($request["year"]) ? json_decode($request["year"]) : null;
        $sale =  isset($request["sale"]) ? json_decode($request["sale"]) : null;
        $invoice = Invoice::leftJoin('payment', 'payment.invoice_id', '=', 'invoices.id');
        $invoice = $invoice
            ->when(!empty($notSent), function ($query) use ($notSent) {
                if ($notSent === 1) {
                    return $query->where('invoices.sent', 0);
                }
            })
            ->when(!empty($record), function ($query) use ($record) {
                if ($record === 1) {
                    return $query->whereNotExists(function ($query) {
                        $query->select("payment.invoice_id")
                            ->from('payment')
                            ->whereRaw('payment.invoice_id = invoices.id');
                    });
                }
            })
            ->when(!empty($recurring), function ($query) use ($recurring) {
                if ($recurring === 1) {
                    return $query->where('invoices.recurring', $recurring);
                }
            })
            ->when(!empty($status), function ($query) use ($status) {
                return $query->whereIn('invoices.status', $status);
            })
            ->when(!empty($year), function ($query) use ($year) {
                return $query->whereIn(DB::raw("year(invoices.date)"), $year);
            })
            ->when(!empty($sale), function ($query) use ($sale) {
                return $query->whereIn('invoices.sale_agent', $sale);
            });

        $invoice = $invoice->with(
            'record',
            'customer:id,company',
            'project:id,name',
            'tags',
            'item',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->select('invoices.*')->distinct()->orderBy('invoices.created_at', 'desc');;
        if ($limit > 0) {
            $invoice = $invoice->paginate($limit, ['*'], 'page', $page);
        } else {
            $invoice = $invoice->get();
        }
        return $invoice;
    }

    // filter invoice theo project
    public function filterInvoiceByProject($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($queryData["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 0;
        $notSent =  isset($request["notSent"]) ? (int)$request["notSent"] : 0;
        $record =  isset($request["record"]) ? (int)$request["record"] : 0;
        $status =  isset($request["status"]) ? json_decode($request["status"]) : null;
        $recurring =  isset($request["recurring"]) ? $request["recurring"] : 0;
        $year =  isset($request["year"]) ? json_decode($request["year"]) : null;
        $sale =  isset($request["sale"]) ? json_decode($request["sale"]) : null;
        $invoice = Invoice::leftJoin('payment', 'payment.invoice_id', '=', 'invoices.id')
            ->where('invoices.project_id', $id);
        $invoice = $invoice
            ->when(!empty($notSent), function ($query) use ($notSent) {
                if ($notSent === 1) {
                    return $query->where('invoices.sent', 0);
                }
            })
            ->when(!empty($record), function ($query) use ($record) {
                if ($record === 1) {
                    return $query->whereNotExists(function ($query) {
                        $query->select("payment.invoice_id")
                            ->from('payment')
                            ->whereRaw('payment.invoice_id = invoices.id');
                    });
                }
            })
            ->when(!empty($recurring), function ($query) use ($recurring) {
                if ($recurring === 1) {
                    return $query->where('invoices.recurring', $recurring);
                }
            })
            ->when(!empty($status), function ($query) use ($status) {
                return $query->whereIn('invoices.status', $status);
            })
            ->when(!empty($year), function ($query) use ($year) {
                return $query->whereIn(DB::raw("year(invoices.date)"), $year);
            })
            ->when(!empty($sale), function ($query) use ($sale) {
                return $query->whereIn('invoices.sale_agent', $sale);
            });

        $invoice = $invoice->with(
            'record',
            'customer:id,company',
            'project:id,name',
            'tags',
            'item.itemTax',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->select('invoices.*')->distinct()->orderBy('invoices.created_at', 'desc');;
        if ($limit > 0) {
            $invoice = $invoice->paginate($limit, ['*'], 'page', $page);
        } else {
            $invoice = $invoice->get();
        }
        return $invoice;
    }

    public function countInvoiceByCustomer($id)
    {
        $total = Invoice::where('customer_id', $id)->count();
        $unpaid = Invoice::where('customer_id', $id)->where('status', 1)->count();
        $paid = Invoice::where('customer_id', $id)->where('status', 2)->count();
        $partially = Invoice::where('customer_id', $id)->where('status', 3)->count();
        $overDue = Invoice::where('customer_id', $id)->where('status', 4)->count();
        $draft = Invoice::where('customer_id', $id)->where('status', 5)->count();
        return ['total' => $total, 'unpaid' => $unpaid, 'paid' => $paid, 'partially' => $partially, 'overdue' => $overDue, 'draft' => $draft];
    }

    // List tiền invoice ( filter theo năm)
    public function getListByYear($request)
    {
        $year = isset($request["year"]) ? json_decode($request["year"]) : null;
        $overDue = DB::table('invoices')
            ->whereIn(DB::raw("year(invoices.date)"), $year)
            ->where('invoices.status', 4)
            ->sum('invoices.total');
        $paid = DB::table('invoices')
            ->leftJoin('payment', 'payment.invoice_id', '=', 'invoices.id')
            ->whereIn(DB::raw("year(invoices.date)"), $year)
            ->whereExists(function ($query) {
                $query->select("payment.invoice_id")
                    ->from('payment')
                    ->whereRaw('payment.invoice_id = invoices.id');
            })
            ->where('invoices.status', 2)
            ->sum('payment.amount');
        $outstanding = DB::table('invoices')
            ->leftJoin('payment', 'payment.invoice_id', '=', 'invoices.id')
            ->whereIn(DB::raw("year(invoices.date)"), $year)
            ->whereExists(function ($query) {
                $query->select("payment.invoice_id")
                    ->from('payment')
                    ->whereRaw('payment.invoice_id = invoices.id');
            })
            ->where('invoices.status', 1)
            ->orwhere('invoices.status', 3)
            ->sum('invoices.total');
        $data = ['overDue' => $overDue, 'paid' => $paid, 'outstanding' => $outstanding];
        return $data;
    }
}

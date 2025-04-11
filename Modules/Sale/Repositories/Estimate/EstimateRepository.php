<?php

namespace Modules\Sale\Repositories\Estimate;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomField;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Tag;
use Modules\Sale\Entities\Estimate;
use Modules\Customer\Entities\Taggables;
use Modules\Sale\Entities\Itemable;
use Modules\Sale\Entities\ItemTax;
use Modules\Sale\Entities\Proposal;


class EstimateRepository implements EstimateInterface
{
    use LogActivityTrait;

    // List báo giá theo id của estimate
    public function findId($id)
    {
        $estimate = Estimate::with(['itemable.itemTax', 'customFieldsValues', 'customFields', 'taggable', 'tags', 'customer:id,company', 'saleAgent:id,first_name,last_name'])->find($id);
        return $estimate;
    }

    // List báo giá
    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $status = isset($request['status']) ? $request['status'] : null;

        // Lấy thông tin user
        $user = Auth::user();
        $userRoles = $user->roles->pluck('id')->toArray();
        $specialRoles = [6, 8, 10]; // Các role có quyền xem tất cả

        // Khởi tạo query
        $baseQuery = Estimate::query();

        // Nếu user không có role đặc biệt, lọc theo customerId của họ
        if (!array_intersect($userRoles, $specialRoles)) {
            $customerId = Customer::where('user_id', $user->id)->value('id'); // Lấy customerId từ user_id
            if ($customerId) {
                $baseQuery->where('estimates.customerId', $customerId);
            } else {
                return collect(); // Trả về danh sách rỗng nếu không tìm thấy customerId
            }
        }

        // Nếu có tìm kiếm thì áp dụng filter
        if ($search) {
            $baseQuery->leftJoin('customers', 'customers.id', '=', 'estimates.customerId')
                ->leftJoin('projects', 'projects.id', '=', 'estimates.project_id')
                ->where(function ($q) use ($search) {
                    $q->where('customers.company', 'like', '%' . $search . '%')
                        ->orWhere('projects.name', 'like', '%' . $search . '%')
                        ->orWhere('estimates.total', 'like', '%' . $search . '%')
                        ->orWhere('estimates.date', 'like', '%' . $search . '%')
                        ->orWhere('estimates.expiry_date', 'like', '%' . $search . '%');
                });
        }

        // Lọc theo trạng thái nếu có
        if ($status) {
            $baseQuery->where('estimates.status', $status);
        }

        // Nạp sẵn các quan hệ cần thiết
        $estimate = $baseQuery->with([
            'customer:id,company',
            'project:id,name',
            'itemable.itemTax',
            'tags:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues'
        ])->select('estimates.*')->orderBy('estimates.created_at', 'desc');

        // Kiểm tra giới hạn phân trang
        if ($limit > 0) {
            return $estimate->paginate($limit);
        }

        return $estimate->get();
    }

    // List báo giá theo customer
    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 10;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $search = isset($request['search']) ? $request['search'] : null;

        // Lấy thông tin user và danh sách role của họ
        $user = Auth::user();
        $userRoles = $user->roles->pluck('id')->toArray();
        $specialRoles = [6, 8, 10]; // Các role có quyền xem tất cả

        // Tạo query
        $baseQuery = Estimate::leftJoin('customers', 'customers.id', '=', 'estimates.customer_id')
            ->leftJoin('projects', 'projects.id', '=', 'estimates.project_id');

        // Nếu user không có role đặc biệt, thì chỉ xem danh sách theo customer_id
        if (!array_intersect($userRoles, $specialRoles)) {
            $baseQuery->where('customers.id', '=', $id);
        }

        // Nếu có tìm kiếm thì áp dụng filter
        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('estimates.total', 'like', '%' . $search . '%')
                    ->orWhere('projects.name', 'like', '%' . $search . '%')
                    ->orWhere('estimates.date', 'like', '%' . $search . '%')
                    ->orWhere('estimates.expiry_date', 'like', '%' . $search . '%');
            });
        }

        // Nạp sẵn các quan hệ cần thiết
        $estimate = $baseQuery->with([
            'project:id,name',
            'customer:id,company',
            'tags:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues',
            'taggable',
            'itemable.itemTax'
        ])->select('estimates.*')->orderBy('estimates.created_at', 'desc');

        // Kiểm tra giới hạn phân trang
        if ($limit > 0) {
            return $estimate->paginate($limit, ['*'], 'page', $page);
        }

        return $estimate->get();
    }

    public function getListByItemable($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $baseQuery = Estimate::join('itemable', 'itemable.rel_id', '=', 'estimates.id')->where('itemable.rel_id', '=', $id)->where('itemable.rel_type', '=', 'estimate');

        if (!$baseQuery) {
            return null;
        }

        $estimate = $baseQuery->with('itemable.itemTax', 'customFields:id,field_to,name', 'customFieldsValues')->select('estimates.*');

        if ($limit > 0) {
            $estimate = $baseQuery->paginate($limit);
        } else {
            $estimate = $baseQuery->get();
        }

        return $estimate;
    }

    // Thêm mới báo giá
    public function create($request)
    {

        // Creating the estimate
        $estimate = new Estimate();
        $estimate->fill($request);
        $estimate->created_by = Auth::id();
        $estimate->save();

        // Handling itemable data
        if (isset($request['itemable'])) {
            foreach ($request['itemable'] as $item) {
                $itemable = new Itemable();
                $itemable->description = $item['description'];
                $itemable->long_description = $item['long_description'];
                $itemable->rate = $item['rate'];
                $itemable->qty = $item['qty'];
                $itemable->item_order = $item['item_order'];
                $itemable->unit = $item['unit'];
                $itemable->rel_id = $estimate->id;
                $itemable->rel_type = 'estimate';
                $itemable->created_by = Auth::user()->id;
                $itemable->save();

                // Handle custom fields for itemable
                if (isset($item['custom_fields'])) {
                    foreach ($item['custom_fields'] as $cfValues) {
                        $customField = CustomField::firstOrCreate(
                            ['name' => $cfValues['name']],
                            [
                                'field_to' => 'items',
                                'slug' => $cfValues['slug'] ?? null,
                                'type' => $cfValues['type'] ?? 'text',
                                'created_by' => Auth::user()->id,
                            ]
                        );
                        $customFieldValue = new CustomFieldValue();
                        $customFieldValue->field_id = $customField->id;
                        $customFieldValue->rel_id = $itemable->id;
                        $customFieldValue->value = $cfValues['value'] ?? null;
                        $customFieldValue->field_to = 'items';
                        $customFieldValue->created_by = Auth::user()->id;
                        $customFieldValue->save();
                        Log::debug('Custom field value saved with ID: ' . $customFieldValue->id);
                    }
                }

                // Handle item tax for itemable
                if (isset($item['item_tax'])) {
                    foreach ($item['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->item_id = $itemable->id;
                        $itemTax->rel_type = 'estimate';
                        $itemTax->rel_id = $estimate->id;
                        $itemTax->tax_rate = $iTax['tax_rate'] ?? 0;
                        $itemTax->tax_name = $iTax['tax_name'] ?? null;
                        $itemTax->tax_amount = $iTax['tax_amount'] ?? 0;
                        $itemTax->created_by = Auth::user()->id;
                        $itemTax->save();
                    }
                }
            }
        }

        // Handle tags for estimate
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $estimate->tags()->attach($tag['id'], ['rel_type' => 'estimate', 'tag_order' => $tag['tag_order'] ?? null]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $estimate->tags()->attach($tg->id, ['rel_type' => 'estimate', 'tag_order' => $tag['tag_order'] ?? null]);
                }
            }
        }

        // Handle custom fields for estimate
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                if (isset($cfValues['field_id'])) {
                    $customField = CustomFieldValue::create([
                        'field_id' => $cfValues['field_id'],
                        'rel_id' => $estimate->id,
                        'value' => $cfValues['value'] ?? '',
                        'field_to' => 'estimate',
                    ]);
                }
            }
        }

        // Create sale activity
        $this->createSaleActivity($estimate->id, 2, ActivityKey::CREATE_ESTIMATE);

        $data = Estimate::where('id', $estimate->id)
            ->with('itemable.itemTax', 'customFields:id,field_to,name', 'customFieldsValues', 'tags')
            ->get();

        return $data;
    }

    //Thêm mới estimate theo customer
    public function createByCustomer($id, $request)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        $estimate = new Estimate($request);
        $estimate->customer_id = $customer->id;
        $estimate->created_by = Auth::user()->id;
        $estimate->save();
        if (isset($request['itemable'])) {
            foreach ($request['itemable'] as $item) {
                $itemable = new Itemable($item);
                $itemable->rel_id = $estimate->id;
                $itemable->rel_type = 'estimate';
                $itemable->created_by = Auth::user()->id;
                $itemable->save();
                if (isset($item['customFieldsValues'])) {
                    foreach ($item['customFieldsValues'] as $cfValues) {
                        $customFields = new CustomFieldValue($cfValues);
                        $customFields->field_id = $cfValues['field_id'];
                        $customFields->rel_id = $itemable->id;
                        $customFields->value = $cfValues['value'];
                        $customFields->field_to = 'items';
                        $customFields->save();
                    }
                }
                if (isset($item['item_tax'])) {
                    foreach ($item['item_tax'] as $iTax) {
                        $itemTax = new ItemTax($iTax);
                        $itemTax->item_id = $estimate->id;
                        $itemTax->rel_type = 'estimate';
                        $itemTax->rel_id = $itemable->id;
                        $itemTax->created_by = Auth::user()->id;
                        $itemTax->save();
                    }
                }
            }
        }
        if (isset($request['tag'])) {
            foreach ($request['tag'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $estimate->tags()->attach($tag['id'], ['rel_type' => 'estimate', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $estimate->tags()->attach($tg->id, ['rel_type' => 'estimate', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $estimate->id;
                $customFields->field_to = 'estimate';
                $customFields->save();
            }
        }

        $this->createSaleActivity($estimate->id, 2, ActivityKey::CREATE_ESTIMATE_BY_CUSTOMER);

        $data = Estimate::where('id', $estimate->id)
            ->with('itemable.itemTax', 'customFields:id,field_to,name', 'customFieldsValues')
            ->get();

        return $data;
    }

    // Cập nhật báo giá
    public function update($id, $request)
    {
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return null;
        }
        $estimate->fill($request->all());
        $estimate->updated_by = Auth::id();
        $estimate->save();

        // Lấy danh sách itemable ID từ request
        $itemableIds = [];
        if (isset($request['itemable']) && !empty($request['itemable'])) {
            foreach ($request['itemable'] as $item) {
                $item['rel_id'] = $estimate->id;
                $itemable = Itemable::updateOrCreate(
                    [
                        'description' => $item['description'],
                        'long_description' => $item['long_description'],
                        'rate' => $item['rate'],
                        'qty' => $item['qty'],
                        'item_order' => $item['item_order'],
                        'unit' => $item['unit'],
                        'rel_id' => $estimate->id,
                        'rel_type' => 'estimate'
                    ],
                    [
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]
                );
                $itemable->save();
                $itemableIds[] = $itemable->id;

                // Xóa itemtax cũ của itemable
                ItemTax::where('item_id', $itemable->id)->delete();

                // Thêm mới itemtax
                if (isset($item['item_tax'])) {
                    foreach ($item['item_tax'] as $iTax) {
                        ItemTax::create([
                            'item_id' => $itemable->id,
                            'rel_id' => $estimate->id,
                            'rel_type' => 'estimate',
                            'tax_rate' => $iTax['tax_rate'],
                            'tax_name' => $iTax['tax_name'],
                            'tax_amount' => $iTax['tax_amount'],
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);
                    }
                }
            }
        }
        $deletedItemables = Itemable::where('rel_id', $estimate->id)
            ->whereNotIn('id', $itemableIds)
            ->get();

        foreach ($deletedItemables as $deletedItemable) {
            ItemTax::where('item_id', $deletedItemable->id)->delete();
            $deletedItemable->delete();
        }

        // Xử lý tags
        $estimate->tags()->sync([]);
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $tag) {
                $tagOrder = $tag['tag_order'] ?? 0;
                if (isset($tag['id'])) {
                    $estimate->tags()->attach($tag['id'], ['rel_type' => 'estimate', 'tag_order' => $tagOrder]);
                } else {
                    $tg = Tag::firstOrCreate(['name' => $tag['name']], $tag);
                    $estimate->tags()->attach($tg->id, ['rel_type' => 'estimate', 'tag_order' => $tagOrder]);
                }
            }
        }

        // Xử lý custom fields
        $customFieldIds = [];
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                if (isset($cfValues['field_id'])) {
                    $customField = CustomFieldValue::updateOrCreate(
                        ['id' => $cfValues['id'] ?? null],
                        [
                            'field_id' => $cfValues['field_id'],
                            'rel_id' => $estimate->id,
                            'value' => $cfValues['value'] ?? '',
                            'field_to' => 'estimate'
                        ]
                    );
                    $customFieldIds[] = $customField->id;
                }
            }
            CustomFieldValue::where('rel_id', $estimate->id)
                ->whereNotIn('id', $customFieldIds)
                ->delete();
        }

        $data = Estimate::with(['itemable.itemTax', 'tags', 'customFieldsValues'])->find($estimate->id);
        return $data;
    }

    // Xóa báo giá
    public function destroy($id)
    {
        $estimate = Estimate::findOrFail($id);
        $this->createSaleActivity($id, 2, ActivityKey::DELETE_ESTIMATE);
        $estimate->itemable()->delete();
        $estimate->delete();
        return $estimate;
    }

    // Đếm báo giá theo customer
    public function countByCustomer($id)
    {
        // status = 1 <=> Draft
        // status = 2 <=> Sent
        // status = 3 <=> Expired
        // status = 4 <=> Declined
        // status = 5 <=> Accepted
        $countDraft = Estimate::where('customerId', $id)->where('status', 1)->sum('total');
        $countSent = Estimate::where('customerId', $id)->where('status', 2)->sum('total');
        $countExpired = Estimate::where('customerId', $id)->where('status', 3)->sum('total');
        $countDeclined = Estimate::where('customerId', $id)->where('status', 4)->sum('total');
        $countAccepted = Estimate::where('customerId', $id)->where('status', 5)->sum('total');

        return [
            'Draft' => $countDraft,
            'Sent' => $countSent,
            'Expired' => $countExpired,
            'Declined' => $countDeclined,
            'Accepted' => $countAccepted,
        ];
    }

    // Đếm báo giá theo project
    public function countEstimateByProject($id)
    {
        // status = 1 <=> Draft
        // status = 2 <=> Sent
        // status = 3 <=> Expired
        // status = 4 <=> Declined
        // status = 5 <=> Accepted
        $total = Estimate::where('project_id', $id)->count();
        $draft = Estimate::where('project_id', $id)->where('status', 1)->count();
        $sent = Estimate::where('project_id', $id)->where('status', 2)->count();
        $expired = Estimate::where('project_id', $id)->where('status', 3)->count();
        $declined = Estimate::where('project_id', $id)->where('status', 4)->count();
        $accepted = Estimate::where('project_id', $id)->where('status', 5)->count();
        return [
            'total' => $total,
            'draft' => $draft,
            'sent' => $sent,
            'expired' => $expired,
            'declined' => $declined,
            'accepted' => $accepted,
        ];
    }

    // List estimaste theo Project
    public function getListByProject($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;

        // Lấy thông tin user
        $user = Auth::user();
        $userRoles = $user->roles->pluck('id')->toArray();
        $specialRoles = [6, 8, 10]; // Các role có quyền xem tất cả

        // Query
        $baseQuery = Estimate::leftJoin('projects', 'estimates.project_id', '=', 'projects.id')
            ->leftJoin('customers', 'customers.id', '=', 'estimates.customer_id')
            ->where('projects.id', $id);

        // Nếu user không có quyền xem tất cả, lọc theo customerId của họ
        if (!array_intersect($userRoles, $specialRoles)) {
            $customerId = Customer::where('user_id', $user->id)->value('id'); // Lấy customerId từ user_id
            if ($customerId) {
                $baseQuery->where('estimates.customer_id', $customerId);
            } else {
                return null; // Trả về danh sách rỗng nếu không tìm thấy customerId
            }
        }

        // Nếu có tìm kiếm thì áp dụng filter
        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('customers.company', 'like', '%' . $search . '%')
                    ->orWhere('projects.name', 'like', '%' . $search . '%')
                    ->orWhere('estimates.date', 'like', '%' . $search . '%')
                    ->orWhere('estimates.expiry_date', 'like', '%' . $search . '%');
            });
        }

        $baseQuery = $baseQuery->with([
            'project:id,name',
            'customer:id,company',
            'tags:id,name',
            'taggable',
            'itemable'
        ])->select('estimates.*');

        // Kiểm tra giới hạn phân trang
        if ($limit > 0) {
            $estimate = $baseQuery->paginate($limit);
        } else {
            $estimate = $baseQuery->get();
        }

        return $estimate;
    }

    public function getListByYearProject($id, $request)
    {
        // status = 1 <=> Draft
        // status = 2 <=> Sent
        // status = 3 <=> Expired
        // status = 4 <=> Declined
        // status = 5 <=> Accepted
        $year = isset($request['year']) ? json_decode($request['year']) : null;
        $baseQuery = DB::table('estimates')->leftJoin('projects', 'projects.id', '=', 'estimates.project_id')->where('projects.id', $id);
        if (!is_null($year)) {
            $baseQuery->whereIn(DB::raw('year(estimates.date)'), $year);
        }
        $statuses = [
            'draft' => 1,
            'sent' => 2,
            'expired' => 3,
            'declined' => 4,
            'accepted' => 5,
        ];
        $data = [];
        foreach ($statuses as $key => $status) {
            $data[$key] = DB::table('estimates')
                ->leftJoin('projects', 'projects.id', '=', 'estimates.project_id')
                ->where('projects.id', $id)
                ->when($year, function ($query) use ($year) {
                    $query->whereIn(DB::raw('year(estimates.date)'), $year);
                })
                ->where('estimates.status', $status)
                ->sum('estimates.total');
        }
        return $data;
    }

    // Tính tiền estimates theo customer (truyền vào năm)
    public function getListByYearCustomer($id, $request)
    {
        // status = 1 <=> Draft
        // status = 2 <=> Sent
        // status = 3 <=> Expired
        // status = 4 <=> Declined
        // status = 5 <=> Accepted
        $year = isset($request['year']) ? json_decode($request['year']) : null;
        $draft = DB::table('estimates')->leftJoin('customers', 'customers.id', '=', 'estimates.customer_id')->where('customers.id', $id)->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 1)->sum('estimates.total');
        $sent = DB::table('estimates')->leftJoin('customers', 'customers.id', '=', 'estimates.customer_id')->where('customers.id', $id)->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 2)->sum('estimates.total');
        $expired = DB::table('estimates')->leftJoin('customers', 'customers.id', '=', 'estimates.customer_id')->where('customers.id', $id)->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 3)->sum('estimates.total');
        $declined = DB::table('estimates')->leftJoin('customers', 'customers.id', '=', 'estimates.customer_id')->where('customers.id', $id)->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 4)->sum('estimates.total');
        $accepted = DB::table('estimates')->leftJoin('customers', 'customers.id', '=', 'estimates.customer_id')->where('customers.id', $id)->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 5)->sum('estimates.total');
        $data = ['draft' => $draft, 'sent' => $sent, 'expired' => $expired, 'declined' => $declined, 'accepted' => $accepted];
        return $data;
    }

    //Thay đổi status
    public function changeStatus($id, $request)
    {
        $estimate = Estimate::where('id', $id)->first();
        $estimate->save();
        $this->createSaleActivity($id, 2, ActivityKey::CHANGE_STATUS_BY_ESTIMATE);
        return $estimate;
    }

    //Sao chép dữ liệu của Estimate
    public function copyData($id)
    {
        //copy dữ liệu Estimate
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return null;
        }
        $newEstimate = $estimate->replicate();
        $newEstimate->save();
        $this->createSaleActivity($id, 2, ActivityKey::COPY_ESTIMATE);
        //copy Tag
        $tag = Taggables::where('rel_type', 'estimate')->where('rel_id', $id)->first();
        if ($tag) {
            $newTag = $tag->replicate();
            $newTag->rel_id = $newEstimate->id;
            $newTag->save();
        }
        //copy Itemable
        $itemable = Itemable::where('rel_id', $id)->first();
        if ($itemable) {
            $newItemAble = $itemable->replicate();
            $newItemAble->rel_id = $newEstimate->id;
            $newItemAble->save();
        }
        //copy Customfield
        $customField = CustomFieldValue::where('rel_id', $id)->first();
        if ($customField) {
            $newCustomField = $customField->replicate();
            $newCustomField->rel_id = $newEstimate->id;
            $newCustomField->save();
        }

        $data = Estimate::where('id', $newEstimate->id)
            ->with('customer:id,company', 'project:id,name', 'itemable.itemTax', 'tags:id,name', 'customFields:id,field_to,name', 'customFieldsValues')
            ->get();
        return $data;
    }

    //Convert Proposal sang Estimate
    public function convertProposalToEstimaste($id, $request)
    {
        $estimate = $this->create($request);
        $newEstimate = $estimate->original['result'][0]->id;
        $proposal = Proposal::find($id);
        $proposal->estimate_id = $newEstimate;
        $proposal->status = 6;
        $proposal->save();
        return $proposal;
    }

    //Đếm tổng tiền của Estimate theo status
    public function countByStatus()
    {
        // status = 1 <=> Draft
        // status = 2 <=> Sent
        // status = 3 <=> Expired
        // status = 4 <=> Declined
        // status = 5 <=> Accepted
        $draft = Estimate::where('status', 1)->count();
        $sent = Estimate::where('status', 2)->count();
        $expired = Estimate::where('status', 3)->count();
        $declined = Estimate::where('status', 4)->count();
        $accepted = Estimate::where('status', 5)->count();

        return [
            'draft' => $draft,
            'sent' => $sent,
            'expired' => $expired,
            'declined' => $declined,
            'accepted' => $accepted,
        ];
    }

    public function filterByEstimate($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 0;
        $notSent = isset($request['notSent']) ? (int) $request['notSent'] : 0;
        $invoice = isset($request['invoice']) ? (int) $request['invoice'] : 0;
        $notInvoice = isset($request['notInvoice']) ? (int) $request['notInvoice'] : 0;
        $status = isset($request['status']) ? json_decode($request['status']) : null;
        $year = isset($request['year']) ? json_decode($request['year']) : null;
        $sale = isset($request['sale']) ? json_decode($request['sale']) : null;
        $estimate = Estimate::leftJoin('staff', 'staff.staffid', '=', 'estimates.sale_agent')->leftJoin('invoices', 'invoices.id', '=', 'estimates.invoiceid');
        $estimate = $estimate
            ->when(!empty($status), function ($query) use ($status) {
                return $query->whereIn('estimates.status', $status);
            })
            ->when(!empty($year), function ($query) use ($year) {
                return $query->whereIn(DB::raw('year(estimates.date)'), $year);
            })
            ->when(!empty($sale), function ($query) use ($sale) {
                return $query->whereIn('estimates.sale_agent', $sale);
            })
            ->when(!empty($notSent), function ($query) use ($notSent) {
                if ($notSent === 1) {
                    return $query->where('estimates.sent', '=', 0);
                }
            })
            ->when(!empty($invoice), function ($query) use ($invoice) {
                if ($invoice === 1) {
                    return $query->whereNotNull('estimates.invoiceid');
                }
            })
            ->when(!empty($notInvoice), function ($query) use ($notInvoice) {
                if ($notInvoice === 1) {
                    return $query->whereNull('estimates.invoiceid');
                }
            });

        $estimate = $estimate->with('project:id,name', 'customer:id,company', 'tags:id,name', 'customFields:id,field_to,name', 'customFieldsValues')->select('estimates.*')->distinct()->orderBy('estimates.created_at', 'desc');
        if ($limit > 0) {
            $estimate = $estimate->paginate($limit, ['*'], 'page', $page);
        } else {
            $estimate = $estimate->get();
        }
        return $estimate;
    }

    public function filterEstimateByProject($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 0;
        $notSent = isset($request['notSent']) ? (int) $request['notSent'] : 0;
        $invoice = isset($request['invoice']) ? (int) $request['invoice'] : 0;
        $notInvoice = isset($request['notInvoice']) ? (int) $request['notInvoice'] : 0;
        $status = isset($request['status']) ? json_decode($request['status']) : null;
        $year = isset($request['year']) ? json_decode($request['year']) : null;
        $sale = isset($request['sale']) ? json_decode($request['sale']) : null;
        $estimate = Estimate::leftJoin('staff', 'staff.staffid', '=', 'estimates.sale_agent')->leftJoin('invoices', 'invoices.id', '=', 'estimates.invoiceid')->where('estimates.project_id', $id);
        $estimate = $estimate
            ->when(!empty($status), function ($query) use ($status) {
                return $query->whereIn('estimates.status', $status);
            })
            ->when(!empty($year), function ($query) use ($year) {
                return $query->whereIn(DB::raw('year(estimates.date)'), $year);
            })
            ->when(!empty($sale), function ($query) use ($sale) {
                return $query->whereIn('estimates.sale_agent', $sale);
            })
            ->when(!empty($notSent), function ($query) use ($notSent) {
                if ($notSent === 1) {
                    return $query->where('estimates.sent', '=', 0);
                }
            })
            ->where(function ($query) use ($invoice) {
                if ($invoice === 1) {
                    return $query->whereNotNull('estimates.invoiceid');
                }
            })
            ->orWhere(function ($query) use ($notInvoice) {
                if ($notInvoice === 1) {
                    return $query->whereNull('estimates.invoiceid');
                }
            });

        $estimate = $estimate->with('project:id,name', 'customer:id,company', 'tags:id,name', 'customFields:id,field_to,name', 'customFieldsValues')->select('estimates.*')->distinct()->orderBy('estimates.created_at', 'desc');
        if ($limit > 0) {
            $estimate = $estimate->paginate($limit, ['*'], 'page', $page);
        } else {
            $estimate = $estimate->get();
        }
        return $estimate;
    }

    // Tính tiền estimates(truyền vào năm)
    public function getListByYear($request)
    {
        $year = isset($request['year']) ? json_decode($request['year']) : null;
        $draft = DB::table('estimates')->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 1)->sum('estimates.total');
        $sent = DB::table('estimates')->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 2)->sum('estimates.total');
        $expired = DB::table('estimates')->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 3)->sum('estimates.total');
        $declined = DB::table('estimates')->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 4)->sum('estimates.total');
        $accepted = DB::table('estimates')->whereIn(DB::raw('year(estimates.date)'), $year)->where('estimates.status', 5)->sum('estimates.total');
        $data = ['draft' => $draft, 'sent' => $sent, 'expired' => $expired, 'declined' => $declined, 'accepted' => $accepted];
        return $data;
    }
}

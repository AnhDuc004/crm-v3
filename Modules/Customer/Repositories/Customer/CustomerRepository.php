<?php

namespace Modules\Customer\Repositories\Customer;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\Contact;
use Modules\Customer\Entities\CustomerGroups;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\Group;
use Modules\Sale\Entities\CreditNotes;
use Modules\Sale\Entities\CreditNotesRefunds;
use Modules\Sale\Entities\Invoice;
use Modules\Sale\Entities\Payment;

class CustomerRepository implements CustomerInterface
{
    // List customer theo id
    public function findId($id)
    {
        $customer = Customer::with(
            'groups:id,name',
            'contacts:id,customer_id,is_primary,first_name,last_name,email,phone_number',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->find($id);
        if (!$customer) {
            return null;
        }
        return $customer;
    }

    // List customer
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Customer::query();
        if ($search) {
            $baseQuery = $baseQuery->leftJoin('contacts', 'contacts.customer_id', '=', 'customers.id')
                ->leftJoin('customer_groups', 'customer_groups.customer_id', '=', 'customers.id')
                ->leftJoin('groups', 'groups.id', '=', 'customer_groups.group_id')->where(
                    function ($q) use ($search) {
                        $q->where('customers.company', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.first_name', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.last_name', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.email', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.phone_number', 'like',  '%' . $search . '%')
                            ->orWhere('groups.name', 'like',  '%' . $search . '%');
                    }
                );
        }
        $customer = $baseQuery->with(
            'groups:id,name',
            'contacts:id,customer_id,is_primary,first_name,last_name,email,phone_number',
            'customFields',
            'customFieldsValues'
        )->select('customers.*')->orderBy('customers.created_at', 'desc');
        if ($limit > 0) {
            $customer = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $customer = $baseQuery->get();
        }
        return $customer;
    }

    public function listSelect()
    {
        $customers =  Customer::orderBy('company')->get();
        return $customers;
    }

    // Thêm mới customer
    public function create($request)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền: hoặc có permission create customer hoặc là admin
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('create customer', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }
        $customer =  new Customer($request);
        $customer->created_by = $user->id;
        $customer->save();
        if (isset($request['groups'])) {
            foreach ($request['groups'] as $key => $group) {
                if (isset($group['id'])) {
                    $customer->groups()->attach($group['id']);
                } else {
                    $gp = Group::where('name', $group['name'])->first();
                    if ($gp === null) {
                        $gp = new Group($group);
                        $gp->save();
                    }
                    $customer->groups()->attach($gp->id);
                }
            }
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $customer->id;
                $customFields->field_id = 3;
                $customFields->field_to = "customers";
                $customFields->save();
            }
        }
        $data = Customer::where('id', $customer->id)->with(
            'groups:id,name',
            'contacts:id,customer_id,is_primary,first_name,last_name,email,phone_number',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        return $data;
    }

    // Cập nhật customer
    public function update($id, $request)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền: hoặc có permission edit customer hoặc là admin
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('edit customer', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        $customer->fill($request);
        $customer->updated_by = Auth::id();
        $customer->groups()->detach();
        if (isset($request['groups'])) {
            foreach ($request['groups'] as $key => $group) {
                if (isset($group['id'])) {
                    $customer->groups()->attach($group['id']);
                } else {
                    $gp = Group::where('name', $group['name'])->first();
                    if ($gp === null) {
                        $gp = new Group($group);
                        $gp->save();
                    }
                    $customer->groups()->attach($gp->id);
                }
            }
        }
        $customFields = [];
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findorNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $customer->id;
                $customFieldsValues->field_id = 3;
                $customFieldsValues->field_to = "customers";
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id', $customer->id)->whereNotIn('id', $customFields)->delete();
        }
        $customer->save();
        $data = Customer::where('id', $customer->id)->with(
            'groups:id,name',
            'contacts:id,customer_id,is_primary,first_name,last_name,email,phone_number',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->get();
        return $data;
    }

    // Xóa customer
    public function destroy($id)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('delete customer', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        $customer->delete();
        return $customer;
    }

    // Thay đổi trạng thái của customer
    public function toggleActive($id)
    {
        $customer = Customer::find($id);
        $customer->active = !$customer->active;
        $customer->save();
        return $customer;
    }

    // Tính toán số lượng khách hàng
    public function count()
    {
        // Tổng khách hàng
        $countCustomer = Customer::all()->count();
        // Tổng khách hàng đang hoạt động
        $countCustomerInactive = Customer::all()->where('active', 1)->count();
        // Tổng khách hàng không hoạt động
        $countCustomerActive = Customer::all()->where('active', 0)->count();
        // Tổng người liên hệ đang hoạt động
        $countContactActive = Contact::all()->where('active', 0)->count();
        // Tổng người liên hệ không hoạt động
        $countContactInactive = Contact::all()->where('active', 1)->count();
        return [
            'countCustomer' => $countCustomer,
            'countCustomerActive' => $countCustomerActive,
            'countCustomerInactive' => $countCustomerInactive,
            'countContactActive' => $countContactActive,
            'countContactInactive' => $countContactInactive,
        ];
    }

    public function statement($id, $request)
    {
        // Xác định statement, mặc định là tuần hiện tại (1)
        $statement = isset($request["statement"]) ? (int)$request["statement"] : 1;

        // Lấy ngày hiện tại
        $today = Carbon::now()->toDateString();

        // Xác định các khoảng thời gian
        $dateRanges = $this->getDateRanges($today);

        // Lấy khoảng thời gian cụ thể dựa trên statement
        $dateRange = $this->getDateRangeByStatement($statement, $dateRanges, $request);

        // Lấy thông tin khách hàng
        $customer = Customer::find($id);

        // Lấy danh sách hoá đơn, thanh toán, ghi chú tín dụng, hoàn tiền ghi chú tín dụng
        $invoices = $this->getInvoices($id, $dateRange);
        $priceInvoice = $invoices->sum('total');

        $payments = $this->getPayments($id, $dateRange);
        $pricePayment = $payments->sum('amount');

        $creditNotes = $this->getCreditNotes($id, $dateRange);
        $priceCreditNote = $creditNotes->sum('total');

        $creditNoteRefunds = $this->getCreditNoteRefunds($id, $dateRange);
        $priceCreditNoteRefund = $creditNoteRefunds->sum('amount');

        // Tính toán các số liệu tài chính
        $priceBeginningBalance = $priceInvoice - $pricePayment - $priceCreditNote + $priceCreditNoteRefund;
        $priceInvoicedAmount = $priceInvoice - $priceCreditNote + $priceCreditNoteRefund;
        $priceBalanceDue = $priceBeginningBalance + $priceInvoicedAmount + $pricePayment;

        return [
            'beginningBalance' => $priceBeginningBalance,
            'invoicedAmount' => $priceInvoicedAmount,
            'amountPaid' => $pricePayment,
            'balanceDue' => $priceBalanceDue,
            'startDate' => $dateRange[0],
            'endDate' => $dateRange[1],
            'invoice' => $invoices,
            'payment' => $payments,
            'creditNote' => $creditNotes,
            'customer' => $customer,
            'creditNoteRefund' => $creditNoteRefunds
        ];
    }

    // Lấy các khoảng thời gian cần thiết
    private function getDateRanges($today)
    {
        return [
            'today' => $today,
            'week' => [
                'start' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                'end' => Carbon::now()->endOfWeek()->format('Y-m-d')
            ],
            'month' => [
                'start' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'end' => Carbon::now()->endOfMonth()->format('Y-m-d')
            ],
            'lastMonth' => [
                'start' => Carbon::now()->addMonths(-1)->startOfMonth()->format('Y-m-d'),
                'end' => Carbon::now()->addMonths(-1)->endOfMonth()->format('Y-m-d')
            ],
            'year' => [
                'start' => Carbon::now()->startOfYear()->format('Y-m-d'),
                'end' => Carbon::now()->endOfYear()->format('Y-m-d')
            ],
            'lastYear' => [
                'start' => Carbon::now()->addYears(-1)->startOfYear()->format('Y-m-d'),
                'end' => Carbon::now()->addYears(-1)->endOfYear()->format('Y-m-d')
            ]
        ];
    }

    // Lấy phạm vi thời gian dựa trên statement
    private function getDateRangeByStatement($statement, $dateRanges, $request)
    {
        // Xử lý phạm vi thời gian nếu có startDate và endDate trong request
        $startDate = isset($request["startDate"]) ? $request["startDate"] : null;
        $endDate = isset($request["endDate"]) ? $request["endDate"] : null;
        $dateRange = [$startDate, $endDate];

        switch ($statement) {
            case 1:
                // Hôm nay
                return [$dateRanges['today'], $dateRanges['today']];
            case 2:
                // Tuần này
                return [$dateRanges['week']['start'], $dateRanges['week']['end']];
            case 3:
                // Tháng này
                return [$dateRanges['month']['start'], $dateRanges['month']['end']];
            case 4:
                // Tháng trước
                return [$dateRanges['lastMonth']['start'], $dateRanges['lastMonth']['end']];
            case 5:
                // Năm này
                return [$dateRanges['year']['start'], $dateRanges['year']['end']];
            case 6:
                // Năm trước
                return [$dateRanges['lastYear']['start'], $dateRanges['lastYear']['end']];
            default:
                // Nếu không có statement thì mặc định là hôm nay
                return $dateRange;
        }
    }

    // Lấy hoá đơn của khách hàng trong phạm vi thời gian
    private function getInvoices($customerId, $dateRange)
    {
        return Invoice::where('customer_id', $customerId)
            ->whereBetween('date', $dateRange)
            ->select('id', 'customer_id', 'number', 'prefix', 'date', 'due_date', 'total')
            ->distinct()
            ->get();
    }

    // Lấy thanh toán của khách hàng trong phạm vi thời gian
    private function getPayments($customerId, $dateRange)
    {
        return Payment::leftJoin('invoices', 'payment.invoice_id', '=', 'invoices.id')
            ->where('invoices.customer_id', $customerId)
            ->where('invoices.status', 2)
            ->whereBetween('payment.date', $dateRange)
            ->select('payment.id', 'payment.invoice_id', 'payment.amount', 'payment.date')
            ->with('invoice:id,number,prefix')
            ->distinct()
            ->get();
    }

    // Lấy ghi chú tín dụng của khách hàng trong phạm vi thời gian
    private function getCreditNotes($customerId, $dateRange)
    {
        return CreditNotes::leftJoin('invoices', 'invoices.customer_id', '=', 'credit_notes.customer_id')
            ->where('invoices.customer_id', $customerId)
            ->whereBetween('credit_notes.date', $dateRange)
            ->select('credit_notes.id', 'credit_notes.customer_id', 'credit_notes.number', 'credit_notes.prefix', 'credit_notes.date', 'credit_notes.total')
            ->distinct()
            ->get();
    }

    // Lấy hoàn tiền ghi chú tín dụng của khách hàng trong phạm vi thời gian
    private function getCreditNoteRefunds($customerId, $dateRange)
    {
        return CreditNotesRefunds::leftJoin('credit_notes', 'credit_notes.id', '=', 'credit_note_refunds.credit_note_id')
            ->leftJoin('invoices', 'invoices.customer_id', '=', 'credit_notes.customer_id')
            ->where('invoices.customer_id', $customerId)
            ->whereBetween('credit_note_refunds.refunded_on', $dateRange)
            ->select('credit_note_refunds.id', 'credit_note_refunds.credit_note_id', 'credit_note_refunds.refunded_on', 'credit_note_refunds.amount')
            ->with('creditNote:id,number,prefix')
            ->distinct()
            ->get();
    }

    private function getDateRange($statement, $today, $week, $month, $lastMonth, $year, $lastYear, $date)
    {
        // Xác định khoảng thời gian dựa trên giá trị của statement
        switch ($statement) {
            case 1:
                return [$today, $today]; // Hôm nay
            case 2:
                return $week; // Tuần này
            case 3:
                return $month; // Tháng này
            case 4:
                return $lastMonth; // Tháng trước
            case 5:
                return $year; // Năm nay
            case 6:
                return $lastYear; // Năm trước
            case 7:
                return $date; // Theo khoảng thời gian từ request
            default:
                return [$today, $today]; // Mặc định là ngày hôm nay
        }
    }

    // Filter customer
    public function filterByCustomer($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $group =  isset($request["group"]) ? $request["group"] : null;
        $invoice = isset($request["invoice"]) ? $request["invoice"] : null;
        $estimate =  isset($request["estimate"]) ? $request["estimate"] : null;
        $project =  isset($request["project"]) ? $request["project"] : null;
        $proposal =  isset($request["proposal"]) ? $request["proposal"] : null;
        $contractType =  isset($request["contractType"]) ? $request["contractType"] : null;
        $customer = Customer::leftJoin('contacts', 'contacts.customer_id', '=', 'customers.id')
            ->leftJoin('customer_groups', 'customer_groups.customer_id', '=', 'customers.id')
            ->leftJoin('groups', 'groups.id', '=', 'customer_groups.group_id')
            ->leftJoin('invoices', 'invoices.customer_id', '=', 'customers.id')
            ->leftJoin('estimates', 'estimates.customer_id', '=', 'customers.id')
            ->leftJoin('projects', 'projects.customer_id', '=', 'customers.id')
            ->leftJoin('proposals', 'proposals.rel_id', '=', 'customers.id')
            ->leftJoin('contracts', 'contracts.client', '=', 'customers.id')
            ->leftJoin('contracts_types', 'contracts_types.id', '=', 'contracts.contract_type');
        $customer = $customer->when(!empty($group), function ($query) use ($group) {
            return $query->whereIn('groups.id', $group);
        })
            ->when(!empty($invoice), function ($query) use ($invoice) {
                return $query->whereIn('invoices.status', $invoice);
            })
            ->when(!empty($estimate), function ($query) use ($estimate) {
                return $query->whereIn('estimates.status', $estimate);
            })
            ->when(!empty($project), function ($query) use ($project) {
                return $query->whereIn('projects.status', $project);
            })
            ->when(!empty($proposal), function ($query) use ($proposal) {
                return $query->whereIn('proposals.status', $proposal);
            })
            ->when(!empty($contractType), function ($query) use ($contractType) {
                return $query->whereIn('contractType.id', $contractType);
            });
        $customer = $customer->with(
            'groups:id,name',
            'contacts:id,customer_id,is_primary,first_name,last_name,email,phone_number',
            'customfields',
            'customFieldsValues',
            'invoice'
        )->select('customers.*')->distinct()->orderBy('customers.created_at', 'desc');
        if ($limit > 0) {
            $customer = $customer->paginate($limit, ['*'], 'page', $page);
        } else {
            $customer = $customer->get();
        }
        return $customer;
    }

    public function getInactiveCustomers($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Customer::query()->where('active', 1);
        if ($search) {
            $baseQuery = $baseQuery->leftJoin('contacts', 'contacts.customer_id', '=', 'customers.id')
                ->leftJoin('customer_groups', 'customer_groups.customer_id', '=', 'customers.id')
                ->leftJoin('groups', 'groups.id', '=', 'customer_groups.group_id')->where(
                    function ($q) use ($search) {
                        $q->where('customers.company', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.first_name', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.last_name', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.email', 'like',  '%' . $search . '%')
                            ->orWhere('contacts.phone_number', 'like',  '%' . $search . '%')
                            ->orWhere('groups.name', 'like',  '%' . $search . '%');
                    }
                );
        }
        $customer = $baseQuery->with(
            'groups:id,name',
            'contacts:id,customer_id,is_primary,first_name,last_name,email,phone_number',
            'customfields',
            'customFieldsValues'
        )->select('customers.*')->orderBy('customers.created_at', 'desc');
        if ($limit > 0) {
            $customer = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $customer = $baseQuery->get();
        }
        return $customer;
    }

    public function bulkAction($request)
    {
        $customerIds = $request->customer_ids;
        $action = $request->action;

        switch ($action) {
            case 'delete':
                Customer::whereIn('id', $customerIds)->delete();
                return null;

            case 'activate':
                Customer::whereIn('id', $customerIds)->update(['active' => 1]);
                return null;

            case 'deactivate':
                Customer::whereIn('id', $customerIds)->update(['active' => 0]);
                return null;

            case 'update_group':
                if (!$request->group_id) {
                    return null;
                }
                foreach ($customerIds as $customerId) {
                    CustomerGroups::updateOrCreate(
                        ['customer_id' => $customerId],
                        ['group_id' => $request->group_id]
                    );
                }
                return null;

            default:
                return null;
        }
    }
}

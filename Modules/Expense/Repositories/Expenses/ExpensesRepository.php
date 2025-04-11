<?php

namespace Modules\Expense\Repositories\Expenses;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Entities\File;
use Modules\Expense\Entities\Expenses;
use Modules\Project\Entities\Project;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomFieldValue;
use Str;

class ExpensesRepository implements ExpensesInterface
{
    use LogActivityTrait;

    // List expense theo id
    public function findId($id)
    {
        $expenses = Expenses::with([
            'customer:id,company',
            'project:id,name',
            'expenseCategory',
            'customFields:id,field_to,name',
            'customFieldsValues',
            'files:id,rel_id,file_name,file_type',
        ])->find($id);
        return $expenses;
    }

    // List expense theo customer
    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request["id"]) ? $request["id"] : null;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $baseQuery = Expenses::leftJoin('customers', 'customers.id', '=', 'expenses.customer_id')
            ->leftJoin('projects', 'projects.id', '=', 'expenses.project_id')
            ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'expenses.category')
            ->where('customers.id', '=', $id);
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('expenses.date', 'like', '%' . $search . '%')
                        ->orWhere('expenses_categories.name', 'like', '%' . $search . '%')
                        ->orWhere('projects.name', 'like', '%' . $search . '%');
                }
            );
        }
        $subscription = $baseQuery->with(
            'customer:id,company',
            'project:id,name',
            'paymentMode',
            'expenseCategory',
            'files:id,rel_id,file_name,file_type',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )
            ->select('expenses.*')->orderBy('expenses.created_at', 'desc');
        if ($limit > 0) {
            $subscription = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $subscription = $baseQuery->get();
        }
        return $subscription;
    }

    // List expense
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $name = isset($request["name"]) ? $request["name"] : null;
        $baseQuery = Expenses::leftJoin('customers', 'customers.id', '=', 'expenses.customer_id')
            ->leftJoin('projects', 'projects.id', '=', 'expenses.project_id');
        if ($name) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($name) {
                    $q->where('customers.company', 'like', '%' . $name . '%')
                        ->orWhere('expenses.expense_name', 'like', '%' . $name . '%')
                        ->orWhere('expenses.date', 'like', '%' . $name . '%')
                        ->orWhere('projects.name', 'like', '%' . $name . '%');
                }
            );
        }
        $baseQuery = $baseQuery->with(
            'customer:id,company',
            'files:id,rel_id,file_name,file_type',
            'project:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->select('expenses.*')->orderBy('expenses.created_at', 'desc');
        if ($limit > 0) {
            $expenses = $baseQuery->paginate($limit);
        } else {
            $expenses = $baseQuery->get();
        }
        return $expenses;
    }

    // Thêm mới expense theo customer
    public function createByCustomer($id, $request)
    {
        $customer = Customer::find($id);
        $expenses = new Expenses($request);
        $expenses->created_at = Auth::id();
        $expenses->customer_id = $id;
        $expenses->created_by = Auth::id();
        $expenses->save();

        if (isset($request['file_name'])) {
            $file = new File($request);
            $file->created_at = Auth::id();
            $file->rel_id = $expenses->id;
            $file->rel_type = 'expense';
            $file->visible_to_customer = "1";
            $file->staff_id = Auth::user()->id;
            $file->task_comment_id = "0";
            $fileUpLoad = $request['file_name'];
            $fileName = $fileUpLoad->getClientOriginalName();
            $fileType = $fileUpLoad->getMimeType();
            $file->file_name = $fileName;
            $file->filetype = $fileType;
            $fileUpLoad->move('uploads/expense', $fileName);
            $file->save();
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $expenses->id;
                $customFields->field_to = "expenses";
                $customFields->save();
            }
        }
        $data = Expenses::where('id', $expenses->id)->with(
            'customer:id,company',
            'project:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->first();
        return $data;
    }

    // Thêm mới expense theo project
    public function createByProject($id, $request)
    {
        $expenses = Expenses::where('project_id', $id);
        $project = Project::where('id', $id)->first();
        $expenses = new Expenses($request);
        $expenses->created_at = Auth::id();
        $expenses->project_id = $id;
        $expenses->customer_id = $project->customer_id;
        $expenses->created_by = Auth::id();
        $expenses->save();

        $this->createProjectActivity($id, ActivityKey::CREATE_EXPENSE_BY_PROJECT);
        //upload file khi create
        if (isset($request['file_name'])) {
            $file = new File($request);
            $file->created_at = Auth::id();
            $file->rel_id = $expenses->id;
            $file->rel_type = 'expense';
            $file->visible_to_customer = "1";
            $file->staff_id = Auth::user()->id;
            $file->task_comment_id = "0";
            $fileUpLoad = $request['file_name'];
            $fileName = $fileUpLoad->getClientOriginalName();
            $fileType = $fileUpLoad->getMimeType();
            $file->file_name = $fileName;
            $file->filetype = $fileType;
            $fileUpLoad->move('uploads/expense', $fileName);
            $file->save();
        }
        //xử lý cùng customField
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $expenses->id;
                $customFields->field_to = "expenses";
                $customFields->save();
            }
        }
        $data = Expenses::where('id', $expenses->id)->with(
            'customer:id,company',
            'project:id,name',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->first();
        return $data;
    }

    // Thêm mới expense
    public function create($request)
    {
        // Tạo mới expense
        $expenses = new Expenses($request);
        $expenses->created_by = Auth::id();
        $expenses->save();
        // Log::debug($expenses);

        $uploadedFile = null;

        // Upload file khi create
        if (isset($request['file_name'])) {
            $file = new File($request);
            $file->rel_id = $expenses->id;
            $file->rel_type = 'expense';
            $file->visible_to_customer = "1";
            $file->staff_id = Auth::user()->id;
            $file->task_comment_id = "0";

            $fileUpload = $request['file_name'];
            $originalFileName = $fileUpload->getClientOriginalName();
            $fileType = $fileUpload->getMimeType();

            $fileNameWithoutExt = pathinfo($originalFileName, PATHINFO_FILENAME);
            $extension = $fileUpload->getClientOriginalExtension();
            $cleanFileName = Str::slug($fileNameWithoutExt, '_');
            $uniqueFileName = $cleanFileName . '_' . time() . '.' . $extension;

            $filePath = 'uploads/expense/' . $uniqueFileName;

            $fileUpload->move(public_path('uploads/expense'), $uniqueFileName);

            $file->file_name = $uniqueFileName;
            $file->file_type = $fileType;
            $file->save();

            $fileUrl = url($filePath);

            $uploadedFile = [
                'id' => $file->id,
                'file_name' => $uniqueFileName,
                'file_type' => $fileType,
                'file_path' => $fileUrl,
            ];
        }

        // Xử lý custom fields
        if (isset($request['customFieldsValues']) && is_array($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $expenses->id;
                $customFields->field_id = 3;
                $customFields->field_to = "expenses";
                $customFields->save();
            }
        }

        // Lấy dữ liệu trả về
        $data = Expenses::where('id', $expenses->id)->with(
            'customer:id,company',
            'project:id,name',
            'expenseCategory',
            'customFields:id,field_to,name',
            'customFieldsValues'
        )->first();

        // Gán thông tin file vào response
        $data->uploaded_file = $uploadedFile;

        return $data;
    }

    // Cập nhật expense
    public function update($id, $request)
    {
        $expenses = Expenses::find($id);
        $expenses->fill($request);

        $expenses->save();
        if ($expenses->project_id !== null) {
            $this->createProjectActivity($expenses->project_id, 44);
        }
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findorNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $expenses->id;
                $customFieldsValues->field_id = $expenses->id;
                $customFieldsValues->field_to = "expenses";
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id', $expenses->id)->whereNotIn('id', $customFields)->delete();
        }
        File::where('rel_id', $expenses->id)->where('rel_type', '=', 'expense')->delete();
        if (isset($request['file_name'])) {
            $file = new File($request);
            $file->created_at = Auth::id();
            $file->rel_id = $expenses->id;
            $file->rel_type = 'expense';
            $file->visible_to_customer = "1";
            $file->staff_id = Auth::user()->id;
            $file->task_comment_id = "0";
            $fileUpLoad = $request['file_name'];
            $fileName = $fileUpLoad->getClientOriginalName();
            $fileType = $fileUpLoad->getMimeType();
            $file->file_name = $fileName;
            $file->file_type = $fileType;
            $fileUpLoad->move('uploads/expense', $fileName);
            $file->save();
        }

        $data = Expenses::where('id', $expenses->id)->with('customer:id,company', 'project:id,name', 'paymentMode', 'expenseCategory', 'customFieldsValues')->first();
        return $data;
    }
    // Xóa expense
    public function destroy($id)
    {
        $expenses = Expenses::find($id);

        if ($expenses && $expenses->project_id !== null) {
            $this->createProjectActivity($expenses->project_id, 45);
        }
        $expenses->delete();

        return $expenses;
    }

    // Tính giá theo customer
    public function countByCustomer($id)
    {
        // Tổng tiền theo customer
        $total = Expenses::where('custoemr_id', $id)->sum('amount');
        // Tổng tiền theo customer khi invoice null
        $notInvoice = Expenses::where('custoemr_id', $id)
            ->whereNull('invoice_id')->sum('amount');
        // Tổng tiền theo customer khi invoice not null
        $billed = Expenses::where('custoemr_id', $id)
            ->whereNotNull('invoice_id')->sum('amount');
        return [
            'total' => $total,
            'not invoice' => $notInvoice,
            'billed' => $billed

        ];
    }

    // List expense theo project
    public function getListByProject($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Expenses::query()->leftJoin('projects', 'projects.id', '=', 'expenses.project_id')->where('projects.id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('expenses.expense_name', 'like', '%' . $search . '%')
                ->orWhere('expenses.date', 'like', '%' . $search . '%');
        }
        $baseQuery = $baseQuery->with(
            'customer:id,company',
            'project:id,name',
            'files:id,rel_id,file_name,file_type',
            'paymentMode:id,name',
            'invoice:id,prefix,number',
            'expenseCategory:id,name'
        )->select('expenses.*');
        if ($limit > 0) {
            $expenses = $baseQuery->paginate($limit);
        } else {
            $expenses = $baseQuery->get();
        }
        return $expenses;
    }

    // List expense theo project, theo năm
    public function getListByYearProject($id, $request)
    {
        $year = isset($request["year"]) ? json_decode($request["year"]) : null;
        // Tổng tiền theo năm
        $total = DB::table('expenses')->leftJoin('projects', 'projects.id', '=', 'expenses.project_id')->where('projects.id', $id)
            ->whereIn(DB::raw("year(expenses.date)"), $year)
            ->sum('expenses.amount');
        // Tổng tiền theo năm với invoice null
        $notInvoice = DB::table('expenses')->leftJoin('projects', 'projects.id', '=', 'expenses.project_id')->where('projects.id', $id)
            ->whereIn(DB::raw("year(expenses.date)"), $year)
            ->whereNull(DB::raw("expenses.invoice_id"))
            ->sum('expenses.amount');
        // Tổng tiền theo năm với billable = 1 && invoice != null
        $invoice = DB::table('expenses')->leftJoin('projects', 'projects.id', '=', 'expenses.project_id')->where('projects.id', $id)
            ->leftJoin('invoices', 'invoices.id', '=', 'expenses.invoice_id')
            ->where('expenses.billable', 1)
            ->whereIn(DB::raw("year(expenses.date)"), $year)
            ->whereNotNull(DB::raw("expenses.invoice_id"))
            ->sum('expenses.amount');
        $data = ['total' => $total, 'notInvoice' => $notInvoice, 'billed' => $invoice];
        return $data;
    }

    // List expense theo customer, theo năm
    public function getListByYearCustomer($id, $request)
    {
        $year = isset($request["year"]) ? json_decode($request["year"]) : null;
        $currency = isset($request["currency"]) ? $request["currency"] : null;
        // Tổng tiền theo năm
        $total = DB::table('expenses')->leftJoin('customers', 'customers.id', '=', 'expenses.customer_id')->where('customers.id', $id)
            ->whereIn(DB::raw("year(expenses.date)"), $year)
            ->where('expenses.currency', $currency)
            ->sum('expenses.amount');
        // Tổng tiền theo năm với invoice null
        $notInvoice = DB::table('expenses')->leftJoin('customers', 'customers.id', '=', 'expenses.customer_id')->where('customers.id', $id)
            ->whereIn(DB::raw("year(expenses.date)"), $year)
            ->whereNull(DB::raw("expenses.invoice_id"))
            ->where('expenses.currency', $currency)
            ->sum('expenses.amount');
        // Tổng tiền theo năm với billable = 1 && invoice != null
        $invoice = DB::table('expenses')->leftJoin('customers', 'customers.id', '=', 'expenses.customer_id')->where('customers.id', $id)
            ->leftJoin('invoices', 'invoices.id', '=', 'expenses.invoice_id')
            ->where('expenses.billable', 1)
            ->whereIn(DB::raw("year(expenses.date)"), $year)
            ->where('expenses.currency', $currency)
            ->whereNotNull(DB::raw("expenses.invoice_id"))
            ->sum('expenses.amount');
        $data = ['total' => $total, 'notInvoice' => $notInvoice, 'billed' => $invoice];
        return $data;
    }

    public function filterByExpense($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 0;
        $billable = isset($request["billable"]) ? json_decode($request["billable"]) : null;
        $invoice = isset($request["invoice"]) ? (int) $request["invoice"] : 0;
        $notInvoice = isset($request["notInvoice"]) ? (int) $request["notInvoice"] : 0;
        $recurring = isset($request["recurring"]) ? $request["recurring"] : null;
        $year = isset($request["year"]) ? json_decode($request["year"]) : null;
        $month = isset($request["month"]) ? json_decode($request["month"]) : null;
        $category = isset($request["category"]) ? json_decode($request["category"]) : null;
        $expense = Expenses::leftJoin('expenses_categories', 'expenses_categories.id', '=', 'expenses.category');
        $expense = $expense
            ->when(!empty($billable), function ($query) use ($billable) {
                return $query->whereIn('expenses.billable', $billable);
            })
            ->where(function ($query) use ($invoice) {
                if ($invoice === 1) {
                    return $query->whereNotNull("expenses.invoice_id");
                }
            })
            ->orWhere(function ($query) use ($notInvoice) {
                if ($notInvoice === 1) {
                    return $query->whereNull("expenses.invoice_id");
                }
            })
            ->when(!empty($recurring), function ($query) use ($recurring) {
                return $query->where('expenses.recurring', $recurring);
            })
            ->when(!empty($category), function ($query) use ($category) {
                return $query->whereIn('expenses_categories.id', $category);
            })
            ->when(!empty($year), function ($query) use ($year) {
                return $query->whereIn(DB::raw("year(expenses.date)"), $year);
            })
            ->when(!empty($month), function ($query) use ($month) {
                return $query->whereIn(DB::raw("month(expenses.date)"), $month);
            });

        $expense = $expense
            ->with(
                'customer:id,company',
                'project:id,name',
                'customFields:id,field_to,name',
                'customFieldsValues'
            )->select('expenses.*')->distinct()->orderBy('expenses.created_at', 'desc');
        if ($limit > 0) {
            $expense = $expense->paginate($limit, ['*'], 'page', $page);
        } else {
            $expense = $expense->get();
        }
        return $expense;
    }

    public function filterExpenseByProject($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 0;
        $billable = isset($request["billable"]) ? json_decode($request["billable"]) : null;
        $invoice = isset($request["invoice"]) ? (int) $request["invoice"] : 0;
        $notInvoice = isset($request["notInvoice"]) ? (int) $request["notInvoice"] : 0;
        $recurring = isset($request["recurring"]) ? $request["recurring"] : null;
        $year = isset($request["year"]) ? json_decode($request["year"]) : null;
        $month = isset($request["month"]) ? json_decode($request["month"]) : null;
        $category = isset($request["category"]) ? json_decode($request["category"]) : null;
        $expense = Expenses::leftJoin('expenses_categories', 'expenses_categories.id', '=', 'expenses.category')
            ->where('expenses.project_id', $id);
        $expense = $expense
            ->when(!empty($billable), function ($query) use ($billable) {
                return $query->whereIn('expenses.billable', $billable);
            })
            ->where(function ($query) use ($invoice) {
                if ($invoice === 1) {
                    return $query->whereNotNull("expenses.invoice_id");
                }
            })
            ->orWhere(function ($query) use ($notInvoice) {
                if ($notInvoice === 1) {
                    return $query->whereNull("expenses.invoice_id");
                }
            })
            ->when(!empty($recurring), function ($query) use ($recurring) {
                return $query->where('expenses.recurring', $recurring);
            })
            ->when(!empty($category), function ($query) use ($category) {
                return $query->whereIn('expenses_categories.id', $category);
            })
            ->when(!empty($year), function ($query) use ($year) {
                return $query->whereIn(DB::raw("year(expenses.date)"), $year);
            })
            ->when(!empty($month), function ($query) use ($month) {
                return $query->whereIn(DB::raw("month(expenses.date)"), $month);
            });

        $expense = $expense
            ->with(
                'customer:id,company',
                'project:id,name',
                'customFields:id,field_to,name',
                'customFieldsValues'
            )->select('expenses.*')->distinct()->orderBy('expenses.created_at', 'desc');
        if ($limit > 0) {
            $expense = $expense->paginate($limit, ['*'], 'page', $page);
        } else {
            $expense = $expense->get();
        }
        return $expense;
    }

    // List expensetheo năm
    public function getListByYear($request)
    {
        $year = isset($request["year"]) ? json_decode($request["year"]) : null;
        // Tổng tiền theo năm
        $total = DB::table('expenses')
            ->whereIn(DB::raw("year(expenses.date)"), $year)
            ->sum('expenses.amount');
        // Tổng tiền theo năm với invoice null
        $notInvoice = DB::table('expenses')
            ->whereIn(DB::raw("year(expenses.date)"), $year)
            ->whereNull(DB::raw("expenses.invoice_id"))
            ->sum('expenses.amount');
        // Tổng tiền theo năm với billable = 1 && invoice != null
        $invoice = DB::table('expenses')
            ->leftJoin('invoices', 'invoices.id', '=', 'expenses.invoice_id')
            ->where('expenses.billable', 1)
            ->whereIn(DB::raw("year(expenses.date)"), $year)
            ->whereNotNull(DB::raw("expenses.invoice_id"))
            ->sum('expenses.amount');
        $data = ['total' => $total, 'notInvoice' => $notInvoice, 'billed' => $invoice];
        return $data;
    }
}

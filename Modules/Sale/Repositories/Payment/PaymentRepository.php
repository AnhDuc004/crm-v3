<?php

namespace Modules\Sale\Repositories\Payment;

use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use Modules\Sale\Entities\Invoice;
use Modules\Sale\Entities\Payment;
use Modules\Sale\Entities\SalesActivity;

class PaymentRepository implements PaymentInterface
{
    use LogActivityTrait;
    
    // List payment theo customer
    public function getListByCustomer($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 10;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = $request["search"] ?? null;

        $query = Payment::leftJoin('invoices', 'invoices.id', '=', 'payment.invoice_id')
            ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'payment.payment_mode')
            ->where('customers.id', $id)
            ->select([
                'payment.*',
                'customers.company as customer_company',
                'customers.email as customer_email',
                'customers.phone as customer_phone',
                DB::raw("CONCAT(invoices.prefix, invoices.number) as invoice_number"),
                'payment_modes.name as payment_mode'
            ])
            ->with(['invoice', 'mode'])
            ->orderBy('payment.date', 'desc');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('payment.date', 'like', '%' . $search . '%')
                    ->orWhere('payment.amount', 'like', '%' . $search . '%')
                    ->orWhere('payment_modes.name', 'like', '%' . $search . '%')
                    ->orWhere('customers.company', 'like', '%' . $search . '%');
            });
        }

        return $limit > 0 ? $query->paginate($limit, ['*'], 'page', $page) : $query->get();
    }

    // List payment
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Payment::with(['invoice', 'mode']);
        if ($search) {
            $baseQuery = $baseQuery->leftJoin('invoices', 'invoices.id', '=', 'payment.invoice_id')->join('customers', 'customers.id', '=', 'invoices.customer_id')
                ->orWhere('customers.company', 'like', '%' . $search . '%')
                ->orWhere('payment.date', 'like', '%' . $search . '%')
                ->orWhere('payment.transaction_id', 'like', '%' . $search . '%');
        }

        $invoicePayment = $baseQuery->with('invoice.customer:id,company')->select('payment.*')->orderBy('payment.date', 'desc');;

        if ($limit > 0) {
            $invoicePayment = $baseQuery->paginate($limit);
        } else {
            $invoicePayment = $baseQuery->get();
        }
        return $invoicePayment;
    }

    // Thêm mới payment theo customer
    public function createByCustomer($id, $request)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        $invoice = Invoice::where('customer_id', $id)->whereIn('status', [2, 3])->get();
        $invoicePayment = new Payment($request);
        $invoicePayment->created_by = Auth::user()->id;
        $invoicePayment->save();
        $this->createSaleActivity($invoicePayment->id, 4, ActivityKey::CREATE_PAYMENT_BY_CUSTOMER);
        $data = Payment::where('id', $invoicePayment->id)->with(['invoice', 'mode'])
            ->get();
        return $data;
    }

    // Cập nhật payment
    public function update($id, $request)
    {
        $invoicePayment = Payment::find($id);
        if (!$invoicePayment) {
            return null;
        }
        $invoicePayment->fill($request);
        $invoicePayment->updated_by = Auth::user()->id;
        $invoicePayment->save();
        $this->createSaleActivity($id, 4, ActivityKey::UPDATE_PAYMENT);
        $data = Payment::where('id', $invoicePayment->id)->with(['invoice', 'mode'])
            ->get();
        return $data;
    }

    // Xóa payment
    public function destroy($id)
    {
        $invoicePayment = Payment::find($id);
        if (!$invoicePayment) {
            return null;
        }
        $this->createSaleActivity($id, 4, ActivityKey::DELETE_PAYMENT);
        $invoicePayment->delete();
        return $invoicePayment;
    }

    public function findId($id)
    {
        $payment = Payment::with('invoice.customer:id,company', 'mode:id,name')->select('id', 'invoiceid', 'amount', 'date', 'paymentmode')->find($id);
        if (!$payment) {
            return null;
        }
        return $payment;
    }
}

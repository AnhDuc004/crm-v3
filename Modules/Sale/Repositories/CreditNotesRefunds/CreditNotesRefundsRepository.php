<?php

namespace Modules\Sale\Repositories\CreditNotesRefunds;
use Illuminate\Support\Carbon;
use Modules\Sale\Entities\CreditNotesRefunds;


class CreditNotesRefundsRepository implements CreditNotesRefundsInterface
{
    public function getListByCreditNote($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request['search'] : null;
        $baseQuery  = CreditNotesRefunds::query()->where('credit_note_id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('note', 'like',  '%' . $search . '%');
        }
        $baseQuery = $baseQuery->with('creditNote:id,number,prefix', 'staff:staffid,firstname,lastname', 'paymentMode:id,name')->select('creditnote_refunds.*')->orderBy('created_at', 'desc');
        if ($limit > 0) {
            $creditNotesRefunds = $baseQuery->paginate($limit);
        } else {
            $creditNotesRefunds = $baseQuery->get();
        }
        return $creditNotesRefunds;
    }
    public function createByCreditNote($id, $request)
    {
        $creditNotesRefunds = new CreditNotesRefunds($request);
        $creditNotesRefunds->credit_note_id = $id;
        $creditNotesRefunds->created_at = Carbon::now();
        $creditNotesRefunds->save();
        $data = CreditNotesRefunds::where('id', $creditNotesRefunds->id)->with('creditNote', 'staff', 'paymentMode')->get();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function update($id, $request)
    {
        $creditNotesRefunds = CreditNotesRefunds::find($id);
        if (!$creditNotesRefunds) {
            return null;
        }
        $creditNotesRefunds->fill($request);
        $creditNotesRefunds->save();
        $data = CreditNotesRefunds::where('id', $creditNotesRefunds->id)->with('creditNote', 'staff', 'paymentMode')->get();
        return $data;
    }

    public function destroy($id)
    {
        $creditNotesRefunds = CreditNotesRefunds::find($id);
        if (!$creditNotesRefunds) {
            return null;
        }
        $creditNotesRefunds->delete();
        return $creditNotesRefunds;
    }
}

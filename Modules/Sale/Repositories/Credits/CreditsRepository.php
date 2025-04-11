<?php

namespace Modules\Sale\Repositories\Credits;
use Illuminate\Support\Carbon;
use Modules\Sale\Entities\Credits;

class CreditsRepository implements CreditsInterface
{
    public function getListByCreditNote($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request['search'] : null;
        $baseQuery  = Credits::query()->where('credit_id', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('note', 'like',  '%' . $search . '%');
        }
        $baseQuery = $baseQuery->with('creditNote:id,number,prefix', 'staff:staffid,firstname,lastname', 'invoice:id,number,prefix')->select('credits.*')->orderBy('date_applied', 'desc');
        if ($limit > 0) {
            $credits = $baseQuery->paginate($limit);
        } else {
            $credits = $baseQuery->get();
        }
        return $credits;
    }
    
    public function createByCreditNote($id, $request)
    {
        $credits = new Credits($request);
        $credits->credit_id = $id;
        $credits->date_applied = Carbon::now();
        $credits->save();
        $data = Credits::where('id', $credits->id)->with('creditNote', 'staff', 'invoice')->get();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function update($id, $request)
    {
        $credits = Credits::find($id);
        if (!$credits) {
            return null;
        }
        $credits->fill($request);
        $credits->save();
        $data = Credits::where('id', $credits->id)->with('creditNote', 'staff', 'invoice')->get();
        return $data;
    }

    public function destroy($id)
    {
        $credits = Credits::find($id);
        if (!$credits) {
            return null;
        }
        $credits->delete();
        return $credits;
    }
}

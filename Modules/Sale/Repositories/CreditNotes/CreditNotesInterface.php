<?php

namespace Modules\Sale\Repositories\CreditNotes;

interface CreditNotesInterface
{
    public function findId($id);

    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function filterByCreditNote($request);

    public function createByCustomer($id, $request);

    public function getListByCustomer($id, $request);
}

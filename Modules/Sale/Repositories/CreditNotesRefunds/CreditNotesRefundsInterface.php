<?php

namespace Modules\Sale\Repositories\CreditNotesRefunds;

interface CreditNotesRefundsInterface
{
    public function getListByCreditNote($id, $request);

    public function createByCreditNote($id, $request);

    public function update($id, $request);

    public function destroy($id);

}
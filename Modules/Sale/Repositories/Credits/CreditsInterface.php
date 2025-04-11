<?php

namespace Modules\Sale\Repositories\Credits;

interface CreditsInterface
{
    public function getListByCreditNote($id, $request);

    public function createByCreditNote($id, $request);

    public function update($id, $request);

    public function destroy($id);

}
<?php

namespace Modules\Sale\Repositories\Taxes;

interface TaxesInterface
{
    public function findId($id);
    
    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);
}
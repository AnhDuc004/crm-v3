<?php

namespace Modules\Contract\Repositories\ContractRenewals;

interface ContractRenewalsInterface
{
    public function findId($id);
    
    public function listAll($request);


    public function create($id,$request);

    public function update($id, $request);

    public function destroy($id);

    public function getListByContract($id, $request);

}
<?php

namespace Modules\Contract\Repositories\Contract;

interface ContractInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function getListByComment($id, $request);

    public function listAll($queryData);

    public function createByCustomer($id, $request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function countActive();

    public function createByComment($id, $request);

    public function updateComment($id, $request);

    public function destroyComment($id);

    public function countByContractType($id);

    public function filterByContract($request);

    public function statisticContractsByType();

    public function statisticContractsValueByType();

    public function contractByContent($id, $request);

    public function changeSigned($id, $request);

    public function copyContract($id);

    public function countContractsByType();
}

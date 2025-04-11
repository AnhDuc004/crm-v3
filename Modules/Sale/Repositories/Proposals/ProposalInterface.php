<?php

namespace Modules\Sale\Repositories\Proposals;

interface ProposalInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function getListByLead($id, $request);

    public function findItemable($id, $request);

    public function listAll($request);

    public function listSelect();

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function changeStatus($id, $status);

    public function createByLead($id, $request);

    public function createByCustomer($id, $request);

    public function getListByComment($id, $request);

    public function createByComment($id, $request);

    public function updateByComment($id, $request);

    public function destroyByComment($id);

    public function copyData($id);

    public function filterByProposal($request);

    public function count();
}

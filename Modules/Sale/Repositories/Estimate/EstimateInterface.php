<?php

namespace Modules\Sale\Repositories\Estimate;

interface EstimateInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function getListByItemable($id, $request);

    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function countByCustomer($id);

    public function countEstimateByProject($id);

    public function getListByProject($id,$request);

    public function createByCustomer($id,$request);

    public function getListByYearProject($id,$request); 

    public function getListByYearCustomer($id,$request);
    
    public function changeStatus($id, $request);

    public function copyData($id);

    public function convertProposalToEstimaste($id, $request);

    public function countByStatus();

    public function filterByEstimate($request);

    public function filterEstimateByProject($id, $request);

    public function getListByYear($request); 

}

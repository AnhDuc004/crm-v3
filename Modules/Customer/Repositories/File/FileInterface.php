<?php

namespace Modules\Customer\Repositories\File;

interface FileInterface
{
    public function findId($id);
    
    public function getListByCustomer($id, $request);

    public function getListByContract($id, $request);

    public function getListByLead($id, $request);

    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function uploadFileByLead($id,$request);

    public function uploadFileByCustomer($id,$request);

    public function uploadFileByContract($id,$request);

    public function changeVisibleToCustomer($id, $request);

    public function uploadFileByProposal($id, $request);

    public function getListByProposal($id, $request);

    public function uploadFileByEstimate($id, $request);

    public function getListByEstimate($id, $request);

    public function uploadFileByInvoice($id, $request);

    public function getListByInvoice($id, $request);

    public function uploadFileByTask($id, $request);

    public function getListByTask($id, $request);

}
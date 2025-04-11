<?php

namespace Modules\Customer\Repositories\Notes;

interface NoteInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function listAll($request);

    public function createByCustomer($id, $request);

    public function update($id, $request);

    public function destroy($id);

    public function getByEstimaste($id, $request);

    public function createByEstimaste($id, $request);

    public function getByLead($id, $request);

    public function createByLead($id, $request);

    public function getListByContract($id, $request);

    public function createByContract($id, $request);

    public function getListByTicket($id, $request);

    public function createByTicket($id, $request);

    public function getByProposal($id, $request);
    
    public function createByProposal($id, $request);

    public function getByInvoice($id, $request);
    
    public function createByInvoice($id, $request);

    public function getByStaff($id, $request);
    
    public function createByStaff($id, $request);

}
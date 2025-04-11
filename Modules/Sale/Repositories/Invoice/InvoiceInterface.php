<?php

namespace Modules\Sale\Repositories\Invoice;

interface InvoiceInterface
{
    public function getListByCustomer($id, $request);

    public function listAll($request);

    public function createByCustomer($id, $request);

    public function update($id, $request);

    public function destroy($id);

    public function getListByProject($id,$request);

    public function getListByYearProject($id, $request); 

    public function getListByYearCustomer($id,$request); 

    public function getListByYear($request); 

    public function countInvoiceByProject($id);
    
    public function create($request);

    public function getListRecuringInvoice($request);

    public function findId($id);

    public function payment($id, $request);

    public function invoiceWithCustomers($id, $request);

    public function convertEstimateToInvoice($id, $request);

    public function convertProposalToInvoice($id, $request);

    public function filterByInvoice($request);
    
    public function copyData($id, $request);

    public function countInvoiceByCustomer($id);

    // filter invoice theo project
    public function filterInvoiceByProject($id, $request);
}
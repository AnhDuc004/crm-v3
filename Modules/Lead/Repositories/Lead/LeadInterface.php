<?php

namespace Modules\Lead\Repositories\Lead;

interface LeadInterface
{
    public function findId($id, $request);

    public function listAll($request);

    public function listSelect();

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function changeStatus($id, $status);

    public function convertToCustomer($id, $request);

    public function countLeadBySources($id);

    public function countLeadByStatus($id);
}

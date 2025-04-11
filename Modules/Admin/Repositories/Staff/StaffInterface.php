<?php

namespace Modules\Admin\Repositories\Staff;

use Illuminate\Http\Request;

interface StaffInterface
{
    public function findId($id);

    public function listAll($request);

    public function create(Request $request);

    public function update($id,Request $request);

    public function destroy($id);

    public function toggleActive($id);

    public function getListByTask($request);

    public function getListByTicket($request);

    public function getListByProposal($request);

    public function getListByEstimate($staffId);

    public function getListByInvoice($staffId);

    public function updateProfileImage(Request $request, $id);

}
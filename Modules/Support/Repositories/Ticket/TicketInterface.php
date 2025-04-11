<?php

namespace Modules\Support\Repositories\Ticket;

interface TicketInterface
{
    public function findId($id);
    
    public function getListByCustomer($id, $request);

    public function listAll($request);

    public function createByCustomer($id, $request);

    public function update($id, $request);

    public function destroy($id);

    public function count();

    public function create($request);

    public function filterByTicket($request);

    public function getListByProject($id, $request);

    public function createByProject($id, $request);

    public function countByProject($project_id);

}
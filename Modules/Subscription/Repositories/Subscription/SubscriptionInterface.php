<?php

namespace Modules\Subscription\Repositories\Subscription;

interface SubscriptionInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function listAll($request);

    public function createByCustomer($id,$request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function countNotSubscribed();

    public function countActive();

    public function countFuture();

    public function countPastDue();

    public function countPaid();

    public function countIncomplete();

    public function countCanceled();

    public function countIncompleteExpired();

    public function getByProject($id,$request);

}
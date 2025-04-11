<?php

namespace Modules\Sale\Repositories\Payment;

interface PaymentInterface
{
    public function getListByCustomer($id, $request);

    public function listAll($request);

    public function createByCustomer($id, $request);

    public function update($id, $request);

    public function destroy($id);

    public function findId($id);

}
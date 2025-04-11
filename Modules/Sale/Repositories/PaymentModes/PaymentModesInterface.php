<?php

namespace Modules\Sale\Repositories\PaymentModes;

interface PaymentModesInterface
{
    public function findId($id);

    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function toggleActive($id);
}

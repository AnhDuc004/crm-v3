<?php

namespace Modules\Customer\Repositories\Contact;

interface ContactInterface
{
    public function getListByCustomer($id, $request);

    public function listAll($queryData);

    public function create($id,$request);

    public function update($id, $request);

    public function destroy($id);

    public function toggleActive($id);

    public function findId($id);


}
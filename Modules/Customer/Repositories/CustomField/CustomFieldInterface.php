<?php

namespace Modules\Customer\Repositories\CustomField;

interface CustomFieldInterface
{
    public function findId($id);

    public function getByName($id);
    
    public function listAll($request);

    public function listSelect();

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function toggleActive($id);
}
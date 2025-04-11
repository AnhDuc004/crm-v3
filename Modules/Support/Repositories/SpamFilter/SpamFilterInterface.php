<?php

namespace Modules\Support\Repositories\SpamFilter;

interface SpamFilterInterface
{
    public function findId($id);
    
    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function createByLead($request);
}
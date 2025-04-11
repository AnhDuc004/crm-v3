<?php

namespace Modules\Lead\Repositories\Lead;

interface LeadSourceInterface
{
    public function findId($id);

    public function listAll($request);

    public function listSelect();

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

}

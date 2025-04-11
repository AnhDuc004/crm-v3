<?php

namespace Modules\Campaign\Repositories\Domain;

interface GroupDomainInterface
{
    public function findId($id);
    
    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);
}
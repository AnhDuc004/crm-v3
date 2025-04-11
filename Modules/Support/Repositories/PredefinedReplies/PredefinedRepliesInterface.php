<?php

namespace Modules\Support\Repositories\PredefinedReplies;

interface PredefinedRepliesInterface
{
    public function findId($id);
    
    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);
}
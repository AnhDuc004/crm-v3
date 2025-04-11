<?php

namespace Modules\KnowledgeBase\Repositories\KnowledgeBase;

interface KnowledgeBaseInterface
{
    public function findId($id);

    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);
}
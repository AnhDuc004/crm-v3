<?php

namespace Modules\KnowledgeBase\Repositories\KnowledgeBaseGroup;

interface KnowledgeBaseGroupInterface
{
    public function findId($id);

    public function listAll($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);
}
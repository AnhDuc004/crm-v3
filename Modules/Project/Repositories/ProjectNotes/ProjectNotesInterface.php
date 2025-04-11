<?php

namespace Modules\Project\Repositories\ProjectNotes;

interface ProjectNotesInterface
{

    public function listAll($request);

    public function create($request, $id);

    public function destroy($id);

    public function update($id, $request);

    public function getListByProject($id ,$request);

}

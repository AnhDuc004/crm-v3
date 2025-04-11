<?php

namespace Modules\Project\Repositories\ProjectActivity;

interface ProjectActivityInterface
{

    public function listAll($queryData);

    public function getListByProject($id, $queryData);

    public function destroy($id);

}

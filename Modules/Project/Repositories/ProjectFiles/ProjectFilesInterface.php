<?php

namespace Modules\Project\Repositories\ProjectFiles;

interface ProjectFilesInterface
{

    public function listAll($request);

    public function create($requestData, $id);

    public function destroy($id);

    public function update($id, $requestData);

    public function uploadFileByProject($id, $request);

    public function getListByProject($id, $request);

    public function changeVisibleToCustomer($id, $request);

}

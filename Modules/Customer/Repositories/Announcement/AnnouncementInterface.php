<?php

namespace Modules\Customer\Repositories\Announcement;

interface AnnouncementInterface
{
    public function findId($id);

    public function listAll($queryData);

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
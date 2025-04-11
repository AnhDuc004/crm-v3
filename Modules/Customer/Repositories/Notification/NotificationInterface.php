<?php

namespace Modules\Customer\Repositories\Notification;

interface NotificationInterface
{
    public function findId($id);

    public function listByStaff($request);

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function isRead($id, $request);
}
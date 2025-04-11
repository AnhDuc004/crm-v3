<?php

namespace Modules\Customer\Repositories\Notification;

use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\Notifications;

class NotificationRepository implements NotificationInterface
{

    public function findId($id)
    {
        $notification = Notifications::find($id);
        if (!$notification) {
            return null;
        }
        return $notification;
    }

    public function listByStaff($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;
        $baseQuery = Notifications::where('touserid', Auth::user()->staffid);
        $notification = $baseQuery->with('staff');

        if ($limit > 0) {
            $notification = $baseQuery->paginate($limit);
        } else {
            $notification = $baseQuery->get();
        }

        return $notification;
    }


    public function create($requestData)
    {
        $notification =  new Notifications($requestData);
        $notification->save();
        return $notification;
    }

    public function update($id, $requestData)
    {
        $notification = Notifications::find($id);
        if (!$notification) {
            return null;
        }

        $notification->fill($requestData);
        $notification->save();

        return $notification;
    }

    public function destroy($id)
    {
        $notification = Notifications::find($id);
        if (!$notification) {
            return null;
        }
        $notification->delete();
        return $notification;
    }

    public function isRead($id, $request)
    {
        $isRead = $request['isRead'];
        $notification = Notifications::find($id);
        $notification->is_read = $isRead;
        $notification->save();
        return $notification;
    }
}

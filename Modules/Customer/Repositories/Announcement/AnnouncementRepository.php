<?php

namespace Modules\Customer\Repositories\Announcement;
use Modules\Customer\Entities\Announcements;

class AnnouncementRepository implements AnnouncementInterface
{
    public function findId($id)
    {
        $announcement = Announcements::find($id);
        if (!$announcement) {
            return null;
        }
        return $announcement;
    }

    public function listAll($queryData)
    {
        $limit = isset($queryData["limit"]) && ctype_digit($queryData["limit"]) ? (int) $queryData["limit"] : 0;

        $baseQuery = Announcements::query();

        $announcement = $baseQuery->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $announcement = $baseQuery->paginate($limit);
        } else {
            $announcement = $baseQuery->get();
        }

        return $announcement;
    }

    public function create($requestData)
    {
        $announcement =  new Announcements($requestData);
        $announcement->save();
        return $announcement;
    }

    public function update($id, $requestData)
    {
        $announcement = Announcements::find($id);
        if (!$announcement) {
            return null;
        }
        $announcement->fill($requestData);
        $announcement->save();
        return $announcement;
    }

    public function destroy($id)
    {
        $announcement = Announcements::find($id);
        if (!$announcement) {
            return null;
        }
        $announcement->delete();
        return $announcement;
    }
}

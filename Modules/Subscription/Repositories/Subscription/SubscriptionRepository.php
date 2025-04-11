<?php

namespace Modules\Subscription\Repositories\Subscription;

use Carbon\Carbon;
use Modules\Customer\Entities\Customer;
use Modules\Subscription\Entities\Subscription;

class SubscriptionRepository implements SubscriptionInterface
{
    // List subscription theo id
    public function findId($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return null;
        }
        return $subscription;
    }
    // List subscription theo customer
    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request['search'] : null;
        $baseQuery = Subscription::leftJoin('clients', 'clients.clientId', '=', 'subscriptions.clientid')
            ->leftJoin('projects', 'projects.id', '=', 'subscriptions.project_id')
            ->where('clients.clientId', '=', $id);
        if (!$baseQuery) {
            return null;
        }
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('subscriptions.name', 'like',  '%' . $search . '%')
                        ->orWhere('projects.name', 'like',  '%' . $search . '%');
                }
            );
        }
        $subscription = $baseQuery->with('customer:clientId,company', 'project:id,name')->select('subscriptions.*')->orderBy('subscriptions.created_at', 'desc');
        if ($limit > 0) {
            $subscription = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $subscription = $baseQuery->get();
        }
        return $subscription;
    }

    // List subscription
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request['search'] : null;
        $baseQuery = Subscription::leftJoin('clients', 'clients.clientId', '=', 'subscriptions.clientid')
            ->leftJoin('projects', 'projects.id', '=', 'subscriptions.project_id');
        if (!$baseQuery) {
            return null;
        }
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('subscriptions.name', 'like',  '%' . $search . '%')
                        ->orWhere('projects.name', 'like',  '%' . $search . '%')
                        ->orWhere('clients.company', 'like',  '%' . $search . '%');
                }
            );
        }
        $subscription = $baseQuery->with('customer:clientId,company', 'project:id,name')->select('subscriptions.*')->orderBy('subscriptions.created_at', 'desc');;
        if ($limit > 0) {
            $subscription = $baseQuery->paginate($limit);
        } else {
            $subscription = $baseQuery->get();
        }

        return $subscription;
    }
    // Thêm mới subscription
    public function create($request)
    {
        $subscription = new Subscription($request);
        $subscription->hash = md5($request['hash']);
        $subscription->created = Carbon::now();
        $subscription->save();
        $data = Subscription::where('id', $subscription->id)->with('customer')->get();
        return $data;
    }
    // Thêm mới subscription theo customer
    public function createByCustomer($id, $request)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        $subscription = new Subscription($request);
        $subscription->clientid = $id;
        $subscription->hash = md5($request['hash']);
        $subscription->created = Carbon::now();
        $subscription->save();
        $data = Subscription::where('id', $subscription->id)->with('customer')->get();
        return $data;
    }

    // Cập nhật subscription
    public function update($id, $request)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return null;
        }
        $subscription->fill($request);
        $subscription->save();
        return $subscription;
    }

    // Xóa subscription
    public function destroy($id)
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return null;
        }
        $subscription->delete();
        return $subscription;
    }

    public function countNotSubscribed()
    {
        $subscriptions = Subscription::all();
        $count = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->status == 1) {
                $count++;
            }
        }
        return $count;
    }

    public function countActive()
    {
        $subscriptions = Subscription::all();
        $count = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->status == 2) {
                $count++;
            }
        }
        return $count;
    }

    public function countFuture()
    {
        $subscriptions = Subscription::all();
        $count = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->status == 3) {
                $count++;
            }
        }
        return $count;
    }

    public function countPastDue()
    {
        $subscriptions = Subscription::all();
        $count = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->status == 4) {
                $count++;
            }
        }
        return $count;
    }

    public function countPaid()
    {
        $subscriptions = Subscription::all();
        $count = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->status == 5) {
                $count++;
            }
        }
        return $count;
    }

    public function countIncomplete()
    {
        $subscriptions = Subscription::all();
        $count = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->status == 6) {
                $count++;
            }
        }
        return $count;
    }

    public function countCanceled()
    {
        $subscriptions = Subscription::all();
        $count = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->status == 7) {
                $count++;
            }
        }
        return $count;
    }

    public function countIncompleteExpired()
    {
        $subscriptions = Subscription::all();
        $count = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->status == 8) {
                $count++;
            }
        }
        return $count;
    }

    public function getByProject($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request['search'] : null;
        $baseQuery = Subscription::query()->leftJoin('projects', 'subscriptions.project_id', '=', 'projects.id')
            ->leftJoin('clients', 'subscriptions.clientid', '=', 'clients.clientId')
            ->where('projects.id', $id);

        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('clients.company', 'like',  '%' . $search . '%')
                        ->orWhere('subscriptions.name', 'like',  '%' . $search . '%')
                        ->orWhere('projects.name', 'like',  '%' . $search . '%');
                }
            );
        }
        $baseQuery = $baseQuery->with('project:id,name', 'customer:clientId,company')->select('subscriptions.*');
        if ($limit > 0) {
            $subscription = $baseQuery->paginate($limit);
        } else {
            $subscription = $baseQuery->get();
        }
        return $subscription;
    }
}

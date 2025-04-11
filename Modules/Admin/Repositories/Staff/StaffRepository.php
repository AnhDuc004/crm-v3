<?php

namespace Modules\Admin\Repositories\Staff;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Entities\Staff;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Sale\Entities\Estimate;
use Modules\Sale\Entities\Invoice;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StaffRepository implements StaffInterface
{
    const DEFAULT_PASSWORD = 'password123';
    const DEFAULT_PROFILE_IMAGE = 'default.jpg';
    const DEFAULT_NAME = 'Unknown User';
    public function findId($id)
    {
        return Staff::with('user', 'user.roles', 'department', 'user.permissions', 'user.roles.permissions')->find($id);
    }

    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $order_name = isset($request['order_name']) ? $request['order_name'] : 'id';
        $order_type = isset($request['order_type']) ? $request['order_type'] : 'desc';

        $baseQuery = Staff::query();

        // Áp dụng điều kiện tìm kiếm
        if ($search) {
            $baseQuery = $baseQuery
                ->where('first_name', 'like', '%' . $search . '%')
                ->orWhere('last_name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('phone_number', 'like', '%' . $search . '%');
        }

        // Lấy nhân viên cùng với các dữ liệu liên quan
        $staff = $baseQuery->with('customFields:id,field_to,name', 'customFieldsValues', 'department', 'user')->orderBy($order_name, $order_type);

        // Nếu có phân trang, áp dụng
        if ($limit > 0) {
            $staff = $baseQuery->paginate($limit);
        } else {
            $staff = $baseQuery->get();
        }
        return $staff;
    }

    public function create(Request $request)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền: hoặc có permission create staff hoặc là admin
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('create staff', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }
        // Log::debug('Data: ', $request->all());
        $staff = new Staff($request->except(['password']));

        if (!empty($request->password)) {
            $staff->password = Hash::make($request->password);
        }

        // Xử lý ảnh đại diện của nhân viên
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('images/profile', $filename, 'public');
            $staff->profile_image = $path;
        } else {
            $staff->profile_image = self::DEFAULT_PROFILE_IMAGE;
        }
        $staff->created_by = $user->id;
        $staff->save();

        // Đồng bộ phòng ban
        if (isset($request['department'])) {
            $departmentIdsList = collect($request['department'])
                ->filter(function ($value) {
                    return is_numeric($value) && $value > 0;
                })
                ->values();

            if ($departmentIdsList->isNotEmpty()) {
                $staff->department()->sync($departmentIdsList);
            } else {
                // Log::error('Không có department hợp lệ để đồng bộ');
            }
        }

        // Xử lý các trường tùy chỉnh
        $this->handleCustomFields($staff, $request);

        // Tạo hoặc lấy thông tin user từ email
        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')) ?: self::DEFAULT_NAME,
                'password' => Hash::make($request->password ?? self::DEFAULT_PASSWORD),
            ]
        );

        $staff->user_id = $user->id;
        $staff->save();

        // Đồng bộ vai trò cho user từ request
        if (isset($request['role'])) {
            $role_id = is_string($request['role']) ? explode(',', $request['role']) : $request['role'];

            $roles = Role::whereIn('id', $role_id)->get();
            if ($roles->isNotEmpty()) {
                $user->syncRoles($roles);
                // Log::info('Roles synced for user:', [
                //     'user_id' => $user->id,
                //     'roles' => $roles->pluck('name')
                // ]);
            } else {
                // Log::error('Không tìm thấy vai trò hợp lệ với id trong mảng roles', ['roleIds' => $roleIds]);
            }
        }

        // Đồng bộ quyền cho user
        if ($request->has('permissions')) {
            $permissionIds = is_string($request->permissions)
                ? explode(',', $request->permissions)
                : $request->permissions;

            $permissionIds = collect($permissionIds)
                ->filter(fn($value) => is_numeric($value))
                ->values()
                ->toArray();

            if (!empty($permissionIds)) {
                $permissions = Permission::whereIn('id', $permissionIds)->get();
                if ($permissions->isNotEmpty()) {
                    $user->syncPermissions($permissions);
                    // Log::info('Permissions synced for user:', [
                    //     'user_id' => $user->id,
                    //     'permissions' => $permissions->pluck('name')
                    // ]);
                }
            }
        }

        $staff = $staff->load('customFields:id,field_to,name', 'customFieldsValues', 'department', 'user.roles', 'user.permissions');

        return $staff;
    }

    public function update($id, Request $request)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền: hoặc có permission edit staff hoặc là admin
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('edit staff', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }
        $staff = Staff::findOrFail($id);
        $staff->fill($request->except(['password']));

        if (!empty($request->password)) {
            $staff->password = Hash::make($request->password);
        }

        $staff->save();

        // Đồng bộ phòng ban
        if (isset($request['department'])) {
            $departmentIdsList = collect($request['department'])
                ->filter(function ($value) {
                    return is_numeric($value) && $value > 0;
                })
                ->values();

            if ($departmentIdsList->isNotEmpty()) {
                $staff->department()->sync($departmentIdsList);
            } else {
                // Log::error('Không có department hợp lệ để đồng bộ');
            }
        }

        // Xử lý các trường tùy chỉnh
        $this->handleCustomFields($staff, $request);

        // Tạo hoặc lấy thông tin user từ email
        $user = $staff->user ?? User::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')) ?: self::DEFAULT_NAME,
                'password' => Hash::make($request->password ?? self::DEFAULT_PASSWORD),
            ]
        );

        $staff->user_id = $user->id;
        $staff->save();

        // Xử lý vai trò cho user
        if (isset($request['roles'])) {
            $role_id = is_string($request['roles']) ? explode(',', $request['roles']) : $request['roles'];
            $roles = Role::whereIn('id', $role_id)->get();
            if ($roles->isNotEmpty()) {
                $user->syncRoles($roles);
                Log::info('Roles synced for user:', [
                    'user_id' => $user->id,
                    'roles' => $roles->pluck('name')
                ]);
            } else {
                Log::error('Không tìm thấy vai trò hợp lệ với id trong mảng roles', ['roleIds' => $role_id]);
            }
        }

        if ($request->has('permissions')) {
            $permissionIds = is_string($request->permissions)
                ? explode(',', $request->permissions)
                : $request->permissions;

            $permissionIds = collect($permissionIds)
                ->filter(fn($value) => is_numeric($value))
                ->values()
                ->toArray();

            if (!empty($permissionIds)) {
                $permissions = Permission::whereIn('id', $permissionIds)->get();
                $user->syncPermissions($permissions);
            } else {
                // Nếu permissions rỗng, xóa toàn bộ quyền của user
                $user->syncPermissions([]);
                // Log::info('All permissions removed for user:', [
                //     'user_id' => $user->id
                // ]);
            }
        }

        $staff = $staff->load('customFields:id,field_to,name', 'customFieldsValues', 'department', 'user.roles', 'user.permissions');

        return $staff;
    }

    public function updateProfileImage(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        if (!$request->hasFile('profile_image')) {
            return null;
        }
        $file = $request->file('profile_image');
        $filename = $file->getClientOriginalName();
        $path = $file->storeAs('images/profile', $filename, 'public');

        $staff->profile_image = $path;
        $staff->save();

        return $staff->profile_image;
    }


    public function destroy($id)
    {
        $user = auth()->guard('api')->user();

        if (!$user) {
            return null;
        }

        // Kiểm tra quyền: hoặc có permission delete staff hoặc là admin
        $role = $user->roles->pluck('id')->toArray();
        $admin_roles = [7, 9, 10]; // Danh sách role admin

        if (!$user->hasPermissionTo('delete staff', 'web') && !array_intersect($role, $admin_roles)) {
            return null;
        }
        $staff = Staff::find($id);
        $staff->delete();
        return $staff;
    }

    public function toggleActive($id)
    {
        $staff = Staff::find($id);
        $user = User::find($staff->user_id);
        if (!$staff || !$user) {
            return null;
        }
        $user->active = !$user->active;
        $user->save();
        return $user;
    }

    // private function handleProfileImageUpload($staff, $request)
    // {
    //     $path = 'images' . DIRECTORY_SEPARATOR . 'staff' . DIRECTORY_SEPARATOR;
    //     $file = isset($request['profile_image']) ? $request['profile_image'] : null;
    //     if ($file) {
    //         $filename = $file->getClientOriginalName();
    //         $location = public_path($path);
    //         if (!File::exists($location)) {
    //             File::makeDirectory($location);
    //         }
    //         Image::make($file)->save($location . $filename);
    //         $staff->profile_image = DIRECTORY_SEPARATOR . $path . $filename;
    //     }
    // }

    private function handleCustomFields($staff, $request)
    {
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $staff->id; // Thay relid thành rel_id
                $customFields->field_to = 'staff'; // Thay fieldto thành field_to
                $customFields->save();
            }
        }
    }

    public function getListByTask($request)
    {
        $staff = Staff::leftJoin('task_assigned', 'task_assigned.id', '=', 'staff.id')->select('staff.id', 'staff.first_name', 'staff.last_name')->distinct()->get();
        return $staff;
    }

    public function getListByTicket($request)
    {
        $staff = Staff::leftJoin('tickets', 'tickets.assigned', '=', 'staff.id')->select('staff.id', 'staff.first_name', 'staff.last_name')->distinct()->get();
        return $staff;
    }

    public function getListByProposal($request)
    {
        $staff = Staff::leftJoin('proposals', 'proposals.assigned', '=', 'staff.id')->select('staff.id', 'staff.first_name', 'staff.last_name')->distinct()->get();
        return $staff;
    }

    public function getListByEstimate($staffId)
    {
        // Log thông tin staffId để kiểm tra
        Log::debug('Staff ID: ' . $staffId);
        $estimates = Estimate::where('sale_agent', $staffId)
            ->with(
                'customer:id,company',
                'project:id,name',
                'tags',
                'itemable',
                'customFields:id,field_to,name',
                'customFieldsValues'
            )
            ->get();
        if ($estimates->isEmpty()) {
            return null;
        }
        return $estimates;
    }

    public function getListByInvoice($staffId)
    {
        Log::debug('Staff ID: ' . $staffId);
        $invoices = Invoice::where('sale_agent', $staffId)
            ->with(
                'record',
                'customer:id,company',
                'project:id,name',
                'tags',
                'itemable.itemTax',
                'customFields:id,field_to,name',
                'customFieldsValues'
            )
            ->get();

        if ($invoices->isEmpty()) {
            return null;
        }
        return $invoices;
    }
}

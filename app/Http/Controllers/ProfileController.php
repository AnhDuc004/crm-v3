<?php

namespace App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Modules\Admin\Entities\Staff;

class ProfileController extends Controller
{
    use ResponseTrait;

    const PASSWORD_CHANGED = 'Đổi mật khẩu thành công!';
    const PASSWORD_INVALID = 'Mật khẩu cũ không đúng!';
    const PASSWORD_DEFAULT_ERR = 'Đổi mật khẩu không thành công!';
    const USER_NOT_FOUND = 'User không tồn tại!';

    public function __construct(private AuthRepository $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Authentication"},
     *     summary="User profile",
     *     description="User profile",
     *     operationId="profile",
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        try {
            $user = $this->auth->getUserProfile();
            return Result::success($user);
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="User logout",
     *     description="User logout",
     *     operationId="logout",
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        try {
            Auth::guard()->user()->token()->revoke();
            Auth::guard()->user()->token()->delete();
            return $this->responseSuccess([], 'User logged out successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }

    public function roles(): JsonResponse
    {
        try {
            return $this->responseSuccess(Role::with('permissions')->get(), 'All roles in system.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }

    public function permissions(): JsonResponse
    {
        try {
            return $this->responseSuccess(Permission::get(), 'All permissions in system.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }

    public function StaffPermissions($id): JsonResponse
    {
        try {
            $staff = Staff::with('user', 'user.roles', 'user.roles.permissions')->find($id);
            return $this->responseSuccess($staff, 'User Information.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/change-password",
     *     tags={"Authentication"},
     *     summary="Thay đổi mật khẩu",
     *     description="Thay đổi mật khẩu",
     *     security={{ "bearer": {} }},
     *     operationId="changePassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldpassword123"),
     *             @OA\Property(property="new_password", type="string", example="newpassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đổi mật khẩu thành công!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid password",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Mật khẩu cũ không đúng!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);
        $user = auth()->user();
        $result = $this->auth->changePassword($user, $request->current_password, $request->new_password);

        if (!$result) {
            return Result::fail(self::PASSWORD_INVALID);
        }
        return Result::success(self::PASSWORD_CHANGED);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/reset-password/{id}",
     *     summary="Reset mật khẩu người dùng về mặc định",
     *     description="Admin đặt lại mật khẩu của user về giá trị mặc định từ file passworddefault.txt.",
     *     tags={"Admin"},
     *     security={{"bearer":{}}},
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của user cần reset mật khẩu",
     *         @OA\Schema(type="integer")
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Mật khẩu đã được đặt lại mặc định",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Mật khẩu đã được đặt lại mặc định"),
     *             @OA\Property(property="user_id", type="integer", example=26)
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy user",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi từ server hoặc không tìm thấy file passworddefault.txt",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="File passworddefault.txt không tồn tại")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request, $id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return Result::fail('Staff không tồn tại');
        }

        $user = User::find($staff->user_id);

        if (!$user) {
            return Result::fail(self::USER_NOT_FOUND);
        }
        $authRepo = new AuthRepository();
        $result = $authRepo->resetPasswordToDefault($user);
        if (empty($result)) {
            return Result::fail(self::PASSWORD_DEFAULT_ERR);
        }
        return Result::success($result);
    }
}

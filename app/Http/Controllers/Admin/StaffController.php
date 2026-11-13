<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Đảm bảo bảng roles luôn có đủ 4 quyền cơ bản của hệ thống.
     */
    private function ensureDefaultRolesExist(): void
    {
        Role::firstOrCreate(['id' => 1], ['name' => Role::ADMIN]);
        Role::firstOrCreate(['id' => 2], ['name' => Role::CUSTOMER]);
        Role::firstOrCreate(['id' => 3], ['name' => Role::WAREHOUSE_STAFF]);
        Role::firstOrCreate(['id' => 4], ['name' => Role::SALES_STAFF]);
    }

    /**
     * Danh sách nhân viên & phân quyền tài khoản.
     * Hỗ trợ tìm kiếm theo tên/email/SĐT/username và lọc theo nhóm quyền (role).
     */
    public function index(Request $request): View
    {
        $this->ensureDefaultRolesExist();

        $keyword = trim($request->input('q', ''));
        $roleFilter = trim($request->input('role', 'staff_only')); // 'staff_only' | 'all' | 'admin' | 'warehouse_staff' | 'sales_staff' | 'customer'

        $query = User::with('role')->search($keyword);

        if ($roleFilter === 'staff_only') {
            $query->staff();
        } elseif ($roleFilter !== 'all' && $roleFilter !== '') {
            $query->whereHas('role', fn ($q) => $q->where('name', $roleFilter));
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();
        $roles = Role::orderBy('id')->get();

        // Thống kê số lượng theo từng nhóm quyền
        $roleCounts = [
            'all'             => User::count(),
            'staff_only'      => User::staff()->count(),
            'admin'           => User::whereHas('role', fn ($q) => $q->where('name', Role::ADMIN))->count(),
            'warehouse_staff' => User::whereHas('role', fn ($q) => $q->where('name', Role::WAREHOUSE_STAFF))->count(),
            'sales_staff'     => User::whereHas('role', fn ($q) => $q->where('name', Role::SALES_STAFF))->count(),
            'customer'        => User::whereHas('role', fn ($q) => $q->where('name', Role::CUSTOMER))->count(),
        ];

        return view('admin.staff.index', compact(
            'users',
            'roles',
            'keyword',
            'roleFilter',
            'roleCounts'
        ));
    }

    /**
     * Hiển thị form thêm tài khoản nhân viên mới.
     */
    public function create(): View
    {
        $this->ensureDefaultRolesExist();

        $staffRoles = Role::whereIn('name', [
            Role::WAREHOUSE_STAFF,
            Role::SALES_STAFF,
            Role::ADMIN,
        ])->orderBy('id')->get();

        return view('admin.staff.create', compact('staffRoles'));
    }

    /**
     * Lưu tài khoản nhân viên mới vào CSDL.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureDefaultRolesExist();

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'username'  => 'required|string|max:50|unique:users,username',
            'email'     => 'required|string|email|max:100|unique:users,email',
            'phone'     => 'nullable|string|max:20',
            'role_id'   => 'required|exists:roles,id',
            'password'  => 'required|string|min:8|confirmed',
        ], [
            'username.unique'    => 'Tên đăng nhập này đã tồn tại.',
            'email.unique'       => 'Email này đã được sử dụng.',
            'password.min'       => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = User::create([
            'role_id'   => (int) $validated['role_id'],
            'full_name' => $validated['full_name'],
            'username'  => $validated['username'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Đã tạo tài khoản nhân viên "' . $user->full_name . '" (' . ($user->role->label ?? '') . ') thành công!');
    }

    /**
     * Cập nhật phân quyền (Role) cho một tài khoản.
     * Quy tắc:
     * - Admin có thể phân quyền tài khoản thành: Nhân viên kho, Nhân viên bán hàng, Quản trị viên, hoặc Khách hàng.
     * - Không cho phép tự hạ quyền của chính tài khoản Admin đang đăng nhập.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $this->ensureDefaultRolesExist();

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $newRole = Role::findOrFail((int) $validated['role_id']);

        // Chặn tự hạ quyền chính mình
        if ($user->id === Auth::id() && $newRole->name !== Role::ADMIN) {
            return redirect()->back()
                ->with('error', 'Không thể tự hạ quyền Quản trị viên của tài khoản đang đăng nhập.');
        }

        $oldRoleLabel = $user->role->label ?? $user->role->name ?? 'Chưa rõ';
        $user->update(['role_id' => $newRole->id]);

        return redirect()->back()
            ->with('success', 'Đã cập nhật quyền cho "' . $user->full_name . '" từ [' . $oldRoleLabel . '] sang [' . $newRole->label . '].');
    }

    /**
     * Khóa / Mở khóa tài khoản nhân viên.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'Không thể tự khóa tài khoản đang đăng nhập.');
        }

        $newStatus = !$user->is_active;
        $user->update(['is_active' => $newStatus]);

        $actionText = $newStatus ? 'mở khóa' : 'khóa';

        return redirect()->back()
            ->with('success', 'Đã ' . $actionText . ' tài khoản "' . $user->full_name . '" thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa chi tiết tài khoản nhân viên (đổi quyền, khóa/mở khóa, đổi thông tin).
     */
    public function edit(User $user): View
    {
        $this->ensureDefaultRolesExist();

        $roles = Role::orderBy('id')->get();
        $isCurrentUser = ($user->id === Auth::id());

        return view('admin.staff.edit', compact('user', 'roles', 'isCurrentUser'));
    }

    /**
     * Cập nhật thông tin chi tiết tài khoản nhân viên.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureDefaultRolesExist();

        $isCurrentUser = ($user->id === Auth::id());

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email'     => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'phone'     => 'nullable|string|max:20',
            'role_id'   => 'required|exists:roles,id',
            'password'  => 'nullable|string|min:8|confirmed',
        ], [
            'full_name.required' => 'Họ và tên không được để trống.',
            'email.required'     => 'Địa chỉ email không được để trống.',
            'email.unique'       => 'Email này đã được sử dụng bởi tài khoản khác.',
            'password.min'       => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $newRole = Role::findOrFail((int) $validated['role_id']);

        // Chặn tự hạ quyền chính mình
        if ($isCurrentUser && $newRole->name !== Role::ADMIN) {
            return redirect()->back()
                ->with('error', 'Không thể tự hạ quyền Quản trị viên của tài khoản đang đăng nhập.')
                ->withInput();
        }

        // Xử lý trạng thái khóa/mở khóa
        $isActive = $request->boolean('is_active', true);
        if ($isCurrentUser && !$isActive) {
            return redirect()->back()
                ->with('error', 'Không thể tự khóa tài khoản của chính mình.')
                ->withInput();
        }

        $updateData = [
            'full_name' => $validated['full_name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'role_id'   => $newRole->id,
            'is_active' => $isActive,
        ];

        // Nếu có nhập mật khẩu mới
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Đã cập nhật thông tin tài khoản "' . $user->full_name . '" thành công!');
    }

    /**
     * Xóa tài khoản nhân viên.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'Không thể tự xóa tài khoản của chính mình.');
        }

        // Chặn xóa nếu đây là tài khoản Quản trị viên và chỉ còn 1 Quản trị viên duy nhất
        if ($user->isAdmin()) {
            $adminCount = User::whereHas('role', fn ($q) => $q->where('name', Role::ADMIN))->count();
            if ($adminCount <= 1) {
                return redirect()->back()
                    ->with('error', 'Không thể xóa Quản trị viên duy nhất của hệ thống.');
            }
        }

        $userName = $user->full_name;
        $user->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Đã xóa tài khoản "' . $userName . '" thành công.');
    }
}

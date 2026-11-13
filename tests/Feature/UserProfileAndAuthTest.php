<?php

namespace Tests\Feature;

use App\Models\PasswordResetOtp;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileAndAuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['id' => 1, 'name' => 'admin']);
        $roleCustomer = Role::create(['id' => 2, 'name' => 'customer']);

        $this->customer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'test_user',
            'email'     => 'test_user@gmail.com',
            'password'  => Hash::make('old_password123'),
            'full_name' => 'Nguyễn Văn Test',
            'phone'     => '0987654321',
            'address'   => '123 Đường Thủ Công, Hà Nội',
            'is_active' => true,
        ]);

        $roleAdmin = Role::find(1);
        $this->admin = User::create([
            'role_id'   => $roleAdmin->id,
            'username'  => 'admin_user',
            'email'     => 'admin_user@gmail.com',
            'password'  => Hash::make('admin_password123'),
            'full_name' => 'Quản trị viên Hệ thống',
            'phone'     => '0901234567',
            'address'   => 'Trụ sở Shop Handmade',
            'is_active' => true,
        ]);
    }

    /**
     * Khách vãng lai chưa đăng nhập bị chuyển hướng về login khi vào admin
     */
    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Tài khoản Customer không được phép truy cập khu vực Admin (403 Forbidden)
     */
    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->customer)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Tài khoản Admin truy cập bình thường khu vực Admin
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /**
     * 1. Khách vãng lai chưa đăng nhập không thể vào trang profile
     */
    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }

    /**
     * 2. Người dùng đã đăng nhập có thể xem thông tin cá nhân
     */
    public function test_user_can_view_profile_page(): void
    {
        $response = $this->actingAs($this->customer)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Nguyễn Văn Test');
        $response->assertSee('test_user@gmail.com');
        $response->assertSee('123 Đường Thủ Công, Hà Nội');
    }

    /**
     * 3. Người dùng cập nhật họ tên, email, số điện thoại, địa chỉ thành công
     */
    public function test_user_can_update_profile_information(): void
    {
        $response = $this->actingAs($this->customer)->put('/profile', [
            'full_name' => 'Nguyễn Văn Update',
            'email'     => 'updated_user@gmail.com',
            'phone'     => '0911223344',
            'address'   => '456 Phố Mới, TP. Hồ Chí Minh',
        ]);

        $response->assertRedirect('/profile');
        $this->customer->refresh();
        $this->assertEquals('Nguyễn Văn Update', $this->customer->full_name);
        $this->assertEquals('updated_user@gmail.com', $this->customer->email);
        $this->assertEquals('0911223344', $this->customer->phone);
        $this->assertEquals('456 Phố Mới, TP. Hồ Chí Minh', $this->customer->address);
    }

    /**
     * Người dùng không thể đổi sang email đã được tài khoản khác sử dụng
     */
    public function test_user_cannot_update_profile_with_duplicate_email(): void
    {
        $response = $this->actingAs($this->customer)->put('/profile', [
            'full_name' => 'Nguyễn Văn Test',
            'email'     => 'admin_user@gmail.com',
            'phone'     => '0987654321',
            'address'   => '123 Đường Thủ Công, Hà Nội',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->customer->refresh();
        $this->assertEquals('test_user@gmail.com', $this->customer->email);
    }

    /**
     * Bắt lỗi khi email không đúng định dạng
     */
    public function test_user_cannot_update_profile_with_invalid_email_format(): void
    {
        $response = $this->actingAs($this->customer)->put('/profile', [
            'full_name' => 'Nguyễn Văn Test',
            'email'     => 'not-a-valid-email',
            'phone'     => '0987654321',
            'address'   => '123 Đường Thủ Công, Hà Nội',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Bắt lỗi khi số điện thoại không đúng định dạng
     */
    public function test_user_cannot_update_profile_with_invalid_phone_format(): void
    {
        $response = $this->actingAs($this->customer)->put('/profile', [
            'full_name' => 'Nguyễn Văn Test',
            'email'     => 'test_user@gmail.com',
            'phone'     => 'abcdef123',
            'address'   => '123 Đường Thủ Công, Hà Nội',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    /**
     * 4. Đổi mật khẩu thất bại nếu nhập sai mật khẩu hiện tại
     */
    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $response = $this->actingAs($this->customer)->put('/profile/password', [
            'current_password'      => 'wrong_password',
            'password'              => 'new_password123',
            'password_confirmation' => 'new_password123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->customer->refresh();
        $this->assertTrue(Hash::check('old_password123', $this->customer->password));
    }

    /**
     * 5. Đổi mật khẩu thành công khi nhập đúng mật khẩu hiện tại
     */
    public function test_user_can_change_password_successfully(): void
    {
        $response = $this->actingAs($this->customer)->put('/profile/password', [
            'current_password'      => 'old_password123',
            'password'              => 'brand_new_pass123',
            'password_confirmation' => 'brand_new_pass123',
        ]);

        $response->assertRedirect('/profile');
        $this->customer->refresh();
        $this->assertTrue(Hash::check('brand_new_pass123', $this->customer->password));
    }

    /**
     * 6. Quên mật khẩu: Gửi OTP thất bại nếu email chưa đăng ký
     */
    public function test_forgot_password_send_otp_fails_for_non_existent_email(): void
    {
        $response = $this->postJson('/forgot-password/send-otp', [
            'email' => 'notfound@example.com',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * 7. Quên mật khẩu: Gửi OTP thành công và lưu mã vào database
     */
    public function test_forgot_password_send_otp_success_and_stores_otp(): void
    {
        $response = $this->postJson('/forgot-password/send-otp', [
            'email' => 'test_user@gmail.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('password_reset_otps', [
            'email' => 'test_user@gmail.com',
        ]);
    }

    /**
     * 8. Quên mật khẩu: Xác thực OTP thất bại nếu nhập sai mã
     */
    public function test_forgot_password_verify_otp_fails_with_invalid_otp(): void
    {
        PasswordResetOtp::create([
            'email'      => 'test_user@gmail.com',
            'otp'        => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/forgot-password/verify-otp', [
            'email' => 'test_user@gmail.com',
            'otp'   => '999999',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * 9. Quên mật khẩu: Xác thực OTP thành công nếu nhập đúng mã
     */
    public function test_forgot_password_verify_otp_success(): void
    {
        PasswordResetOtp::create([
            'email'      => 'test_user@gmail.com',
            'otp'        => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/forgot-password/verify-otp', [
            'email' => 'test_user@gmail.com',
            'otp'   => '123456',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * 10. Quên mật khẩu: Đặt lại mật khẩu mới thành công và đăng nhập được
     */
    public function test_forgot_password_reset_password_success(): void
    {
        PasswordResetOtp::create([
            'email'      => 'test_user@gmail.com',
            'otp'        => '654321',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/forgot-password/reset', [
            'email'                 => 'test_user@gmail.com',
            'otp'                   => '654321',
            'password'              => 'reset_secret_password',
            'password_confirmation' => 'reset_secret_password',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Kiểm tra OTP đã bị xóa sau khi sử dụng
        $this->assertDatabaseMissing('password_reset_otps', [
            'email' => 'test_user@gmail.com',
        ]);

        // Đăng nhập thử với mật khẩu mới
        $loginResponse = $this->post('/login', [
            'account'  => 'test_user@gmail.com',
            'password' => 'reset_secret_password',
        ]);

        $loginResponse->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    /**
     * 11. Đăng ký tài khoản mới thành công và đăng xuất
     */
    public function test_guest_can_register_and_logout(): void
    {
        \App\Models\PasswordResetOtp::create([
            'email'      => 'new_customer_01@gmail.com',
            'otp'        => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->post('/register', [
            'full_name'             => 'Khách Hàng Đăng Ký Mới',
            'username'              => 'new_customer_01',
            'email'                 => 'new_customer_01@gmail.com',
            'phone'                 => '0912345678',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'otp'                   => '123456',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'new_customer_01',
            'role_id'  => 2,
        ]);

        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /**
     * 12. Giá trị biên: Quên mật khẩu từ chối OTP đã hết hạn và mật khẩu thiếu ký tự đặc biệt
     */
    public function test_forgot_password_rejects_expired_otp_and_password_without_special_char(): void
    {
        // OTP đã hết hạn 1 phút trước
        PasswordResetOtp::create([
            'email'      => 'test_user@gmail.com',
            'otp'        => '112233',
            'expires_at' => now()->subMinute(),
        ]);

        $expiredVerify = $this->postJson('/forgot-password/verify-otp', [
            'email' => 'test_user@gmail.com',
            'otp'   => '112233',
        ]);
        $expiredVerify->assertStatus(422)->assertJson(['success' => false]);

        // OTP còn hạn nhưng mật khẩu mới không có ký tự đặc biệt
        PasswordResetOtp::where('email', 'test_user@gmail.com')->delete();
        PasswordResetOtp::create([
            'email'      => 'test_user@gmail.com',
            'otp'        => '445566',
            'expires_at' => now()->addMinutes(10),
        ]);

        $noSpecialChar = $this->postJson('/forgot-password/reset', [
            'email'                 => 'test_user@gmail.com',
            'otp'                   => '445566',
            'password'              => 'OnlyLettersAnd123',
            'password_confirmation' => 'OnlyLettersAnd123',
        ]);
        $noSpecialChar->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    /**
     * 13. Giá trị biên: Không cho phép đổi mật khẩu mới trùng với mật khẩu hiện tại
     */
    public function test_user_cannot_change_password_to_same_as_current_password(): void
    {
        $response = $this->actingAs($this->customer)->put('/profile/password', [
            'current_password'      => 'old_password123',
            'password'              => 'old_password123',
            'password_confirmation' => 'old_password123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}

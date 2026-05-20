<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\UserSeeder;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_login_user()
    {
        $user = User::find(1);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => "coachtech1001",
        ]);

        $response->assertRedirect('/attendance');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_user_validate_email()
    {
        $user = User::find(1);

        $response = $this->post('/login', [
            'email' => "",
            'password' => "coachtech1001",
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_login_user_validate_password()
    {
        $user = User::find(1);

        $response = $this->post('/login', [
            'email' => "teat1@example.com",
            'password' => "",
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_login_user_validate_mismatch()
    {
        $user = User::find(1);

        $response = $this->post('/login', [
            'email' => "test1@example.com",
            'password' => "coachtech1002",
        ]);

        $response->assertSessionHasErrors([
            'email' => "ログイン情報が登録されていません",
        ]);
    }

    public function test_admin_login_user()
    {
        $user = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_user_validate_email()
    {
        $user = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "",
            'password' => "coachtech1007",
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_admin_login_user_validate_password()
    {
        $user = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "teat7@example.com",
            'password' => "",
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_admin_login_user_validate_mismatch()
    {
        $user = User::find(7);

        $response = $this->post('/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1001",
        ]);

        $response->assertSessionHasErrors([
            'email' => "ログイン情報が登録されていません",
        ]);
    }
}

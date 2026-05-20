<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_register_user()
    {
        $response = $this->post('/register', [
            'name' => "テスト三郎",
            'email' => "test10@example.com",
            'password' => "coachtech1010",
            'password_confirmation' => "coachtech1010",
        ]);

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas(User::class, [
            'email' => "test10@example.com",
        ]);
    }

    public function test_register_user_validate_name()
    {
        $response = $this->post('/register', [
            'name' => "",
            'email' => "test10@example.com",
            'password' => "coachtech1010",
            'password_confirmation' => "coachtech1010",
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    public function test_register_user_validate_email()
    {
        $response = $this->post('/register', [
            'name' => "テスト三郎",
            'email' => "",
            'password' => "coachtech1010",
            'password_confirmation' => "coachtech1010",
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_register_user_validate_password8()
    {
        $response = $this->post('/register', [
            'name' => "テスト三郎",
            'email' => "test10@example.com",
            'password' => "coa1010",
            'password_confirmation' => "coa1010",
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    public function test_register_user_validate_mismatch()
    {
        $response = $this->post('/register', [
            'name' => "テスト三郎",
            'email' => "test10@example.com",
            'password' => "coachtech1010",
            'password_confirmation' => "coachtech1011",
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    public function test_register_user_validate_password()
    {
        $response = $this->post('/register', [
            'name' => "テスト三郎",
            'email' => "test10@example.com",
            'password' => "",
            'password_confirmation' => "",
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }
}

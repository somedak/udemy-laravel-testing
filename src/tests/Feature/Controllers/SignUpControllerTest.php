<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\SignUpController
 */
class SignUpControllerTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test index */
    function ユーザー登録画面を開ける()
    {
        $this->get('signup')
            ->assertOk();
    }

    /** @test store */
    function ユーザー登録できる()
    {
        // データ検証
        // DBに保存
        // ログインさせてからマイページにリダイレクト

        // $validData = User::factory()->valid()->raw(); // これだとうまくいかない
        $validData = User::factory()->validData();

        $this->post('signup', $validData)
            ->assertOk();

        unset($validData['password']);

        $this->assertDatabaseHas('users', $validData);

        // パスワードの検証
        $user = User::firstWhere($validData);
        $this->assertNotNull($user);

        $this->assertTrue(\Hash::check('abcd1234', $user->password));
    }

    /** @test store */
    function 不正なデータではユーザー登録できない()
    {
        $url = 'signup';

        // $this->post($url, [])
        //     ->assertRedirect();

        $this->post($url, ['name' => ''])
            ->assertSessionHasErrors(['name' => '名前は必ず指定してください。']);
        $this->post($url, ['name' => str_repeat('あ', 21)])
            ->assertSessionHasErrors(['name' => '名前は、20文字以下で指定してください。']);
        $this->post($url, ['name' => str_repeat('あ', 20)])
            ->assertSessionDoesntHaveErrors('name');

        $this->post($url, ['email' => ''])
            ->assertSessionHasErrors(['email' => 'メールアドレスは必ず指定してください。']);
        $this->post($url, ['email' => 'aa@bb@cc'])
            ->assertSessionHasErrors(['email' => 'メールアドレスには、有効なメールアドレスを指定してください。']);
        $this->post($url, ['email' => 'aa@ああ.いい'])
            ->assertSessionHasErrors(['email' => 'メールアドレスには、有効なメールアドレスを指定してください。']);

        User::factory()->create(['email' => 'aaa@bbb.net']);
        $this->post($url, ['email' => 'aaa@bbb.net'])
            ->assertSessionHasErrors(['email' => 'メールアドレスの値は既に存在しています。']);

        $this->post($url, ['password' => ''])
            ->assertSessionHasErrors(['password' => 'パスワードは必ず指定してください。']);
        $this->post($url, ['password' => 'abcd123'])
            ->assertSessionHasErrors(['password' => 'パスワードは、8文字以上で指定してください。']);
        $this->post($url, ['password' => 'abcd1234'])
            ->assertSessionDoesntHaveErrors('password');
    }
}

<?php

namespace Tests\Feature\Controllers\Mypage;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Mypage\BlogMypageController
 */
class BlogMypageControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function ゲストはブログを管理できない()
    {
        $url = 'mypage/login';

        $this->get('mypage/blogs')->assertRedirect($url);
        $this->get('mypage/blogs/create')->assertRedirect($url);
        $this->post('mypage/blogs/create', [])->assertRedirect($url);
    }

    /** @test index */
    function マイページ、ブログ一覧で自分のデータのみ表示される()
    {
        $user = $this->login();

        $other = Blog::factory()->create();
        $myBlog = Blog::factory()->create(['user_id' => $user]);

        $this->get('mypage/blogs')
            ->assertOk()
            ->assertDontSee($other->title)
            ->assertSee($myBlog->title);
    }

    /** @test create */
    function マイページ、ブログの新規登録画面を開ける()
    {
        $this->login();

        $this->get('mypage/blogs/create')
            ->assertOk();
    }

    /** @test store */
    function マイページ、ブログを新規登録できる、公開の場合()
    {
        $this->login();
        
        $validData = Blog::factory()->validData();

        $this->post('mypage/blogs/create', $validData)
            ->assertRedirect('mypage/blogs/edit/1')  // SQLiteのインメモリではNo.直打ちOK
        ;

        $this->assertDatabaseHas('blogs', $validData);
    }

    /** @test store */
    function マイページ、ブログを新規登録できる、非公開の場合()
    {
        $this->login();
        
        $validData = Blog::factory()->validData();
        unset($validData['status']);

        $this->post('mypage/blogs/create', $validData)
            ->assertRedirect('mypage/blogs/edit/1')  // SQLiteのインメモリではNo.直打ちOK
        ;

        $validData['status'] = 0;
        $this->assertDatabaseHas('blogs', $validData);
    }

    /** @test store */
    function マイページ、ブログの登録時の入力チェック()
    {
        // $this->markTestIncomplete('まだ出来てない。');

        $url = 'mypage/blogs/create';

        $this->login();

        $this->from($url)->post($url, [])
            ->assertRedirect($url);

        $this->post($url, ['title' => ''])
            ->assertSessionHasErrors(['title' => 'titleは必ず指定してください。']);
        $this->post($url, ['title' => str_repeat('あ', 256)])
            ->assertSessionHasErrors(['title' => 'titleは、255文字以下で指定してください。']);
        $this->post($url, ['title' => str_repeat('あ', 255)])
            ->assertSessionDoesntHaveErrors('title');
        
        $this->post($url, ['body' => ''])
            ->assertSessionHasErrors(['body' => 'bodyは必ず指定してください。']);
    }
}

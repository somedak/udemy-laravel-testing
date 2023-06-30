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
        $this->get('mypage/blogs/edit/1')->assertRedirect($url);
        $this->post('mypage/blogs/edit/1')->assertRedirect($url);
        $this->delete('mypage/blogs/delete/1')->assertRedirect($url);
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

    /** @test edit */
    function 他人のブログの編集画面は開けない()
    {
        $blog = Blog::factory()->create();

        $this->login();

        $this->get('mypage/blogs/edit/' . $blog->id)
            ->assertForbidden();
    }

    /** @test update */
    function 他人のブログは更新できない()
    {
        $validData = [
            'title' => '新タイトル',
            'body' => '新本文',
            'status' => '1',
        ];

        $blog = Blog::factory()->create();

        $this->login();

        $url = 'mypage/blogs/edit/' . $blog->id;

        $this->post($url, $validData)
            ->assertForbidden();

        $this->assertDatabaseMissing('blogs', $validData);

        $this->assertCount(1, Blog::all());
        $this->assertEquals($blog->toArray(), Blog::first()->toArray());
    }

    /** @test destroy */
    function 他人のブログは削除できない()
    {
        $this->markTestIncomplete('まだ');
    }

    /** @test edit */
    function 自分のブログの編集画面は開ける()
    {
        $blog = Blog::factory()->create();

        $this->login($blog->user);

        $this->get('mypage/blogs/edit/' . $blog->id)
            ->assertOk();
    }

    /** @test update */
    function 自分のブログは更新できる()
    {
        $validData = [
            'title' => '新タイトル',
            'body' => '新本文',
            'status' => '1',
        ];

        $blog = Blog::factory()->create();

        $this->login($blog->user);

        $url = 'mypage/blogs/edit/' . $blog->id;

        $this->post($url, $validData)
            ->assertRedirect($url);

        $this->get($url)
            ->assertSee('ブログを更新しました。');

        $this->assertDatabaseHas('blogs', $validData);
        // ↑ 更新ではなく新規登録されたかもしれない
        $this->assertCount(1, Blog::all());

        $blog->refresh();
        $this->assertEquals('新タイトル', $blog->title);
        $this->assertEquals('新本文', $blog->body);
    }

    /** @test destroy */
    function 自分のブログは削除できる()
    {
        $blog = Blog::factory()->create();

        $this->login($blog->user);

        $this->delete('mypage/blogs/delete/' . $blog->id)
            ->assertRedirect('mypage/blogs');

        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
        $this->assertDatabaseMissing('blogs', $blog->only('id'));
        $this->assertModelMissing($blog);
        // ブログに付随するコメントの削除は省略
    }
}

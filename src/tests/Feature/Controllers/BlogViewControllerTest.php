<?php

namespace Tests\Feature\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BlogViewControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test index */
    function ブログのTOPページを開ける()
    {
        $blog1 = Blog::factory()->hasComments(1)->create();
        $blog2 = Blog::factory()->hasComments(2)->create();
        $blog3 = Blog::factory()->hasComments(3)->create();
        Blog::factory()->create(['title' => 'かきくけこ']);

        $response = $this->get('/');

        $response->assertViewIs('index')
            ->assertOk()
            ->assertSee($blog1->title)
            ->assertSee($blog2->title)
            ->assertSee($blog3->title)
            ->assertSee('かきくけこ')
            ->assertSee($blog1->user->name)
            ->assertSee($blog2->user->name)
            ->assertSee($blog3->user->name)
            ->assertSee('（1件のコメント）')
            ->assertSee('（2件のコメント）')
            ->assertSee('（3件のコメント）')
            ->assertSeeInOrder([$blog3->title, $blog2->title, $blog1->title]);
    }

    /** @test index */
    function ブログの一覧、非公開のブログは表示されない()
    {
        Blog::factory()->closed()->create([
            'title' => 'ブログA',
        ]);
        Blog::factory()->create(['title' => 'ブログB']);
        Blog::factory()->create(['title' => 'ブログC']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('ブログA')
            ->assertSee('ブログB')
            ->assertSee('ブログC');
    }

    /** @test show */
    function ブログの詳細画面が表示でき、コメントが古い順に表示される()
    {
        $blog = Blog::factory()->create();

        Comment::factory()->create([
            'created_at' => now()->sub('2 days'),
            'name' => '太郎',
            'blog_id' => $blog->id,
        ]);
        Comment::factory()->create([
            'created_at' => now()->sub('3 days'),
            'name' => '次郎',
            'blog_id' => $blog->id,
        ]);
        Comment::factory()->create([
            'created_at' => now()->sub('1 days'),
            'name' => '三郎',
            'blog_id' => $blog->id,
        ]);

        $this->get('blogs/' . $blog->id)
            ->assertOk()
            ->assertSee($blog->title)
            ->assertSee($blog->user->name)
            ->assertSeeInOrder(['次郎', '太郎', '三郎']);
    }

    /** @test show */
    function ブログで非公開のものは、詳細画面は表示できない()
    {
        $blog = Blog::factory()->closed()->create();

        $this->get('blogs/' . $blog->id)
            ->assertForbidden();
    }

    /** @test show */
    function クリスマスの日は、メリークリスマス！と表示される()
    {
        $blog = Blog::factory()->create();

        Carbon::setTestNow('2020-12-24');

        $this->get('blogs/' . $blog->id)
            ->assertOk()
            ->assertDontSee('メリークリスマス！');

        Carbon::setTestNow('2020-12-25');

        $this->get('blogs/' . $blog->id)
            ->assertOk()
            ->assertSee('メリークリスマス！');
    }
}

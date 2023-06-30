<?php

namespace Tests\Feature\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use App\StrRandom;
use Carbon\Carbon;
use Facades\Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class BlogViewControllerTest extends TestCase
{
    use RefreshDatabase;
    // use WithoutMiddleware;

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
        $this->withoutMiddleware(\App\Http\Middleware\BlogShowLimit::class);
        
        $blog = Blog::factory()->withCommentsData([
            ['created_at' => now()->sub('2 days'), 'name' => '太郎'],
            ['created_at' => now()->sub('3 days'), 'name' => '次郎'],
            ['created_at' => now()->sub('1 days'), 'name' => '三郎'],
        ])->create();

        $this->get('blogs/' . $blog->id)
            ->assertOk()
            ->assertSee($blog->title)
            ->assertSee($blog->user->name)
            ->assertSeeInOrder(['次郎', '太郎', '三郎']);
    }

    /** @test show */
    function ブログで非公開のものは、詳細画面は表示できない()
    {
        $this->withoutMiddleware(\App\Http\Middleware\BlogShowLimit::class);
        
        $blog = Blog::factory()->closed()->create();

        $this->get('blogs/' . $blog->id)
            ->assertForbidden();
    }

    /** @test show */
    function クリスマスの日は、メリークリスマス！と表示される()
    {
        $this->withoutMiddleware(\App\Http\Middleware\BlogShowLimit::class);
        
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

    /** @test show */
    function ブログの詳細画面で、ランダムな文字列が10文字表示される()
    {
        $this->withoutMiddleware(\App\Http\Middleware\BlogShowLimit::class);

        $blog = Blog::factory()->create();
        
        // Str::shouldReceive('random')
        //     ->once()->with(10)->andReturn('HELLO_RAND');

        $this->mock(StrRandom::class, function ($mock) {
            $mock->shouldReceive('random')
                ->once()->with(10)->andReturn('HELLO_RAND');
        });

        $this->get('blogs/' . $blog->id)
            ->assertOk()
            ->assertSee('HELLO_RAND');
    }
}

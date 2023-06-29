<?php

namespace Tests\Feature\Controllers\Mypage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Mypage\BlogMypageController
 */
class BlogMypageControllerTest extends TestCase
{
    /** @test index */
    function 認証している場合に限り、マイページを開ける()
    {
        // 認証していない場合
        $this->get('mypage/blogs')
            ->assertRedirect('mypage/login');
    }
}

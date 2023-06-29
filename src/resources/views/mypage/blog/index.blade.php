@extends('layouts.index')
@section('title')マイブログ一覧@endsection

@section('content')


<h1>マイブログ一覧</h1>

<a href="/mypage/blogs/create">ブログ新規登録</a>
<hr>


<table>
    <tr>
        <th>ブログタイトル</th>
    </tr>

    @foreach($blogs as $blog)
    <tr>
        <td>
            {{ $blog->title }}
        </td>
    </tr>
    @endforeach
</table>


@endsection

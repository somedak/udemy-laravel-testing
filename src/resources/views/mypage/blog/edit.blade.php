@extends('layouts.index')
@section('title')マイブログ更新 (ID: {{ $blog->id }})@endsection

@section('content')


<h1>マイブログ更新</h1>

<form method="post">
@csrf

@include('inc.error')

@include('inc.status')


タイトル：<input type="text" name="title" style="width:400px" value="{{ data_get($data, 'title') }}">
<br>
本文：<textarea name="body" style="width:600px; height:200px;">{{ data_get($data, 'body') }}</textarea>
<br>
公開する：
<label>
    <input type="checkbox" name="status" {{ data_get($data, 'status') ? 'checked': '' }} value="1">
    公開する
</label>
<br><br>
<input type="submit" value="更新する">

</form>


@endsection

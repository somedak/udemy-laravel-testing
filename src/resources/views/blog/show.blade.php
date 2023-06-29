@extends('layouts.index')

@section('content')

@if (today()->is('12-25'))
    <h1>メリークリスマス！</h1>
@endif

<h1>{{ $blog->title }}</h1>
<div>{!! nl2br(e($blog->body)) !!}</div>

<p>書き手：{{ $blog->user->name }}</p>
    
@endsection

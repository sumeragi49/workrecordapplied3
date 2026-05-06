@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}" >
@endsection

@section('content')
<div class="email-induction">
    <div class="header-title">
        <h1>登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください</h1>
    </div>

    @env('local')
    <div class="email-button">
        <a target="_blank" href="http://localhost:8025">認証はこちらから</a>
    </div>
    @endenv

    <div class="resend-form">
        <form class="resend-form-button" method="post" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection
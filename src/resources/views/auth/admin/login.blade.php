@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login_form-content">
    <div class="login_form-heading">
        <h1>管理者ログイン</h1>
    </div>
    <form class="form" action="/admin/login" method="post">
        @csrf
        <div class="form_group">
            <div class="form_group-title">
                <span>メールアドレス</span>
            </div>
            <div class="form_group-content">
                <div class="form_input">
                    <input type="text" name="email" value="{{ old('email') }}">
                </div>
                <div class="form_error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form_group">
            <div class="form_group-title">
                <span>パスワード</span>
            </div>
            <div class="form_group-content">
                <div class="form_input">
                    <input type="password" name="password">
                </div>
                <div class="form_error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form_button">
            <button class="form_button-submit" type="submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection
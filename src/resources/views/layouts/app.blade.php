<!DOCTYPE html>
<html lang="ja">

@php
    $logoutRoute = (auth()->user() && auth()->user()->role === 1)
        ? route('admin.logout')
        : route('logout');
@endphp

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header_inner">
            @auth
            <div class="header_title">
                @if(auth()->user()->role === 1)
                <a href="/admin/attendance/list">COACHTECH</a>
                @else
                <a href="/attendance">COACHTECH</a>
                @endif
            </div>
            <nav class="header_nav">
                <ul class="nav_content">
                    <li class="nav_content-list">
                        @if(auth()->user()->role === 1)
                        <a href="/admin/attendance/list">勤怠一覧</a>
                        @else
                        <a href="/attendance">勤怠</a>
                        @endif
                    </li>
                    <li class="nav_content-list">
                        @if(auth()->user()->role === 1)
                        <a href="/admin/staff/list">スタッフ一覧</a>
                        @else
                        <a href="/attendance/list">勤怠一覧</a>
                        @endif
                    </li>
                    <li class="nav_content-list">
                        @if(auth()->user()->role === 1)
                        <a href="{{ route('request.list') }}">申請一覧</a>
                        @else
                        <a href="{{ route('request.list') }}">申請</a>
                        @endif
                    </li>
                    <li class="nav_content-list">
                        <form class="logout_form" action="{{ $logoutRoute }}" method="post">
                            @csrf
                            <button class="header_nav-button" type="submit">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
            @else
            <div class="header_title">
                <a href="">COACHTECH</a>
            </div>
            @endauth
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
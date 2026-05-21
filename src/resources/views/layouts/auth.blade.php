<!DOCTYPE html>
<html lang="ja">

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
            <div class="header_title">
                <a class="header-logo" href="/attendance">
                    <img src="{{ asset('storage/images/COACHTECHヘッダーロゴ (1).png') }}" alt="COACHTECH">
                </a>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
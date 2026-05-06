@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/requestList.css') }}" >
@endsection

@section('content')
<div class="request-list-content">
    <div class="request-list-item">
        <div class="request-list-content-title">
            <h1>❙ 申請一覧</h1>
        </div>
        <div class="request-content-item">
            <div class="request-status">
                <a href="{{ route('admin.request.list', ['status' => '1']) }}" class="tab-link {{ request('status', 1) == 1 ? 'is-active' : '' }}">承認待ち</a>
                <a href="{{ route('admin.request.list', ['status' => '2']) }}" class="tab-link {{ request('status', 2) == 2 ? 'is-active' : '' }}">承認済み</a>
            </div>
            <div class="request-list-table">
                <table class="table-content">
                    <thead class="table-heading">
                        <tr>
                            <th>状態</th>
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody class="table-items">
                        @foreach($attendances as $attendance)
                            <tr>
                                <td>
                                    @if($status == 1)
                                    <span>承認待ち</span>
                                    @elseif($status == 2)
                                    <span>承認済み</span>
                                    @endif
                                </td>
                                <td>{{ $attendance['user']['name'] }}</td>
                                <td>{{ $attendance['date']->format('Y/m/d') }}</td>
                                <td>{{ $attendance['content'] }}</td>
                                <td>{{ $attendance['updated_at']->format('Y/m/d') }}</td>
                                <td>
                                    <a class="detail-attendance-link" href="{{ route('request.approval', $attendance['attendanceRequest']['id']) }}">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{ $attendances->links() }}

@endsection
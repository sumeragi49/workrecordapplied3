@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}" >
@endsection

@section('content')
<div class="index_content">
    <div class="index_content-item">
        <div class="index_title">
            <h1>❙ 勤怠一覧</h1>
        </div>
        <form class="index-month-calender" action="{{ route('admin.staff.attendance', $userId) }}" method="get">
            <div class="prev_month">
                <a href="{{ route('admin.staff.attendance', [$userId, 'month' => $prevMonth]) }}">← 前月</a>
            </div>
            <div class="this_month">
                <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()">
            </div>
            <div class="next_month">
                <a href="{{ route('admin.staff.attendance', [$userId, 'month' => $nextMonth]) }}">翌月 →</a>
            </div>
        </form>
        <div class="index_container">
            <table class="table_content">
                <thead class="table_heading">
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody class="table_items">
                    @foreach($days as $day)
                        <tr>
                            <td>{{ $day['date'] }}</td>
                            <td>{{ $day['attendance']?->time_start?->format('H:i') ?? '' }}</td>
                            <td>{{ $day['attendance']?->time_end?->format('H:i') ?? '' }}</td>
                            <td>
                                @if($day['attendance'])
                                    {{ $day['attendance']->break_total_minutes }}
                                @endif
                            </td>
                            <td>
                                @if($day['attendance'])
                                    {{ $day['attendance']->work_total_minutes }}
                                @endif
                            </td>
                            <td>
                                @if($day['attendance'])
                                    <a class="detail_attendance-link" href="{{ route('admin.attendance.show', $day['attendance']['id']) }}">詳細</a>
                                @else
                                    <form class="form-button" action="{{ route('admin.new.attendance', $userId) }}" method="post">
                                    @csrf
                                        <input type="hidden" name="date" value="{{ date('Y') . '-' . str_replace('/', '-', mb_substr($day['date'], 0, 5)) }}">
                                        <button type="submit" class="form-button-submit">詳細</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
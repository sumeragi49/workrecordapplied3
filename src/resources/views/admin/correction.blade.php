@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}" >
@endsection

@section('content')
<div class="detail_content">
@if($mode === 'approval')
    <div class="detail_content-item">
        <div class="detail_title">
            <h1>❙ 勤怠詳細</h1>
        </div>
        <form action="{{ route('approval.store', $attendanceRequest['id']) }}" class="approval_form" method="post">
            @csrf
            <table class="attendance_table">
                <input type="hidden" name="attendance_id" value="{{ $attendanceRequest['attendance']['id'] }}">
                <tr class="form_content">
                    <th class="form_title">名前</th>
                    <td class="form_item">{{ $attendanceRequest['attendance']['user']['name'] }}</td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">日付</th>
                    <td class="form_item-date">
                        <span>{{ $attendanceRequest['attendance']['date']->format('Y') }}年</span>
                        <span>{{ $attendanceRequest['attendance']['date']->format('m月d日') }}</span>
                    </td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">出勤・退勤</th>
                    <td class="form_item">
                        <span>{{ Carbon\Carbon::parse($attendanceRequest['request_time_start'])->format('H:i') }}</span>
                        <span>~</span>
                        <span>{{ Carbon\Carbon::parse($attendanceRequest['request_time_end'])->format('H:i') }}</span>
                    </td>
                </tr>
                @foreach($attendanceRequest->breakRequests as  $index => $breakRequest)
                    @if($breakRequest['request_break_start'] && $breakRequest['request_break_end'])
                    <tr class="form_content">
                        <th class="form_title">
                            <span>休憩 {{ $loop->iteration }}</span>
                        </th>
                        <td class="form_item">
                            <span>{{ Carbon\Carbon::parse($breakRequest['request_break_start'])->format('H:i') }}</span>
                            <span>~</span>
                            <span>{{ Carbon\Carbon::parse($breakRequest['request_break_end'])->format('H:i') }}</span>
                        </td>
                    </tr>
                    @else
                    <tr class="form_content">
                        <th class="form_title">
                            <span>休憩 {{ $loop->iteration }}</span>
                        </th>
                        <td class="form_item">
                            <span>{{ Carbon\Carbon::parse($breakRequest['new_break_start'])->format('H:i') }}</span>
                            <span>~</span>
                            <span>{{ Carbon\Carbon::parse($breakRequest['new_break_end'])->format('H:i') }}</span>
                        </td>
                    </tr>
                    @endif
                @endforeach
                <tr class="form_content">
                    <th class="form_title">
                        <span>備考</span>
                    </th>
                    <td class="form_item">
                        <span>{{ $attendanceRequest['request_content'] }}</span>
                    </td>
                </tr>
            </table>
            <div class="form_button">
                @if($attendanceRequest->attendance['status'] === 1)
                <button type="submit" class="form_button-submit">承認</button>
                @elseif($attendanceRequest->attendance['status'] === 2)
                <button type="submit" class="form_button-fixed" disabled>承認済み</button>
                @endif
            </div>
        </form>
    </div>
@endif
</div>
@endsection
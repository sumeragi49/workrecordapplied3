@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}" >
@endsection

@section('content')
<div class="detail_content">
@if($mode === 'edit')
    @if($attendanceRequest)
    <div class="detail_content-item">
        <div class="detail_title">
            <h1>❙ 勤怠詳細</h1>
        </div>
        <div class="request_form">
            @csrf
            <table class="attendance_table">
                <input type="hidden" name="attendance_id" value="{{ $attendances['id'] }}">
                <tr class="form_content">
                    <th class="form_title">名前</th>
                    <td class="form_item">{{ $attendances['user']['name'] }}</td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">日付</th>
                    <td class="form_item-date">
                        <span>{{ $attendances['date']->format('Y') }}年</span>
                        <span>{{ $attendances['date']->format('m年d日') }}</span>
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
                <span>・承認待ちのため修正はできません。</span>
            </div>
        </div>
    </div>
    @elseif($attendances)
    <div class="detail_content-item">
        <div class="detail_title">
            <h1>❙ 勤怠詳細</h1>
        </div>
        <form class="request_form" action="{{ route('admin.attendance.request', $attendances['id']) }}" method="post">
            @csrf
            <table class="attendance_table">
                <input type="hidden" name="attendance_id" value="{{ $attendances['id'] }}">
                <tr class="form_content">
                    <th class="form_title">名前</th>
                    <td class="form_item">{{ $attendances->user['name'] }}</td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">日付</th>
                    <td class="form_item-date">
                        <span>{{ $attendances['date']->format('Y') }}年</span>
                        <span>{{ $attendances['date']->format('m年d日') }}</span>
                    </td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">出勤・退勤</th>
                    <td class="form_item">
                        <div class="form-item-content">
                            <input type="time" name="request_time_start" value="{{ Carbon\Carbon::parse($attendances['time_start'])->format('H:i') }}">
                            <span>~</span>
                            <input type="time" name="request_time_end" value="{{ Carbon\Carbon::parse($attendances['time_end'])->format('H:i') }}">
                        </div>
                        <div class="form_error">
                            @error('request_time_start')
                            {{ $message }}
                            @enderror
                        </div>
                        <div class="form_error">
                            @error('request_time_end')
                            {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
                @foreach($breaks as  $index => $break)
                    @if($break['break_start'] && $break['break_end'])
                    <tr class="form_content">
                        <th class="form_title">
                            <span>休憩 {{ $loop->iteration }}</span>
                        </th>
                        <td class="form_item">
                            <div class="form-item-content">
                                <input type="hidden" name="break_id" value="{{ $break['id'] }}">
                                <input type="time" name="breaks[{{ $index }}][request_break_start]" value="{{ Carbon\Carbon::parse($break['break_start'])->timezone('Asia/Tokyo')->format('H:i') }}">
                                <span>~</span>
                                <input type="time" name="breaks[{{ $index }}][request_break_end]" value="{{ Carbon\Carbon::parse($break['break_end'])->timezone('Asia/Tokyo')->format('H:i') }}">
                            </div>
                            <div class="form_error">
                                @error('breaks.*.request_break_start')
                                {{ $message }}
                                @enderror
                            </div>
                            <div class="form_error">
                                @error('breaks.*.request_break_end')
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                    @else
                    <tr class="form_content">
                        <th class="form_title">
                            <span>休憩 {{ $loop->iteration }}</span>
                        </th>
                        <td class="form_item">
                            <div class="form-item-content">
                                <input type="time" name="breaks[0][new_break_start]" value="">
                                <span>~</span>
                                <input type="time" name="breaks[0][new_break_end]" value="">
                            </div>
                            <div class="form_error">
                                @error('breaks.*.new_break_start')
                                {{ $message }}
                                @enderror
                            </div>
                            <div class="form_error">
                                @error('breaks.*.new_break_end')
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                    @endif
                @endforeach
                <tr class="form_content">
                    <th class="form_title">
                        <span>備考</span>
                    </th>
                    <td class="form_item">
                        <div class="form-item-content">
                            <textarea name="request_content" value="{{ $attendances['content'] }}"></textarea>
                        </div>
                        <div class="form_error">
                            @error('request_content')
                            {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
            </table>
            <div class="form_button">
                <button class="form_button-submit">修正</button>
            </div>
        </form>
    </div>
    @else(!$attendances)
    <div class="detail_content-item">
        <div class="detail_title">
            <h1>❙ 勤怠詳細</h1>
        </div>
        <form class="request_form" action="{{ route('admin.attendance.request', $attendances['id']) }}" method="post">
            @csrf
            <table class="attendance_table">
                <input type="hidden" name="attendance_id" value="{{ $attendances['id'] }}">
                <tr class="form_content">
                    <th class="form_title">名前</th>
                    <td class="form_item">{{ $attendances->user['name'] }}</td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">日付</th>
                    <td class="form_item-date">
                        <span>{{ $attendances['date']->format('Y') }}年</span>
                        <span>{{ $attendances['date']->format('m年d日') }}</span>
                    </td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">出勤・退勤</th>
                    <td class="form_item">
                        <div class="form-item-content">
                            <input type="time" name="request_time_start" value="{{ Carbon\Carbon::parse($attendances['time_start'])->format('H:i') }}">
                            <span>~</span>
                            <input type="time" name="request_time_end" value="{{ Carbon\Carbon::parse($attendances['time_end'])->format('H:i') }}">
                        </div>
                        <div class="form_error">
                            @error('request_time_start')
                            {{ $message }}
                            @enderror
                        </div>
                        <div class="form_error">
                            @error('request_time_end')
                            {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
                @foreach($breaks as  $index => $break)
                    @if($break['break_start'] && $break['break_end'])
                    <tr class="form_content">
                        <th class="form_title">
                            <span>休憩 {{ $loop->iteration }}</span>
                        </th>
                        <td class="form_item">
                            <div class="form-item-content">
                                <input type="time" name="breaks[{{ $index }}][request_break_start]" value="{{ Carbon\Carbon::parse($break['break_start'])->timezone('Asia/Tokyo')->format('H:i') }}">
                                <span>~</span>
                                <input type="time" name="breaks[{{ $index }}][request_break_end]" value="{{ Carbon\Carbon::parse($break['break_end'])->timezone('Asia/Tokyo')->format('H:i') }}">
                            </div>
                            <div class="form_error">
                                @error('breaks.*.request_break_start')
                                {{ $message }}
                                @enderror
                            </div>
                            <div class="form_error">
                                @error('breaks.*.request_break_end')
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                    @else
                    <tr class="form_content">
                        <th class="form_title">
                            <span>休憩 {{ $loop->iteration }}</span>
                        </th>
                        <td class="form_item">
                            <div class="form-item-content">
                                <input type="time" name="breaks[0][new_break_start]" value="">
                                <span>~</span>
                                <input type="time" name="breaks[0][new_break_end]" value="">
                            </div>
                            <div class="form_error">
                                @error('breaks.*.new_break_start')
                                {{ $message }}
                                @enderror
                            </div>
                            <div class="form_error">
                                @error('breaks.*.new_break_end')
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                    @endif
                @endforeach
                <tr class="form_content">
                    <th class="form_title">
                        <span>備考</span>
                    </th>
                    <td class="form_item">
                        <div class="form-item-content">
                            <textarea name="request_content" value="{{ $attendances['content'] }}"></textarea>
                        </div>
                        <div class="form_error">
                            @error('request_content')
                            {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
            </table>
            <div class="form_button">
                <button class="form_button-submit">修正</button>
            </div>
        </form>
    </div>
    @endif
@endif

@if($mode === 'create')
    <div class="detail_content-item">
        <div class="detail_title">
            <h1>❙ 勤怠詳細</h1>
        </div>
        <form class="request_form" action="" method="post">
            @csrf
            <table class="attendance_table">
                <tr class="form_content">
                    <th class="form_title">名前</th>
                    <td class="form_item">{{ $user['name'] }}</td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">日付</th>
                    <td class="form_item-date">
                        <span>{{ $targetDate->format('Y') }}年</span>
                        <span>{{ $targetDate->format('m年d日') }}</span>
                    </td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">出勤・退勤</th>
                    <td class="form_item">
                        <div class="form-item-content">
                            <input type="time" name="time_start" value="">
                            <span>~</span>
                            <input type="time" name="time_end" value="">
                        </div>
                        <div class="form_error">
                            @error('time_start')
                            {{ $message }}
                            @enderror
                        </div>
                        <div class="form_error">
                            @error('time_end')
                            {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">
                        <span>休憩 1</span>
                    </th>
                    <td class="form_item">
                        <div class="form-item-content">
                            <input type="time" name="breaks[break_start]" value="">
                            <span>~</span>
                            <input type="time" name="breaks[break_end]" value="">
                        </div>
                        <div class="form_error">
                            @error('breaks.break_start')
                            {{ $message }}
                            @enderror
                        </div>
                        <div class="form_error">
                            @error('breaks.break_end')
                            {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
                <tr class="form_content">
                    <th class="form_title">
                        <span>備考</span>
                    </th>
                    <td class="form_item">
                        <div class="form-item-content">
                            <textarea name="request_content" value=""></textarea>
                        </div>
                        <div class="form_error">
                            @error('content')
                            {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
            </table>
            <div class="form_button">
                <button type="submit" class="form_button-submit">修正</button>
            </div>
        </form>
    </div>
@endif
</div>
@endsection
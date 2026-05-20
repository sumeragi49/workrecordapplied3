<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\BreakRequest;
use App\Http\Requests\CorrectionRequest;
use App\Http\Requests\CreateNewRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function attendance()
    {
        $user = Auth::user();

        $attendance = Attendance::with('breaks')
                    -> where('user_id', $user['id'])
                    -> whereDate('date', Carbon::today())
                    -> first();

        $status = 'not_started';
        if ($attendance) {
            if ($attendance['time_end']) {
                $status = 'finished';
            } elseif ($attendance->breaks()->whereNull('break_end')->exists()) {
                $status = 'breaking';
            } elseif ($attendance['time_start']) {
                $status = 'working';
            }
        }

        return view('staff.attendance', compact('user', 'attendance', 'status'));
    }

    public function workStart(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        Attendance::create([
            'user_id' => $user['id'],
            'date' => $now,
            'time_start' => Carbon::now(),
            'time_end' => null,
        ]);

        return redirect('/attendance');
    }

    public function workEnd(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user['id'])
                                ->whereDate('date', $now)
                                ->whereNotNull('time_start')
                                ->whereNull('time_end')
                                ->first();
        //attendance tableに登録日の「time_end」のデータがない時
        if ($attendance) {
            $attendance->update([
                'time_end' => Carbon::now()
            ]);

            return redirect('/attendance');
        }

        return redirect('/attendance');
    }

    public function breakStart(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user['id'])
                                ->whereDate('date', $now)
                                ->whereNotNull('time_start')
                                ->whereNull('time_end')
                                ->first();

        BreakTime::create([
            'user_id' => $user['id'],
            'attendance_id' => $attendance['id'],
            'break_start' => Carbon::now(),
        ]);

        return redirect('/attendance');
    }

    public function breakEnd(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $breaks = BreakTime::whereNotNull('break_start')
                ->whereNull('break_end')
                ->first();
        //attendance tableに登録日の「time_end」のデータがない時
        if ($breaks) {
            $breaks->update([
                'break_end' => Carbon::now()
            ]);

            return redirect('/attendance');
        }

        return redirect('/attendance');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $targetMonth = Carbon::parse($month);

        $prevMonth = $targetMonth->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $targetMonth->copy()->addMonthNoOverflow()->format('Y-m');

        $startDate = $targetMonth->copy()->startOfMonth();
        $endDate = $targetMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user['id'])
                    ->with('breaks')
                    ->whereBetween('time_start', [$startDate, $endDate])
                    ->get()
                    ->keyBy(function($item) {
                        return Carbon::parse($item->time_start)->isoFormat('MM/DD(ddd)');
                    });

        $days = [];
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $currentDate = $date->isoFormat('MM/DD(ddd)');
                $attendance = $attendances->get($currentDate);

                $totalBreak = $attendance ? $attendance->breaks->sum('duration') : 0;

                $days[] = [
                    'date' => $currentDate,
                    'attendance' => $attendance,
                    'total_break' => $totalBreak,
                ];
            }

        return view('staff.index', compact('user', 'days', 'month', 'prevMonth', 'nextMonth'));
    }

    public function show($attendanceId)
    {
        $user = Auth::user();

        $attendances = Attendance::with(['breaks','attendanceRequest', 'attendanceRequest.breakRequests'])
                    -> findOrFail($attendanceId);

        $breaks = $attendances->breaks->toArray();
        $attendanceRequest = AttendanceRequest::with('breakRequests')
                          -> where('attendance_id', $attendanceId)
                          -> first();

        $breaks[] = [
            'id' => null,
            'break_start' => null,
            'break_end' => null,
        ];

        $displayDate = $attendanceRequest ?: $attendances;

        $mode = 'edit';

        return view('staff.detail', compact('mode','user', 'attendances', 'breaks', 'displayDate', 'attendanceRequest'));
    }

    public function requestStore(CorrectionRequest $request, $attendanceId)
    {
        $user = Auth::user();

        $validated = $request->validated();

        $originalRecord = Attendance::with('breaks')->findOrFail($attendanceId);

        $baseDate = Carbon::parse($originalRecord['date']);

        DB::transaction(function () use ($request, $validated, $baseDate, $attendanceId) {

            $attendanceRequest = AttendanceRequest::create([
                'attendance_id' => $attendanceId,
                'request_time_start' => $baseDate->copy()->setTimeFromTimeString($validated['request_time_start']),
                'request_time_end' => $baseDate->copy()->setTimeFromTimeString($validated['request_time_end']),
                'request_content' => $validated['request_content'],
            ]);

            if ($request->filled('breaks')) {
                foreach ($request->breaks as $break) {
                    if (!empty($break['request_break_start']) && !empty($break['request_break_end'])) {
                        $attendanceRequest->breakRequests()->create([
                            'break_id' => $request->input(['break_id']),
                            'request_break_start' => Carbon::parse($baseDate)->setTimeFromTimeString($break['request_break_start']),
                            'request_break_end' => Carbon::parse($baseDate)->setTimeFromTimeString($break['request_break_end']),
                        ]);
                    }

                    if (!empty($break['new_break_start']) && !empty($break['new_break_end'])) {
                        $attendanceRequest->breakRequests()->create([
                            'new_break_start' => Carbon::parse($baseDate)->setTimeFromTimeString($break['new_break_start']),
                            'new_break_end' => Carbon::parse($baseDate)->setTimeFromTimeString($break['new_break_end']),
                        ]);
                    }
                }
            }

            Attendance::where('id', $attendanceId)->update(['status' => '1', 'content' => $validated['request_content'] ]);
        });

        return redirect()->back();
    }

    public function newAttendance(Request $request)
    {
        $user = Auth::user();

        $targetDate = \Carbon\Carbon::parse($request->input('date'));

        $mode = 'create';

        return view('staff.detail', compact('targetDate', 'user', 'mode'));
    }

    public function newAttendanceStore(CreateNewRequest $request, $targetDate, $userId)
    {
        $data = $request->getFormattedDate($targetDate);

        $attendance = null;

        DB::transaction(function () use ($data, $targetDate, $userId, &$attendance) {
            $attendance = Attendance::create([
                'user_id' => $userId,
                'date' => $targetDate,
                'time_start' => $data['time_start'],
                'time_end' => $data['time_end'],
                'content' => $data['content'],
                'status' => '1',
            ]);

            $break = BreakTime::create([
                'attendance_id' => $attendance['id'],
                'break_start' => $data['break_start'],
                'break_end' => $data['break_end'],
            ]);

            $attendanceRequest = AttendanceRequest::create([
                'attendance_id' => $attendance['id'],
                'request_time_start' => $data['time_start'],
                'request_time_end' => $data['time_end'],
                'request_content' => $data['content'],
            ]);

            $breakRequest = BreakRequest::create([
                'attendance_correct_request_id' => $attendanceRequest['id'],
                'break_id' => $break['id'],
                'request_break_start' => $data['break_start'],
                'request_break_end' => $data['break_end'],
            ]);
        });

        return redirect()->route('attendance.index');
    }
}
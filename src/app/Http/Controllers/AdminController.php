<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\BreakRequest;
use App\Http\Requests\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $dateStr = $request->input('date', now()->format('Y-m-d'));
        $currentDate = Carbon::parse($dateStr);

        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        $users = User::where('role', 0)
              -> with(['attendances' => function ($query) use ($dateStr) {
                $query->whereDate('date', $dateStr)->with('breaks');
            }])->get();

        return view('admin.date', compact('dateStr', 'users', 'prevDate', 'nextDate'));
    }

    public function show($attendanceId)
    {
        $attendances = Attendance::with(['user', 'breaks','attendanceRequest', 'attendanceRequest.breakRequests'])
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

        return view('admin.detail', compact('mode', 'attendances', 'breaks', 'displayDate', 'attendanceRequest'));
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

    public function staffList(Request $request)
    {
        $users = User::where('role', 0)
               ->with('attendances')
               ->get();

        return view('admin.staff', compact('users'));
    }

    public function staffAttendance(Request $request,$userId)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $targetMonth = Carbon::parse($month);

        $prevMonth = $targetMonth->copy()->subMonthNoOverflow()->format('Y-m');
        $nextMonth = $targetMonth->copy()->addMonthNoOverflow()->format('Y-m');

        $startDate = $targetMonth->copy()->startOfMonth();
        $endDate = $targetMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $userId)
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

        return view('admin.index', compact('userId', 'days', 'month', 'prevMonth', 'nextMonth'));
    }

    public function newAttendance(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $targetDate = \Carbon\Carbon::parse($request->input('date'));

        $mode = 'create';

        return view('admin.detail', compact('targetDate', 'user', 'mode'));
    }

    public function exportCsv($userId, $month)
    {
        $staff = User::findOrFail($userId);
        $targetDate = Carbon::parse($month)->startOfMonth();
        $daysInMonth = $targetDate->daysInMonth;

        $attendances = Attendance::with('breaks')
                    -> where('user_id', $userId)
                    -> whereBetween('date', [$targetDate->copy()->startOfMonth(), $targetDate->copy()->endOfMonth()])
                    -> get()
                    -> keyBy(function($item) {
                        return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
                    });

        return new StreamedResponse(function () use ($staff, $targetDate, $daysInMonth, $attendances) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, pack('C*', 0xEF, 0xBB, 0xBF));

            fputcsv($handle, ['日付', '氏名', '出勤時間', '退勤時間', '休憩', '合計']);

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dateString = $targetDate->copy()->day($i)->format('Y-m-d');
                $record = $attendances->get($dateString);

                $timeStart =  '-';
                $timeEnd =  '-';
                $breakTime = '00:00';
                $workTime = '00:00';

                if  ($record) {
                    $timeStart = $record->time_start ?? '-';
                    $timeEnd = $record->time_end ?? '-';

                    $breakTime = $record->getBreakTotalMinutesAttribute();

                    if ($record->time_end) {
                        $workTime = $record->getWorkTotalMinutesAttribute();
                    }
                }

                fputcsv($handle, [
                    $dateString,
                    $staff['name'],
                    $timeStart,
                    $timeEnd,
                    $breakTime,
                    $workTime
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"勤怠_{$staff['name']}_{$month}.csv\"",
        ]);
    }
}

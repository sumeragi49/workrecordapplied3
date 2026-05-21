<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_attendance_time()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        Carbon::setLocale('ja');
        $now = Carbon::create(2026, 5, 13, 12, 0, 0);
        Carbon::setTestNow($now);
        $expectedDate = $now->isoFormat('YYYY年M月D日(ddd)');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee($expectedDate);

        Carbon::setTestNow();
    }

    public function test_view_unAttendance()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $response->assertViewHas('status', 'not_started');

        $response->assertSee('勤務外');
    }

    public function test_view_attendance()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now(),
            'time_start' => now(),
            'time_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_view_break()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now(),
            'time_start' => now(),
            'time_end' => null,
        ]);

        $attendance->breaks()->create([
            'break_start' => now(),
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_view_leaving_work()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now(),
            'time_start' => now(),
            'time_end' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    public function test_view_attendance_situation()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $response = $this->post('/attendance/start');
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_view_leaving_work_situation()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now(),
            'time_start' => now(),
            'time_end' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    public function test_view_attendance_list_time_start()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $response = $this->post('/attendance/start');
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee(now()->format('H:i'));
    }

    public function test_view_attendance_for_break()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now(),
            'time_start' => now(),
            'time_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $response = $this->post('/break/start');
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_view_break_start()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now(),
            'time_start' => now(),
            'time_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩入');
    }

    public function test_view_break_situation()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now(),
            'time_start' => now(),
            'time_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $response = $this->post('/break/start');
        $response->assertRedirect('/attendance');

        $response = $this->patch('/break/end');
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_view_break_situation_second()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now(),
            'time_start' => now(),
            'time_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response = $this->post('/break/start');
        $response->assertRedirect('/attendance');

        $response = $this->patch('/break/end');
        $response->assertRedirect('/attendance');

        $response = $this->post('/break/start');
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩戻');
    }

    public function test_view_list_break_record()
    {
        $knownDate = Carbon::create(2026, 5, 14, 12, 0, 0);
        Carbon::setTestNow($knownDate);

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'time_start' => now()->toTimeString(),
            'time_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response = $this->post('/break/start');
        $response->assertRedirect('/attendance');

        Carbon::setTestNow($knownDate->copy()->addMinutes(30));
        $response = $this->patch('/break/end');
        $response->assertRedirect('/attendance');

        $expectedTotalTime = $attendance->break_total_minutes;

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($expectedTotalTime);
    }

    public function test_view_leave_work_situation2()
    {
        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'time_start' => now()->toTimeString(),
            'time_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $response = $this->patch('/attendance/end');
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    public function test_view_attendance_start_for_end()
    {
        $startTime = Carbon::create(2026, 5, 14, 9, 0, 0);
        Carbon::setTestNow($startTime);

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $response = $this->actingAs($user)->get('/attendance');

        $response = $this->post('/attendance/start');
        $response->assertRedirect('/attendance');

        Carbon::setTestNow($startTime->copy()->addMinutes(30));

        $response = $this->patch('/attendance/end');
        $response->assertRedirect('/attendance');

        $attendance = Attendance::where('user_id', $user->id)
                   -> where('date', $startTime->toDateString())
                   -> first();

        $attendanceEndTime = Carbon::parse($attendance->time_end)->format('H:i');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($attendanceEndTime);
    }

    public function test_view_list()
    {
        $startTime = Carbon::create(2026, 5, 14, 9, 0, 0);
        Carbon::setTestNow($startTime);

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-14',
            'time_start' => '2026-05-14 09:00:00',
            'time_end' => '2026-05-14 18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('05/14(木)');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
        $response->assertSee('08:00');
    }

    public function test_view_list_month()
    {
        $startTime = now();

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $response = $this->actingAs($user)->get('/attendance');

        $month = now()->format('Y-m');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($month);
    }

    public function test_view_list_prev_month()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendanceApril = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
        ]);

        $attendanceMay = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'time_start' => '2026-04-01 09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response = $this->get('/attendance/list');

        $response = $this->get('/attendance/list?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('2026-04');

        $response->assertSee('04/01');
    }

    public function test_view_list_next_month()
    {
        Carbon::setTestNow(Carbon::parse('2026-04-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendanceApril = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
        ]);

        $attendanceMay = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'time_start' => '2026-04-01 09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response = $this->get('/attendance/list');

        $response = $this->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('2026-05');

        $response->assertSee('05/01');
    }

    public function test_view_detail()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['attendanceId' => $attendance->id]));

        $response->assertStatus(200);
    }

    public function test_view_detail_name()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['attendanceId' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_view_detail_date()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['attendanceId' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($attendance->date->format('Y年'));
        $response->assertSee($attendance->date->format('m月d日'));
    }

    public function test_view_detail_timeStart_and_timeEnd()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['attendanceId' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($attendance->time_start->format('H:i'));
        $response->assertSee($attendance->time_end->format('H:i'));
    }

    public function test_view_detail_breakStart_and_breakEnd()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $break = $attendance->breaks()->create([
            'attendance' => $attendance->id,
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['attendanceId' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($break->break_start->format('H:i'));
        $response->assertSee($break->break_end->format('H:i'));
    }

    public function test_view_detail_attendance_correct()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $break = $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['attendanceId' => $attendance->id]));
        
        $response = $this->post(route('attendance.request', ['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '19:00',
            'request_time_end' => '18:00',
            'request_content' => '遅延のため'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'request_time_start' => '出勤時間が不適切な値です'
        ]);
    }

    public function test_view_detail_break_correct()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $break = $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['attendanceId' => $attendance->id]));
        
        $response = $this->post(route('attendance.request', ['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:00',
            'request_time_end' => '18:00',
            'request_content' => '会議延長のため',

            'breaks' => [
                [
                    'request_break_start' => '19:00',
                    'request_break_end' => '20:00',
                ]
            ]
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.request_break_start' => '休憩時間が不適切な値です'
        ]);
    }
    
    public function test_view_detail_break_correct2()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $break = $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['attendanceId' => $attendance->id]));
        
        $response = $this->post(route('attendance.request', ['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:00',
            'request_time_end' => '18:00',
            'request_content' => '会議延長のため',

            'breaks' => [
                [
                    'request_break_start' => '13:00',
                    'request_break_end' => '19:00',
                ]
            ]
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.request_break_end' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function test_view_detail_content_correct()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $break = $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['attendanceId' => $attendance->id]));
        
        $response = $this->post(route('attendance.request', ['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:00',
            'request_time_end' => '18:00',
            'request_content' => ''
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'request_content' => '備考を記入してください'
        ]);
    }

    public function test_view_request_situation()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $break = $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user);
        
        $response = $this->post(route('attendance.request', ['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '10:00',
            'request_time_end' => '19:00',
            'request_content' => '遅延のため'
        ]);

        $attendanceRequest = AttendanceRequest::latest()->firstOrFail();

        $admin = User::where('role', 1)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('request.list'));
        $response->assertStatus(200);

        $response = $this->get(route('request.approval', ['attendanceCorrectRequestId' => $attendanceRequest->id]));
        $response->assertStatus(200);
    }

    public function test_view_request_unApproval()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $break = $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user);
        
        $response = $this->post(route('attendance.request', ['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '10:00',
            'request_time_end' => '19:00',
            'request_content' => '遅延のため'
        ]);

        $attendanceRequest = AttendanceRequest::latest()->firstOrFail();

        $admin = User::where('role', 1)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('request.list'));
        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026/05/01');
        $response->assertSee('遅延のため');
    }

    public function test_view_request_approval()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $break = $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user);
        
        $response = $this->post(route('attendance.request', ['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '10:00',
            'request_time_end' => '19:00',
            'request_content' => '遅延のため'
        ]);

        $attendanceRequest = AttendanceRequest::latest()->firstOrFail();

        $admin = User::where('role', 1)->firstOrFail();

        $response = $this->actingAs($admin)->get(route('request.list'));
        $response = $this->get(route('admin.attendance.show', ['id' => $attendanceRequest->id]));

        $response = $this->get(route('request.approval', ['attendanceCorrectRequestId' => $attendanceRequest->id]));

        $response = $this->post(route('approval.store', ['attendanceCorrectRequestId' => $attendanceRequest->id]));

        $response = $this->actingAs($user)->get(route('request.list', ['status' => '2']));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026/05/01');
        $response->assertSee('遅延のため');
    }

    public function test_view_request_for_detail()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 10:00:00'));

        $user = User::first();

        if ($user) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        } else {
            $user = User::factory()->create(['email_verified_at' => now()]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-05-01',
            'time_start' => '2026-05-01 09:00:00',
            'time_end' => '2026-05-01 18:00:00',
        ]);

        $break = $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-05-01 13:00:00',
            'break_end' => '2026-05-01 14:00:00',
        ]);

        $response = $this->actingAs($user);
        
        $response = $this->post(route('attendance.request', ['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '10:00',
            'request_time_end' => '19:00',
            'request_content' => '遅延のため'
        ]);

        $attendanceRequest = AttendanceRequest::latest()->firstOrFail();

        $response = $this->get(route('request.list'));

        $response = $this->get(route('attendance.show', ['attendanceId' => $attendance->id]));

        $response->assertStatus(200);
    }
}

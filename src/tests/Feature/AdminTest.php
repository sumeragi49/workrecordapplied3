<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_admin_login_user()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => $today,
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('2026-05-14');

        Carbon::setTestNow();
    }

    public function test_admin_attendance_prev_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $prevDate = '2026-05-13';

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-13',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get(route('admin.attendance.index', ['date' => $prevDate]));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026-05-13');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }

    public function test_admin_attendance_next_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $nextDate = '2026-05-15';

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-15',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get(route('admin.attendance.index', ['date' => $nextDate]));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026-05-15');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }

    public function test_admin_staff_attendance_detail()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026年');
        $response->assertSee('05月14日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('13:00');
        $response->assertSee('14:00');

        Carbon::setTestNow();
    }

    public function test_admin_staff_attendance_correct_end_before_start()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response = $this->post(route('admin.attendance.request',['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:00',
            'request_time_end' => '08:00',
            'request_content' => '遅延のため'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'request_time_end' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function test_admin_staff_attendance_correct_break_start_after_end()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response = $this->post(route('admin.attendance.request',['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:00',
            'request_time_end' => '18:00',
            'breaks' => [
                [
                    'request_break_start' => '19:00',
                    'request_break_end' => '14:00',
                ]
            ],
            'request_content' => '遅延のため',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.request_break_start' => '休憩時間が不適切な値です'
        ]);
    }

    public function test_admin_staff_attendance_correct_break_end_after_end()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response = $this->post(route('admin.attendance.request',['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:00',
            'request_time_end' => '18:00',
            'breaks' => [
                [
                    'request_break_start' => '13:00',
                    'request_break_end' => '19:00',
                ]
            ],
            'request_content' => '遅延のため',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.request_break_end' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function test_admin_staff_attendance_correct_noComment()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response = $this->post(route('admin.attendance.request',['attendanceId' => $attendance->id]), [
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:00',
            'request_time_end' => '18:00',
            'breaks' => [
                [
                    'request_break_start' => '13:00',
                    'request_break_end' => '19:00',
                ]
            ],
            'request_content' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'request_content' => '備考を記入してください'
        ]);
    }

    public function test_admin_attendance_staff_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('test1@example.com');
        $response->assertSee('西伶奈');
        $response->assertSee('test2@example.com');
        $response->assertSee('増田一世');
        $response->assertSee('test3@example.com');
        $response->assertSee('山本敬吉');
        $response->assertSee('test4@example.com');
        $response->assertSee('秋田朋美');
        $response->assertSee('test5@example.com');
        $response->assertSee('中西敦夫');
        $response->assertSee('test6@example.com');
    }

    public function test_admin_attendance_staff_index()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get('/admin/staff/list');

        $response = $this->get(route('admin.staff.attendance', ['userId' => 1]));

        $response->assertStatus(200);
        $response->assertSee('05/14(木)');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
        $response->assertSee('08:00');

        Carbon::setTestNow();
    }

    public function test_admin_attendance_staff_index_prevMonth()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 14));
        $prevMonth = '2026-04';

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-04-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-04-14 13:00:00',
            'break_end' => '2026-04-14 14:00:00',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 5, 14));

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.attendance', ['userId' => '1', 'month' => $prevMonth]));

        $response->assertStatus(200);
        $response->assertSee('04/14(火)');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
        $response->assertSee('08:00');

        Carbon::setTestNow();
    }

    public function test_admin_attendance_staff_index_nextMonth()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $nextMonth = '2026-05';

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 4, 14));

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.attendance', ['userId' => '1', 'month' => $nextMonth]));

        $response->assertStatus(200);
        $response->assertSee('05/14(木)');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('01:00');
        $response->assertSee('08:00');

        Carbon::setTestNow();
    }

    public function test_admin_attendance_staff_index_for_detail()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.index'));

        $response = $this->get('/admin/staff/list');

        $response = $this->get(route('admin.staff.attendance', ['userId' => 1]));

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response->assertStatus(200);

        Carbon::setTestNow();
    }

    public function test_admin_attendance_request_list_unApproval()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
            'content' => '遅延のため',
            'status' => '1',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:10:00',
            'request_time_end' => '18:00:00',
            'request_content' => '遅延のため'
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin)->get(route('request.list'));

        $response->assertStatus(200);

        $response->assertSee('承認待ち');
        $response->assertSee('山田太郎');
        $response->assertSee('2026/05/14');
        $response->assertSee('遅延のため');
        $response->assertSee('2026/05/14');

        Carbon::setTestNow();
    }

    public function test_admin_attendance_request_list_approval()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
            'content' => '遅延のため',
            'status' => '2',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:10:00',
            'request_time_end' => '18:00:00',
            'request_content' => '遅延のため'
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin)->get(route('request.list', ['status' => '2']));

        $response->assertStatus(200);

        $response->assertSee('承認済み');
        $response->assertSee('山田太郎');
        $response->assertSee('2026/05/14');
        $response->assertSee('遅延のため');
        $response->assertSee('2026/05/14');

        Carbon::setTestNow();
    }

    public function test_admin_attendance_request_unApproval_detail()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
            'content' => '遅延のため',
            'status' => '1',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:10:00',
            'request_time_end' => '18:00:00',
            'request_content' => '遅延のため'
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin)->get(route('request.list'));

        $response = $this->get(route('request.approval', ['attendanceCorrectRequestId' => $attendanceRequest->id]));

        $response->assertStatus(200);

        $response->assertSee('2026年');
        $response->assertSee('05月14日');
        $response->assertSee('09:10');
        $response->assertSee('18:00');
        $response->assertSee('遅延のため');
        $response->assertSee('承認');

        Carbon::setTestNow();
    }

    public function test_admin_attendance_approval()
    {
        $this->withoutExceptionHandling();

        Carbon::setTestNow(Carbon::create(2026, 5, 14));
        $today = Carbon::today();

        $admin = User::find(7);

        $response = $this->post('/admin/login', [
            'email' => "test7@example.com",
            'password' => "coachtech1007",
        ]);

        $attendance = Attendance::create([
            'user_id' => '1',
            'date' => '2026-05-14',
            'time_start' => '09:00:00',
            'time_end' => '18:00:00',
            'content' => '遅延のため',
            'status' => '1',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-05-14 13:00:00',
            'break_end' => '2026-05-14 14:00:00',
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'request_time_start' => '09:10:00',
            'request_time_end' => '18:00:00',
            'request_content' => '遅延のため'
        ]);

        $attendanceRequest->breakRequests()->create([
            'attendance_correct_request_id' => $attendanceRequest->id,
            'break_id' => null,
            'request_break_start' => '2026-05-14 13:00:00',
            'request_break_end' => '2026-05-14 14:00:00',
        ]);

        auth()->shouldUse('admin');

        $response = $this->actingAs($admin)->get(route('request.list'));

        $response = $this->post(route('approval.store', ['attendanceCorrectRequestId' => $attendanceRequest->id]));

        $response = $this->get(route('request.list', ['status' => '2']));

        $response->assertStatus(200);

        $this->assertDatabaseHas('attendances', [
            'time_start' => "2026-05-14 09:10:00"
        ]);

        $response->assertSee('承認済み');
        $response->assertSee('山田太郎');
        $response->assertSee('2026/05/14');
        $response->assertSee('遅延のため');

        Carbon::setTestNow();
    }
}

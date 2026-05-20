<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class CreateNewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'time_start' => ['required', 'date_format:H:i', 'before:time_end'],
            'time_end' => ['required', 'date_format:H:i'],
            'content' => ['required', 'max:255'],
            'break_start' => ['date_format:H:i', 'after:time_start', 'before:time_end'],
            'break_end' => ['date_format:H:i', 'before:time_end'],
        ];
    }

    public function getFormattedDate(string $targetDate): array
    {
        $dateStr = Carbon::parse($targetDate)->format('Y-m-d');

        return [
            'time_start' => $dateStr . $this->input('time_start'),
            'time_end' => $dateStr . $this->input('time_end'),
            'break_start' => $this->filled('break_start') ? $dateStr . $this->input('break_start') : null,
            'break_end' => $this->filled('break_end') ? $dateStr . $this->input('break_end') : null,
            'content' => $this->input('content'),
        ];
    }

    public function messages()
    {
        return [
            'time_start.required' => '出勤時間を登録してください',
            'time_end.required' => '退勤時間を登録してください',
            'time_start.before' => '出勤時間が不適切な値です',
            'content.required' => '備考を記入してください',
            'break_start.after' => '休憩時間が不適切な値です',
            'break_start.before' => '休憩時間が不適切な値です',
            'break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }
}

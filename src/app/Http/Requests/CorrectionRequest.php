<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
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
            'request_time_start' => ['required', 'date_format:H:i', 'before:request_time_end'],
            'request_time_end' => ['required', 'date_format:H:i', 'after:request_time_start'],
            'request_content' => ['required', 'max:255'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.request_break_start' => ['date_format:H:i', 'after:request_time_start', 'before:request_time_end'],
            'breaks.*.request_break_end' => ['date_format:H:i', 'before:request_time_end'],
            'breaks.*.new_break_start' => ['nullable', 'date_format:H:i', 'after:request_time_start', 'before:request_time_end'],
            'breaks.*.new_break_end' => ['nullable', 'date_format:H:i', 'before:request_time_end'],
        ];
    }

    public function messages()
    {
        return [
            'request_time_start.before' => '出勤時間が不適切な値です',
            'request_time_end.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'request_content.required' => '備考を記入してください',
            'breaks.*.request_break_start.after' => '休憩時間が不適切な値です',
            'breaks.*.request_break_start.before' => '休憩時間が不適切な値です',
            'breaks.*.request_break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.0.new_break_start.after' => '休憩時間が不適切な値です',
            'breaks.0.new_break_start.before' => '休憩時間が不適切な値です',
            'breaks.0.new_break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }
}

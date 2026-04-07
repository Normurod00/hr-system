<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_ids' => ['required', 'array'],
            'application_ids.*' => ['integer', 'exists:applications,id'],
            'status' => ['required', 'string', Rule::in(array_column(ApplicationStatus::cases(), 'value'))],
        ];
    }

    public function messages(): array
    {
        return [
            'application_ids.required' => 'Выберите заявки.',
            'status.required' => 'Укажите статус.',
            'status.in' => 'Неверный статус.',
        ];
    }
}

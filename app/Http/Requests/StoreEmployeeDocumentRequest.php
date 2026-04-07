<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'document_type' => ['required', 'in:contract,diploma,certificate,id_document,medical,other'],
            'file' => ['required', 'file', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_profile_id.required' => 'Выберите сотрудника.',
            'document_type.required' => 'Укажите тип документа.',
            'file.required' => 'Прикрепите файл.',
            'file.max' => 'Размер файла не должен превышать 10 МБ.',
        ];
    }
}

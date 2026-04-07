<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resumeType = $this->input('resume_type', 'upload');

        if ($resumeType === 'upload') {
            return [
                'cover_letter' => ['nullable', 'string', 'max:5000'],
                'resume' => ['required', 'file', 'mimes:pdf,doc,docx,txt,rtf', 'max:10240'],
            ];
        }

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'citizenship' => ['nullable', 'string', 'max:255'],
            'desired_position' => ['nullable', 'string', 'max:255'],
            'desired_salary' => ['nullable', 'string', 'max:100'],
            'education' => ['nullable', 'array'],
            'education.*.level' => ['nullable', 'string'],
            'education.*.year' => ['nullable', 'integer', 'min:1970', 'max:2030'],
            'education.*.institution' => ['nullable', 'string', 'max:500'],
            'education.*.speciality' => ['nullable', 'string', 'max:500'],
            'experience' => ['nullable', 'array'],
            'experience.*.company' => ['nullable', 'string', 'max:255'],
            'experience.*.position' => ['nullable', 'string', 'max:255'],
            'experience.*.start_date' => ['nullable', 'string'],
            'experience.*.end_date' => ['nullable', 'string'],
            'experience.*.current' => ['nullable'],
            'experience.*.description' => ['nullable', 'string', 'max:2000'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'languages' => ['nullable', 'array'],
            'languages.*.name' => ['nullable', 'string', 'max:100'],
            'languages.*.level' => ['nullable', 'string', 'max:50'],
            'about' => ['nullable', 'string', 'max:3000'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'resume.required' => 'Прикрепите резюме',
            'resume.mimes' => 'Резюме должно быть в формате PDF, DOC, DOCX, TXT или RTF',
            'resume.max' => 'Размер файла не должен превышать 10 МБ',
            'cover_letter.max' => 'Сопроводительное письмо не должно превышать 5000 символов',
            'full_name.required' => 'Укажите ваше ФИО',
            'birth_date.required' => 'Укажите дату рождения',
            'phone.required' => 'Укажите номер телефона',
            'email.required' => 'Укажите email',
            'city.required' => 'Укажите город проживания',
        ];
    }
}

<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreApplicationRequest;
use PHPUnit\Framework\TestCase;

class StoreApplicationRequestTest extends TestCase
{
    private function makeRequest(array $data = []): StoreApplicationRequest
    {
        $request = new StoreApplicationRequest();
        $request->merge($data);
        return $request;
    }

    public function test_authorization_returns_true(): void
    {
        $request = $this->makeRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_upload_rules_require_resume(): void
    {
        $request = $this->makeRequest(['resume_type' => 'upload']);
        $rules = $request->rules();

        $this->assertArrayHasKey('resume', $rules);
        $this->assertContains('required', $rules['resume']);
        $this->assertContains('file', $rules['resume']);
    }

    public function test_upload_rules_include_cover_letter(): void
    {
        $request = $this->makeRequest(['resume_type' => 'upload']);
        $rules = $request->rules();

        $this->assertArrayHasKey('cover_letter', $rules);
        $this->assertContains('nullable', $rules['cover_letter']);
    }

    public function test_upload_rules_limit_file_types(): void
    {
        $request = $this->makeRequest(['resume_type' => 'upload']);
        $rules = $request->rules();

        $this->assertContains('mimes:pdf,doc,docx,txt,rtf', $rules['resume']);
    }

    public function test_upload_rules_limit_file_size(): void
    {
        $request = $this->makeRequest(['resume_type' => 'upload']);
        $rules = $request->rules();

        $this->assertContains('max:10240', $rules['resume']);
    }

    public function test_form_rules_require_personal_data(): void
    {
        $request = $this->makeRequest(['resume_type' => 'form']);
        $rules = $request->rules();

        $this->assertArrayHasKey('full_name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('phone', $rules);
        $this->assertArrayHasKey('birth_date', $rules);
        $this->assertArrayHasKey('city', $rules);

        $this->assertContains('required', $rules['full_name']);
        $this->assertContains('required', $rules['email']);
        $this->assertContains('required', $rules['phone']);
        $this->assertContains('required', $rules['birth_date']);
        $this->assertContains('required', $rules['city']);
    }

    public function test_form_rules_include_optional_fields(): void
    {
        $request = $this->makeRequest(['resume_type' => 'form']);
        $rules = $request->rules();

        $this->assertArrayHasKey('education', $rules);
        $this->assertArrayHasKey('experience', $rules);
        $this->assertArrayHasKey('skills', $rules);
        $this->assertArrayHasKey('languages', $rules);
        $this->assertArrayHasKey('about', $rules);

        $this->assertContains('nullable', $rules['education']);
        $this->assertContains('nullable', $rules['experience']);
    }

    public function test_default_resume_type_is_upload(): void
    {
        // No resume_type provided, defaults to 'upload'
        $request = $this->makeRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('resume', $rules);
        $this->assertContains('required', $rules['resume']);
    }

    public function test_has_russian_error_messages(): void
    {
        $request = $this->makeRequest();
        $messages = $request->messages();

        $this->assertNotEmpty($messages);
        $this->assertArrayHasKey('resume.required', $messages);
        $this->assertArrayHasKey('full_name.required', $messages);
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('phone.required', $messages);
        $this->assertArrayHasKey('birth_date.required', $messages);
        $this->assertArrayHasKey('city.required', $messages);
    }

    public function test_error_messages_are_in_russian(): void
    {
        $request = $this->makeRequest();
        $messages = $request->messages();

        // Check messages contain Cyrillic characters
        foreach ($messages as $key => $message) {
            $this->assertMatchesRegularExpression('/[\p{Cyrillic}]/u', $message, "Message for {$key} should be in Russian");
        }
    }

    public function test_form_rules_validate_email_format(): void
    {
        $request = $this->makeRequest(['resume_type' => 'form']);
        $rules = $request->rules();

        $this->assertContains('email', $rules['email']);
    }
}

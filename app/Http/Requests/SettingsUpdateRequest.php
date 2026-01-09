<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'theme_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'application_logo' => ['nullable', 'image', 'max:5120'], // 5MB
            'favicon' => ['nullable', 'file', 'mimes:ico,png,svg,jpg,jpeg', 'max:1024'], // 1MB, favicon formats
            'logo_alignment' => ['nullable', 'string', 'in:left,center,right'],
            'button_primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_success_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_danger_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_warning_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_info_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}

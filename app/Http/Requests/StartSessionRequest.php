<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return ['latitude' => 'required|numeric', 'longitude' => 'required|numeric'];
    }
}

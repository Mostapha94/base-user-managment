<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use App\Rules\PhoneNumberCheck;

class UpdateUserRequest extends FormRequest
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
     * Prepare the data for validation.
     */
    public function all($keys = null): array
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('model');
        $data['phone_number'] = isset($data['phone_number']) ? str_replace(' ', '', $data['phone_number']) : null;
        $data['type'] = isset($data['type']) ? strtoupper($data['type']) : null;

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email:filter|max:191|unique:users,email,'.$this->id,
            'name' => 'required|string|max:191',
            'image' => 'nullable|image|max:512',
            'phone_number' => ['nullable', 'max:20', new PhoneNumberCheck],
            'city_id' => 'nullable|exists:cities,id',
            'password' => 'required|string|min:8|max:100',
        ];
    }
}

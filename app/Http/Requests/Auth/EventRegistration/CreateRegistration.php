<?php

namespace App\Http\Requests\Auth\EventRegistration;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateRegistration extends FormRequest
{

    public function authorize()
    {
        if (Auth::check()) {
            return true;
        }
        return false;
    }


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        $dobstate = 'nullable|date';
        if ($event->dateofbirth ?? false) {
            $dobstate = 'required|date';
        }
        return [
            'userid'         => 'nullable',
            'eventid'        => 'required',
            'firstname'      => 'required',
            'lastname'       => 'required',
            'email'          => 'required',
            'membership'     => 'nullable',
            'phone'          => 'nullable',
            'address'        => 'nullable',
            'notes'          => 'nullable',
            'clubid'         => 'nullable',
            'gender'         => 'nullable',
            'roundids'       => 'required',
            'divisionid'     => 'required',
            'dateofbirth'    => $dobstate,
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'firstname.required'   => 'Firstname is required',
            'lastname.required'    => 'Lastname is required',
            'email.required'       => 'Email address is required',
            'dateofbirth.required' => 'Date of Birth is required',
            'divisionid.required'  => 'Division is required'
        ];
    }
}

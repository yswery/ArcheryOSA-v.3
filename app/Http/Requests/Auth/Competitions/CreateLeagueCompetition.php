<?php

namespace App\Http\Requests\Auth\Competitions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateLeagueCompetition extends FormRequest
{

    public function authorize()
    {
        if (Auth::check() && Auth::user()->roleid <= 3) {
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
        return [
            'label'          => 'required|max:155',
            'eventid'        => 'required',
            'schedule'       => 'nullable',
            'location'       => 'nullable',
            'scoringlevel'   => 'nullable',
            'visible'        => 'nullable',
            'scoringenabled' => 'nullable',
            'roundids'       => 'required',
            'divisionids'    => 'required',
            'ignoregenders'  => 'nullable',
            'multipledivisions' => 'nullable'

        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'label.required'       => 'Event name is required',
            'roundids.required'    => 'Please select the rounds for this event',
            'divisionids.required' => 'Please select the divisions for this event',
            'eventid.required'     => 'EventID is required'
        ];
    }
}

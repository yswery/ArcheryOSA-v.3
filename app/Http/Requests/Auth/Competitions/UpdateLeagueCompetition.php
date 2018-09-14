<?php

namespace App\Http\Requests\Auth\Competitions;

use App\Models\EventAdmin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateLeagueCompetition extends FormRequest
{

    public function authorize()
    {
        if (Auth::check()) {
            $eventadmin = EventAdmin::where('userid', Auth::id())
                ->where('eventid', $this->eventid)
                ->where('canedit', 1)
                ->get()->first();

            if (!empty($eventadmin)) {
                return true;
            }

        }
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
            'ignoregenders'  => 'nullable'
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

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\User\UserUpdateProfile;
use App\Http\Controllers\Controller;
use App\Jobs\SendArcherRelationRequest;
use App\Models\UserRelation;
use App\User;

use Illuminate\Support\Facades\Auth;
Use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{







    /******************************************************************************
     * GET Requests
     ******************************************************************************/

    /**
     * Gets the users dashboard
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getDashboard()
    {
        return view('profile.auth.profile');
    }



    /**
     * Gets the users details form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getMyDetails()
    {
        return view('profile.auth.mydetails');
    }

    public function getMyEvents()
    {
        $myevents = DB::select("
            SELECT e.label, e.start, e.eventurl, es.label as status, evs.label as eventstatus
            FROM `events` e
            JOIN `evententrys` ee USING (`eventid`)
            JOIN `eventstatus` evs USING (`eventstatusid`)
            JOIN `entrystatus` es ON (ee.entrystatusid = es.entrystatusid)
            WHERE `ee`.`userid` = '".Auth::id()."'
            ORDER BY `e`.`start` DESC
        ");


        return view('profile.auth.events.myevents', compact('myevents'));
    }

    public function getMyResults()
    {
        return view('profile.auth.results.all');
    }



    /******************************************************************************
     * RELATIONSHIPS
     ******************************************************************************/
    public function getRelationshipsList()
    {
        $relations = UserRelation::where('userid', Auth::id())->pluck('relationid')->toarray();

        if (!empty($relations)) {
            $relations = User::wherein('userid', $relations)->get();
            foreach ($relations as $relation) {
                $relation->status = UserRelation::where('userid', Auth::id())
                                                ->where('relationid', $relation->userid)
                                                ->pluck('authorised')->first();
            }
        }


        return view('profile.auth.relationships-list', compact('relations'));
    }

    public function getRelationshipsRequest()
    {
        return view('profile.auth.relation', compact('relation'));
    }

    public function requestRelationship(Request $request)
    {
        $user = User::where('email', $request->input('email', null) ?? '')->get()->first();

        if (empty($user)) {
            return back()->with('failure', 'Cannot find a user with that email address');
        }

        // check to see if a relationship already exists
        $userrelation = UserRelation::where('userid', Auth::id())->where('relationid', $user->userid)->get()->first();

        if (!empty($userrelation)) {
            return back()->with('failure', 'Relationship already exists');
        }

        $authfullname = ucwords(Auth::user()->firstname ?? '') . ' ' . ucwords(Auth::user()->lastname ?? '') . '(' . Auth::user()->email . ')';

        $userrelation = new UserRelation();
        $userrelation->userid = Auth::id();
        $userrelation->relationid = $user->userid;
        $userrelation->hash = $this->createHash();
        $userrelation->save();

        SendArcherRelationRequest::dispatch($user->email,
                                            $user->firstname,
                                            $authfullname,
                                            $userrelation->hash,
                                            getenv('APP_URL') . '/profile/relationship/authorise'
        );

        return back()->with('success', 'Request sent!');
    }
    public function authoriseRelation(Request $request)
    {
        $userrelation = UserRelation::where('hash', $request->hash ?? -1)->where('authorised', 0)->get()->first();

        $status = false;

        if (empty($userrelation)) {
            $message = 'Authorisation link invalid (may have already been approved)';
        }
        else {
            $userrelation->authorised = 1;
            $userrelation->save();

            $message = 'Authorisation complate!';
            $status = true;
        }

        return view('profile.public.relationconfirm', compact('message', 'status'));

    }
    public function removeRelationship(Request $request)
    {
        $userid = $request->userid;
        if (empty($userid)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Request, please try again later'
            ]);
        }

        $userrelation = UserRelation::where('userid', Auth::id())->where('relationid', $userid)->get()->first();
        if (empty($userrelation)) {
            // could the be relation trying to remove their relation with another user, try that
            $userrelation = UserRelation::where('userid', $userid)->where('relationid', Auth::id())->get()->first();
        }

        if (empty($userrelation)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Request, please try again later'
            ]);
        }

        // Delete the user relationship
        //$userrelation->delete();

        // return response
        return response()->json([
            'success' => true,
            'message' => 'Relationship Removed'
        ]);

    }



    /******************************************************************************
     * POST Requests
     ******************************************************************************/

    /**
     * Updates a Users Profile
     * @param UserUpdateProfile $request
     * @return redirect
     */
    public function updateProfile(UserUpdateProfile $request)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $user->firstname   = $validated['firstname'];
        $user->lastname    = $validated['lastname'];
        $user->phone       = $validated['phone'];
        $user->address     = $validated['address'];
        $user->city        = $validated['city'];
        $user->postcode    = $validated['postcode'];
        $user->postcode    = $validated['postcode'];
        $user->dateofbirth = $validated['dateofbirth'];

        $user->save();

        return redirect()->back()->with('success', 'Profile Updated');
    }



}

<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Business;
use App\Models\Notification;
use App\Models\UserBusiness;
use Illuminate\Http\Request;
use App\Models\Invitation;
use App\Models\User;
use BenSampo\Enum\Rules\EnumValue;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Image;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // not sure if you need this line here, I kept it for now but I think we don't need it
        $myBus = UserBusiness::where('user_id', Auth::user()->id)->get();

        // now to get businesses you can use this calling instead of the loop below, after you check the code delete the comments plz :)
        $businesses =  Auth::user()->businesses;
        // $businesses = collect();
        // if($myBus != null){
        //     foreach($myBus as $b){
        //         //$businesses = Business::all();//where('userId' , Auth::user()->id);
        //         $businesses->push(Business::findOrFail($b->business_id));
        //     }
        // }

        if (request()->is('api/*')) {
            //an api call
            return response()->json(['businesses' => $businesses, 'myBus' => $myBus]);
        } else {
            //a web call
            return view('app.businesses.list-businesses', compact('businesses', 'myBus'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $business = new Business();
        $business->name = $request->name;
        $business->is_active = 1;
        if (isset($request->logo)) {
            $image = $request->file('logo');
            $business->logo = $this->addBizImages($image);
        } elseif ($request->logo == '' || $request->logo == null) {
            $business->logo =  'img/bizLogo.png';
        } else {
            $business->logo = 'img/bizLogo.png';
        }
        $business->save();

        // both options work the same choose whatever you are comfortable with
        // option1
        Auth::user()->businesses()->attach($business->id, ['role' => UserRole::MANAGER,]);

        // option2
        // $relation = new UserBusiness();
        // $relation->role	= UserRole::MANAGER;
        // $relation->user_id =Auth::user()->id;
        // $relation->business_id = $business->id;
        // $relation->save();

        if (request()->is('api/*')) {
            //an api call
            return response()->json(['succeed' => true, 'business' => $business]);
        } else {
            //a web call
            return redirect('businesses')->with('messageSuc', 'Business profile created successfully');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function show(Business $business)
    {
        // also here no need to get business id
        // $business = Business::findOrFail($id);

        //you can check if relation exists using this method

        // you code:
        // $auth = UserBusiness::where('business_id' ,$id)->where('user_id', Auth::user()->id)->get();
        // if(count($auth) > 0 ){
        //     return view('app.businessDetails' , compact('business'));
        // }else{
        //     return redirect('/')->with( 'messageDgr' , 'Access Denied.');
        // }


        // alternate way:
        $exists = $business->users->contains(Auth::user());
        if ($exists) {
            $current_user_business_details = UserBusiness::where('business_id', $business->id)->where('user_id', Auth::user()->id)->first();
            return view('app.businesses.show-business', compact('business', 'current_user_business_details'));
        } else {
            return redirect('/')->with('messageDgr', 'Access Denied.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function edit(Business $business)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Business $business)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function destroy(Business $business)
    {
        //
    }
    public function showBusiness($id)
    {
    }

    public function addBizImages($image)
    {
        $destinationPath = 'uploads/biz'; //public_path('uploads/biz');

        $img = Image::make($image->getRealPath());
        $img->resize(700, 1000, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . time() . $image->getClientOriginalName());
        $path = $destinationPath . '/' . time() . $image->getClientOriginalName();

        /*$imageName =pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME). time() . '.' . $image->getClientOriginalExtension();
        $destinationPath = 'uploads/biz/'; //public_path('uploads/biz');
        $image->move($destinationPath, $imageName);
        $path = $destinationPath . $imageName;*/
        return $path;
    }

    public function showMembers(Business $business)
    {
        $members = $business->users;
        $invitations = Invitation::where('business_id', $business->id)->where('status', 'PENDING')->with('user')->get();
        $current_user_business_details = UserBusiness::where('business_id', $business->id)->where('user_id', Auth::user()->id)->first();
        return view('app.businesses.members.list-members', compact('business', 'invitations', 'current_user_business_details'));
    }

    public function addNewEmployee(Request $request, Business $business)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', new EnumValue(UserRole::class)],
        ]);

        if ($validator->fails()) {
            //TODO: add error message for api
            Log::error($validator->errors());
            return redirect('/businesses/' . $business->id . '/members')
                ->withErrors($validator)
                ->withInput();
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        $user->businesses()->attach([$business->id => ['role' => $request['role']]]);

        if (request()->is('api/*')) {
            return response()->json(['succeed' => true, 'business' => $business, 'user' => $user]);
        } else {
            //a web call
            return redirect('/businesses/' . $business->id . '/employees');
            return view('app.businesses.employees.list-employees', compact('business'));
        }
    }

    public function leave(Business $business)
    {
        $user = Auth::user();
        $user->businesses()->detach($business->id);
        $user->save();
        return redirect('/businesses')->with('messageSuc', 'You have left the business');
    }
}

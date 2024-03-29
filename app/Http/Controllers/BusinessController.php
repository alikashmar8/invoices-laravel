<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Business;
use App\Models\Contact;
use App\Models\Notification;
use App\Models\UserBusiness;
use Illuminate\Http\Request;
use App\Models\Invitation;
use App\Models\Invoice;
use App\Models\InvoiceAttachment;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\BillAccess;
use Carbon\Carbon;
use BenSampo\Enum\Rules\EnumValue;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Plan;
use Illuminate\Support\Facades\Validator;
use Image;


use Illuminate\Support\Facades\Mail;
use App\Mail\InviteNewMember;

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
        $myBusManagerCount = count(UserBusiness::where('user_id', Auth::user()->id)->where('role', 'MANAGER')->get());

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
            return response()->json(['businesses' => $businesses, 'myBusManagerCount' => $myBusManagerCount]);
        } else {
            //a web call
            return view('app.businesses.list-businesses', compact('businesses', 'myBusManagerCount'));
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
        $businesses = count(UserBusiness::where('user_id', Auth::user()->id)->where('role', 'MANAGER')->get());
        // TODO: we will do it this way after we agree on structure
        // if ($businesses >= Auth::user()->plan->max_businesses_number) {
        //     return response()->json(['error' => 'You have reached the maximum number of businesses for your plan.']);
        // }
        if(Auth::user()->plan_id < 3 && $businesses > 0) return redirect('/plan-3');
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

        Auth::user()->businesses()->attach($business->id, ['role' => UserRole::MANAGER,]);

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
        $exists = $business->users->contains(Auth::user());
        $user_has_suitable_plan = 1 ;
        if ($exists) {
            //get user details 
            $current_user_business_details = UserBusiness::where('business_id', $business->id)->where('user_id', Auth::user()->id)->first();

            //check if user plan is suitable for manager:
            if($current_user_business_details->role == 'MANAGER'){
                //how many businesses deos this manager have?
                $user_manage_to = UserBusiness::where('user_id', Auth::user()->id)->where('role' , 'MANAGER')->get();
                //in case this manager has more than one business, he can't access other than the first business:
                if(Auth::user()->plan_id < 3 && count($user_manage_to) > 1 && $user_manage_to[0]->business_id != $business->id ) {
                    $user_has_suitable_plan = 0 ; 
                }
            }
            //check if user has suitable plan as TEAM_MEMBER
            else{
                //how is the manager?
                $manager = User::where( 'id' , UserBusiness::where('business_id', $business->id)->where('role' , 'MANAGER')->first()->user_id)->first() ;
                //how many business deos the manager has?
                $manager_manage_to = UserBusiness::where('user_id', $manager->id)->where('role' , 'MANAGER')->get();
                //on basic plan, the team member cant access
                if($manager->plan_id ==1 ) $user_has_suitable_plan = 0 ;
                //on gold plan:
                elseif($manager->plan_id ==2 ) {
                    //if manager has multiple businesses, team member can't access
                    if( count($manager_manage_to) > 1  ) $user_has_suitable_plan = 0 ; 
                    // if manager has one business and more than 10 team members, tem member can't access 
                    else{
                        if( count(UserBusiness::where('business_id', $business->id)->get()) > 10 ) $user_has_suitable_plan = 0 ;
                    }
                } 

            }
            
            
            //redirect to business page 
            if($current_user_business_details->role == 'TEAM_MEMBER') $invoices =  $business->invoices->where('created_by' , Auth::user()->id)->sortByDesc('created_at');
            else $invoices =  $business->invoices->sortByDesc('created_at');
            // TODO: query Still under testing (better than loops)
            $totalPaid = $invoices->where('isPaid', true)->sum('total');
            $totalPending = $invoices->where('isPaid', false)->sum('total');
            Log::info('totalPaid using query: ' . $totalPaid);
            Log::info('totalPending using query: ' . $totalPending);
            $totalPaid = $totalPending = 0 ;
            foreach($invoices as $inv){
                $inv->items = InvoiceItem::where('invoice_id' , $inv->id)->get();
                if($inv->is_paid) $totalPaid += $inv->total;
                else $totalPending += $inv->total;
            }
            Log::info('totalPaid using foreach: ' . $totalPaid);
            

            
            $totalPaidGST = $invoices->where('isPaid', true)->sum('gst');
            $totalPendingGST = $invoices->where('isPaid', false)->sum('gst');
            $totalPaidGST = $totalPendingGST = 0 ;
            foreach($invoices as $inv){
                if($inv->is_paid) $totalPaidGST += $inv->gst;
                else $totalPendingGST += $inv->gst;
            }

            if($current_user_business_details->role == 'TEAM_MEMBER')  $bills =  $business->bills->where('created_by' , Auth::user()->id)->sortByDesc('created_at');
            else $bills =  $business->bills->sortByDesc('created_at');
            $totalEarning = $bills->where('isPaid', true)->sum('total');
            $totalPendingEarn = $bills->where('isPaid', false)->sum('total');
            Log::info('totalEarning using query: ' . $totalEarning);
            $totalEarning = $totalPendingEarn = 0 ;
            foreach($bills as $bill){
                if($bill->is_paid) $totalEarning += $bill->total;
                else $totalPendingEarn += $bill->total;
                $bill->contact = Contact::findOrFail($bill->contact_id);
                $bill->bill_accesses = BillAccess::where('bill_id' ,$bill->id )->get();
            }
            Log::info('totalEarning using foreach: ' . $totalEarning);

            $totalEarningGST = $bills->where('isPaid', true)->sum('gst');
            $totalPendingEarnGST = $bills->where('isPaid', false)->sum('gst'); 
            $totalEarningGST = $totalPendingEarnGST = 0 ;
            foreach($bills as $bill){
                if($bill->is_paid) $totalEarningGST += $bill->gst;
                else $totalPendingEarnGST += $bill->gst; 
            }

            $monthlyInvoices =0;
            /*$monthlyInvoices = $invoices
            ->reduce(function ($item) {
                return $item->total;
            })
            ->groupBy(function($item) { 
                return Carbon::parse($item->created_at)->format('m');
            }); 
            foreach($monthlyInvoices as $inv){ 
                $inv->monthlyPaid = 0;
                foreach($inv as $i){ 
                    $inv->monthlyPaid += $i->total; 
                } 
            }
            $monthlyBills = $bills->groupBy(function($item) {
                return Carbon::parse($item->created_at)->format('m');
            });*/
            
            return view('app.businesses.show-business', compact('business', 'current_user_business_details', 'invoices' ,'bills', 'totalPaid' , 'totalPending','totalEarning','totalPendingEarn','monthlyInvoices','totalPaidGST' , 'totalPendingGST','totalEarningGST' , 'totalPendingEarnGST', 'user_has_suitable_plan' ));
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
        $business->name = $request->name;
        $business->abn = $request->abn;
        $business->address = $request->address;
        $business->payment_method = $request->payment_method;

        if (isset($request->logo)) {
            $image = $request->file('logo');
            $business->logo = $this->addBizImages($image);
        }
        $business->save();

        if (request()->is('api/*')) {
            return response()->json(['succeed' => true, 'business' => $business]);
        } else {
            return back()->with('messageSuc', 'Business profile updated successfully');
        }
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
        /*$img->orientate()->resize(1000, 1000, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . time() . $image->getClientOriginalName());*/
        $img->orientate()->resize(1000, 1000)->save($destinationPath . '/' . Carbon::now()->format('Y-m-d-H-i').  $image->getClientOriginalName());
        $path = $destinationPath . '/' . Carbon::now()->format('Y-m-d-H-i') .  $image->getClientOriginalName();

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
        if(Auth::user()->plan_id == 3) $teamMembers = false;
        else{
            $allowedTeamMembers = Plan::findOrFail(Auth::user()->plan_id)->team_members;
            $businesses = Auth::user()->businesses()->allRelatedIds();
            $teamMembers = 1;
            foreach ($businesses as $bus) {
                $teamMembers += count(UserBusiness::where('business_id' , $bus)->get()) - 1;
                $teamMembers += count(Invitation::where('business_id' , $bus)
                                ->where('status', 'PENDING')->get()) ;
            } if($teamMembers >= $allowedTeamMembers) $teamMembers = true; else $teamMembers = false;
        }

        return view('app.businesses.members.list-members', compact('business', 'invitations', 'current_user_business_details','teamMembers'));
    }

    public function inviteNewTeamMember(Request $request, Business $business)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            
            'role' => ['required', new EnumValue(UserRole::class)],
        ]);

        if ($validator->fails()) {
            //TODO: add error message for api
            Log::error($validator->errors());
            return redirect('/businesses/' . $business->id . '/members')
                ->withErrors($validator)
                ->withInput();
        }
 
        $data = array(
            'name' => $request->name,
            'email' => $request->email,
            'business' => $business->name,
            'sender' => Auth::user()->name, 
            'date' => Carbon::now(), 
            'role' =>  $request['role']
        );
        Mail::to($request->email)->send(new InviteNewMember($data));
  

        if (request()->is('api/*')) {
            return response()->json(['succeed' => true, 'business' => $business, 'user' => $user]);
        } else {
            //a web call
            return redirect('/businesses/' . $business->id . '/members');
        }
    }

    public function leave(Business $business)
    {
        $user = Auth::user();
        $user->businesses()->detach($business->id);
        $user->save();
        return redirect('/businesses')->with('messageSuc', 'You have left the business');
    }

    public function removeTeamMember(Request $request, Business $business, User $user)
    {
        $user->businesses()->detach($business->id);
        $user->save();
        if ($request->is('api/*')) {
            return response()->json(['success' => true]);
        } else {
            return redirect('/businesses/' . $business->id . '/members')->with('messageSuc', 'Team member removed successfully');
        }
    }

    public function updateRole(Request $request, Business $business, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => ['required', new EnumValue(UserRole::class)],
        ]);

        if ($validator->fails()) {
            //TODO: add error message for api
            Log::error($validator->errors());
            return redirect('/businesses/' . $business->id . '/members')
                ->withErrors($validator)
                ->withInput();
        }

        $user->businesses()->updateExistingPivot($business->id, ['role' => $request['role']]);

        $messageSuc = 'Role updated successfully';

        if ($request->is('api/*')) {
            return response()->json(['success' => true]);
        } else {
            return redirect('/businesses/' . $business->id . '/members')->with('messageSuc', $messageSuc);
        }
    }

    public function makeFavorite(Request $request, Business $business)
    {
        $user = Auth::user();
        $businesses_ids = $user->businesses()->allRelatedIds();
        foreach ($businesses_ids as $id) {
            $user->businesses()->updateExistingPivot($id, ['is_favorite' => false]);
        }
        $user->businesses()->updateExistingPivot($business->id, ['is_favorite' => true]);
        $user->save();
        if ($request->is('api/*')) {
            return response()->json(['success' => true]);
        } else {
            return redirect('/businesses/')->with('messageSuc', 'Business is favorite');
        }
    }

    public function getContacts(Business $business)
    {
        $contacts = $business->contacts;
        return response()->json($contacts);
    }
}

@extends('layouts.app')

@section('title', 'Team members')


@section('content')

<div class="container mt-5">
    <div class="p-2"> 
        <a class="btn btn-link" href="/businesses/{{$business->id}}"><i class="fas fa-arrow-left"></i> Back </a>
    </div>
    <div class="row d-flex justify-content-center">
        <div class="col-md-12">
            <div class="card-prof p-3 py-4" style='border: 1px solid #ff556e30;'>

                <form method='post' action="/edit-business-form/{{$business->id}}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div style='margin:5px; padding :7px; display:inline-block;position: relative;'>
                                <label for='imgFile'>
                                    <div class='avatar avatar-xl position-relative'>
                                        <img src="{{asset($business->logo)}}" id='imgSrc' alt="profile_image"   class="w-100 border-radius-lg shadow-sm" style='max-width:75px;max-height:75px;'>
                                    </div>
                                </label>
                                
                                <i class="fa fa-info-circle text-primary" aria-hidden="true" onmouseover="document.getElementById('hint').style.display = 'inline-block'" onmouseleave="document.getElementById('hint').style.display = 'none'"></i> <small id='hint' class="hide text-primary" style="display: none">Click on the image to change it. Make sure to choose 1x1 image</small>
                                <input type='file'  name='logo' style='display:none' accept="image/*" id='imgFile'  onchange='readImg(this.files[0])'>

                            </div>
                            <!--div class="text-center">
                                <img src="{{-- asset($business->logo) --}}" width="100" class="rounded-circle">
                            </div-->
                        </div>
                        <div class="col-md-5">
                            <div class="text-primary">
                                <input type="text" name='name' class="form-control border-0 mt-2 mb-0" id='editBusinessName' value='{{ $business->name }}' placeholder="Business Name" required onkeypress="nameChanged()"> 
                                <input type="text" name='abn' class="form-control border-0 mt-2 mb-0" id='editBusinessABN' value='{{ $business->abn }}' placeholder="ABN" onkeypress="nameChanged()"> 
                                <input type="text" name='address' class="form-control border-0 mt-2 mb-0" id='editBusinessAddress' value='{{ $business->address }}' placeholder="Address" onkeypress="nameChanged()"> 
                                <textarea  name='payment_method' rows='3' class="form-control border-0 mt-2 mb-0" id='editPaymentMethod' onkeypress="nameChanged()" placeholder="Payment Method" >@if($business->payment_method) {{$business->payment_method}} @else Account Name:
 BSB: 
 Account Number: @endif</textarea>
                                
                            </div>
                        </div>
                        <div class="col-md-4">
                            <ul class="social-list-prof  ">
                                <input type="submit" class='btn btn-success' style=' display:none' id='editFormSubmitBtn' value='Save Changes' >
                                <input type="button" class='btn btn-secondary' style=' display:none' id='editFormCancelBtn' value='Cancel Changes' onclick='cancelChanges()'>
                            </ul>
                        </div>
                    </form>

                    <script>
                        var imgId="imgSrc";
                        var fileId = "imgFile";
                        function readImg(image){
                            document.getElementById(imgId).src = window.URL.createObjectURL(image);
                            document.getElementById('editFormSubmitBtn').style.display = 'inline';
                            document.getElementById('editFormCancelBtn').style.display = 'inline';
                        }
                        function removeImg(){
                            document.getElementById(imgId).src ="{{asset($business->logo)}}";
                            document.getElementById(fileId).value =null;
                        }
                        function cancelChanges(){
                            document.getElementById('editFormSubmitBtn').style.display = 'none';
                            document.getElementById('editFormCancelBtn').style.display = 'none';
                            document.getElementById(imgId).src ="{{asset($business->logo)}}";
                            document.getElementById('editBusinessName').value = '{{ $business->name }}';
                            document.getElementById('editBusinessABN').value = '{{ $business->abn }}';
                            document.getElementById('editBusinessAddress').value = '{{ $business->address }}';
                            document.getElementById('editPaymentMethod').value = ' Account Name: \n BSB: \n Account Number:';
                            document.getElementById(fileId).value =null;
                        }
                        function nameChanged(){
                            document.getElementById('editFormSubmitBtn').style.display = 'inline';
                            document.getElementById('editFormCancelBtn').style.display = 'inline';
                        }
                    </script>

                </div>
            </div>
        </div>
    </div>
</div>


<div class="container mt-5">
    <div class="row d-flex justify-content-center">
        <div class="col-md-12">
            
            
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-9">
                            <h2> Team Memebers </h2>
                        </div>
                        <div class="col-md-3 w-100">
                            @if($current_user_business_details->role == 'MANAGER' || $current_user_business_details->role == 'CO_MANAGER')
                                 
                                <a class="btn btn-primary btn-sm btn-block float-right text-white @if(Auth::user()->plan_id < 3 && $teamMembers) goGem  " @else " data-toggle="modal" data-target="#addMemberModal" type="button" @endif >Add a new member</a>
                                 
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <table class='table table-striped table-hover table-responsive-sm'>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($business->users as $member)
                                <tr>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->pivot->role }}</td>
                                    <td>
                                        @if ($member->pivot->is_active)
                                        Active
                                        @else
                                        Inactive
                                        @endif
                                    </td>
                                    <td>
                                        <div class="row">
                                            @if (($current_user_business_details->role == 'MANAGER' || $current_user_business_details->role == 'CO_MANAGER') && $member->id != Auth::user()->id )
                                            <button type="button" class="btn col-md-2" data-target="#updateModal-{{ $member->id }}" data-toggle="modal">
                                                <i class="fa fa-edit text-primary"></i>
                                            </button>
                                            @endif
                                            @if (($current_user_business_details->role == 'MANAGER' || $current_user_business_details->role == 'CO_MANAGER') && $member->id != Auth::user()->id )
                                            <button type="button" class="btn col-md-2" data-target="#deleteModal-{{ $member->id }}" data-toggle="modal"><i class='fa fa-trash text-primary'></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <!-- delete modal -->
                                <div class="modal fade" id="deleteModal-{{ $member->id }}" tabindex="1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class=" modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Delete confirmation</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body" id="output_content">
                                                Are you sure you want to remove {{$member->name}} from your business?
                                            </div>
                                            <div class="modal-footer">

                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <form method="POST" action="/businesses/{{$business->id}}/members/{{$member->id}}/remove" enctype="multipart/form-data">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">Remove</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- update modal -->
                                <div class="modal fade" id="updateModal-{{ $member->id }}" tabindex="1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class=" modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Update {{$member->name}} role</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body" id="output_content">
                                                <form method="POST" action="/businesses/{{$business->id}}/members/{{$member->id}}/update-role" enctype="multipart/form-data">
                                                    @csrf
                                                    <label for="role" class="col-form-label text-md-end">
                                                        Choose new role:
                                                    </label>
                                                    <select name="role" id="update-role" class="form-control">
                                                        @if($member->pivot->role == 'TEAM_MEMBER') <option value="CO_MANAGER">Co Manager</option>
                                                        @else <option value="TEAM_MEMBER" >Team Member</option>
                                                        @endif
                                                    </select>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @if (count($invitations) > 0)
                                @foreach ($invitations as $invitation)
                                <tr>
                                    <td>{{ $invitation->user->name }}</td>
                                    <td>{{ $invitation->role }}</td>
                                    <td>Pending</td>
                                    <td>
                                        @if (($current_user_business_details->role == 'MANAGER' || $current_user_business_details->role == 'CO_MANAGER')   )
                                            <button type="button" class="btn col-md-2" data-target="#deletePendingModal-{{ $invitation->user_id }}" data-toggle="modal">
                                                <i class='fa fa-trash text-primary'></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                <!-- delete modal -->
                                <div class="modal fade" id="deletePendingModal-{{ $invitation->user_id }}" tabindex="1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class=" modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Delete confirmation</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body" id="output_content">
                                                Are you sure you want to remove {{$invitation->user->name}} from your business?
                                            </div>
                                            <div class="modal-footer">

                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <form method="POST" action="/invitations/{{$invitation->id}}/destroy" enctype="multipart/form-data">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">Remove</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @endforeach
                                @endif
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 


{{-- Add Member modal --}}
<div class="modal fade" id="addMemberModal" tabindex="1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class=" modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add a new team member</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="output_content">
                <div class="row" id='controllerForMember'>
                    <form method="POST" id="memberCheckerIfExist" action="javascript:void(0)">
                        @csrf
                        <label for="email1" class="col-form-label text-md-end">
                            Invite new members to your team:
                        </label>
                        <input id="email1" type="email" class="form-control" name="email1" placeholder='Email' required>
                        <input type='hidden' name="id" value='{{ $business->id }}'>
                        <p class='text-success w-auto' id='isTeamMember' style='display:none'>This account is already in
                            your team</p>
                        <button type="submit" id='next1' class="btn btn-success text-white my-3">Next <i class='fa fa-arrow-right fa-xs'></i></button>

                    </form>
                </div>
                <div class="row" id='createNewMember' style='display:none'>
                    <button class='btn btn-link w-auto' onclick='back()'><i class='fa fa-arrow-left  fa-xs'></i>
                        Back</button>
                    <p class='text-success w-auto'> This email is not registered yet, send an invitation to join Ivoice Gem: </p>
                    <form method="get" action="/businesses/{{ $business->id }}/inviteMembers" enctype="multipart/form-data">
                        @csrf
                        <div class="col-md-12">
                            <label for="name" class="col-form-label text-md-end">
                                Name
                            </label>
                            <input id="name" type="name" class="form-control" name="name" value='' placeholder='Name' required autofocus>

                            <label for="email2" class="col-form-label text-md-end">
                                Email address
                            </label>
                            <input id="email2" type="email" class="form-control" disabled>
                            <input id="email22" type="hidden" style="display:none" name="email">
 

                            <label for="role" class="col-form-label text-md-end">
                                Role
                            </label>
                            <select name="role" id="role" class="form-control">
                                <option value="CO_MANAGER">Co Manager</option>
                                <option value="TEAM_MEMBER" selected>Team Member</option>
                            </select>

                            <button type="submit" class="btn btn-success text-white my-3">Send</button>

                        </div>
                    </form>
                </div>

                <div class="row" id='addExistingMember' style='display:none'>
                    <button class='btn btn-link w-auto' onclick='back()'><i class='fa fa-arrow-left fa-xs'></i>
                        Back</button>

                    <form method="POST" action='/invitations' id='sendInviteForm'>
                        @csrf
                        <input type='hidden' name="business_id" value='{{ $business->id }}'>
                        <h4>Invite this user:</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="email" class="col-form-label text-md-end">
                                    Email address
                                </label>
                                <input id="email3" type="email" class="form-control" disabled>
                                <input id="email33" type="email" style="display:none" name="email">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="role" class="col-form-label text-md-end">
                                    Role
                                </label>
                                <select name="role" id="role2" class="form-control">
                                    <option value="CO_MANAGER">Co Manager</option>
                                    <option value="TEAM_MEMBER" selected>Team Member</option>
                                </select>
                            </div>
                        </div>
                    </form>
                        <button onclick="submitInviteForm()" id="sendInviteFormBtn" 
                                class="btn btn-success text-white m-3 col-md-3" >Submit</button>
                    
                    <script>
                        function submitInviteForm(){
                            document.getElementById("sendInviteFormBtn").disabled = true;
                            document.getElementById("sendInviteForm").submit();
                        }
                    </script>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
<script>
    var url = "/memberCheckerIfExist";
    if ($("#memberCheckerIfExist").length > 0) {
        $("#memberCheckerIfExist").validate({
            submitHandler: function(form) {
                $('#next1').html('Please Wait...');
                $("#next1").attr("disabled", true);
                $.ajax({
                    url: url,
                    type: "POST",
                    data: $('#memberCheckerIfExist').serialize(),
                    success: function(response) {
                        $('#next1').html("Next <i class='fa fa-arrow-right fa-xs'></i>");
                        $("#next1").attr("disabled", false);
                        //alert(response['success']);
                        if (response['success'] == 'exist') {
                            $('#createNewMember').css('display', 'none');
                            $('#addExistingMember').css('display', 'block');
                            $('#controllerForMember').css('display', 'none');
                            $('#isTeamMember').css('display', 'none');
                            $('#email3').val($('#email1').val());
                            $('#email33').val($('#email1').val());
                        } else if (response['success'] == 'notExist') {
                            $('#createNewMember').css('display', 'block');
                            $('#addExistingMember').css('display', 'none');
                            $('#controllerForMember').css('display', 'none');
                            $('#isTeamMember').css('display', 'none');
                            $('#email2').val($('#email1').val());
                            $('#email22').val($('#email1').val());
                        } else {
                            $('#isTeamMember').css('display', 'block');

                        }
                        document.getElementById("memberCheckerIfExist").reset();

                    },
                    error: function() {

                        $('#next1').html("Next <i class='fa fa-arrow-right fa-xs'></i>");
                        $("#next1").attr("disabled", false);
                        alert("Something went wrong! Try again");

                        document.getElementById("memberCheckerIfExist").reset();
                    }
                });
            }
        })
    }

    function back() {
        //alert('s')
        document.getElementById("createNewMember").style.display = 'none';
        document.getElementById("addExistingMember").style.display = 'none';
        document.getElementById("controllerForMember").style.display = 'block';
    }
</script>
@endsection

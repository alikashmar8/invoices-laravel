@extends('layouts.app')

@section('title', 'Create Bill')


@section('content')
    <div class="container"> 
        <form class="form" method='post' action="{{ route('bills.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="card">
                <div class="card-header closed">
                    <h5>Payment Details</h5>
                </div>
                <div class="card-body">
                    @if (count($businesses) > 1)
                        <div class="form-group">
                            <label for="business_id" class="required">Choose Business:</label>
                            <select id="business_select" class="form-control" name="business_id" id="business_id" required>
                                <option value hidden disabled selected>Choose Business</option>
                                @foreach ($businesses as $business)
                                    <option value="{{ $business->id }}"
                                        @if (old('business_id')) @if (old('business_id') == $business->id)
                                                selected @endif
                                    @elseif($business->pivot->is_favorite) selected @endif>
                                        {{ $business->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" id="business_id" name="business_id" value="{{ $businesses[0]->id }}">
                    @endif

                    <div class="form-group">
                        <label for="title" class="required">
                            Title:
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control"
                            id="bill-name" required placeholder="Bill">
                    </div>
                    <i>table of content --> </i>
                    <div class="form-group">
                        <label for="total" class="required">
                            Total Amount <small>AUD</small>:
                        </label>
                        <input type="number" name="total" value="{{ old('total') }}" class="form-control"
                            id="bill-amount" min="0" required>
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="total" class="required">
                            Total GST <small>AUD</small>:
                        </label>
                        <input type="number" name="gst" value="{{ old('gst') }}" class="form-control"
                            id="bill-gst" min="0" required>
                    </div>

                    <div class="form-check form-switch">
                        <style>
                            .form-check-input:checked {
                                background-color: #ff556e;
                                border-color: #ff556e;
                            }

                            .form-check-input:focus {
                                border-color: #ff556e90;
                                outline: 0;
                                box-shadow: 0 0 0 0.25rem #ff556e80;
                            }

                        </style>
                        <input name="is_paid" class="form-check-input mx-0 my-1 px-2 position-relative" type="checkbox"
                            checked id="flexSwitchCheckDefault" onclick='paymentCheckbox()'>
                        <label class="form-check-label px-2" for="flexSwitchCheckDefault">Paid Bill</label>
                    </div>

                    <div class="form-group" style='overflow:hidden;height:75px' id="payment_date_div">
                        <label for="payment_date">Payment Date:</label>
                        <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date') }}"
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date:</label>
                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}"
                            class="form-control">
                    </div> 
 
                </div>
            </div>
            <br>

            <div class="card ">
                <div class="card-header detailsClick closed">
                    <h5>Client Details <i id='detailsClickI' class="fa fa-arrow-circle-down text-primary"
                            style='transition: all .4s ease 0s;' aria-hidden="true"></i> </h5>
                </div>
                <div class="detailsContent " style='overflow: hidden; height:0px'>
                    
                    <div class="card-body  ">
                        <div class="form-group">
                            <label for="contact_id">Select Contact:</label>
                            <select name="contact_id" id="contact_id" onchange="hello(this)" class="form-control">
                                <option value=0 >Create new </option> 
                                @foreach($contacts as $contact)
                                    <option value={{$contact->id}}>{{$contact->name}} </option>
                                @endforeach 
                            </select>
                        </div>

                        <div id="new_user" > 
                            <label >Create New Contact:</label>
                            <div class="form-group">
                                <label  class="required">Name:</label>
                                <input type="text" name="contact_name" id='contact_name' required  class="form-control" >
                            </div>
                            <div class="form-group">
                                <label >Email:</label>
                                <input type="email" name="contact_email"   class="form-control"  >
                            </div>
                            <div class="form-group">
                                <label >ABN:</label>
                                <input type="text" name="contact_abn"  class="form-control"  >
                            </div>
                            <div class="form-group">
                                <label >Phone Number:</label>
                                <input type="phone" name="contact_phone" class="form-control"  >
                            </div>
                            <div class="form-group">
                                <label >Address:</label>
                                <input type="text" name="contact_address"  class="form-control"  >
                            </div>
                        </div>
                        
                        <script>
                            function hello(selectContact) {
                                if(selectContact.value !=0 ) {
                                    document.getElementById('new_user').style.display = "none";
                                    document.getElementById('contact_name').required = false;
                                }
                                else {
                                    document.getElementById('new_user').style.display = "block";
                                    document.getElementById('contact_name').required = true;
                                }
                            }
                        </script>

                    </div>
                </div> 
            </div>

            <br>
            <div class="card ">
                <div class="card-header advancedClick closed">
                    <h5>Items Details <small>(Optional)</small> <i id='advancedClickI'
                            class="fa fa-arrow-circle-down text-primary" style='transition: all .4s ease 0s;'
                            aria-hidden="true"></i> </h5>
                </div>
                <div class="advancedContent " style='overflow: hidden; height:0px'>
                    <div class="card-body  ">



                        <div class="form-group">
                            <label for="notes">Notes:</label>
                            <textarea name="notes" value="{{ old('notes') }}" class="form-control" id="notes" rows="3"></textarea>
                        </div>

                    </div>
                </div>
            </div>

            <button type="submit" class="mt-2 btn btn-primary">Create</button>
        </form>

        <script>
            document.getElementById("payment_date").value = new Date().toISOString().substring(0, 10);
            const payment_date_div = document.querySelector("#payment_date_div");

            const t1 = new TimelineMax();

            function paymentCheckbox() {
                if (document.getElementById("flexSwitchCheckDefault").checked) {
                    t1.fromTo(payment_date_div, 1, {
                        height: "0"
                    }, {
                        height: "75px",
                        ease: Power2.easeInOut
                    });

                } else {
                    t1.fromTo(payment_date_div, 1, {
                        height: "75"
                    }, {
                        height: "0px",
                        ease: Power2.easeInOut
                    });

                }
            }

            $(".advancedClick").click(function() {
                var $this = $(this),
                    $content = $(".advancedContent"),
                    $advancedClickI = $("#advancedClickI"); //$this.find(".content");
                if (!$this.hasClass("closed")) {
                    TweenLite.to($content, 0.5, {
                        height: 0
                    })
                    $this.addClass("closed")
                    document.getElementById("advancedClickI").style.transform = "rotate(0deg)";
                } else {
                    TweenLite.set($content, {
                        height: "auto"
                    })
                    TweenLite.from($content, 0.5, {
                        height: 0
                    })
                    $this.removeClass("closed");
                    document.getElementById("advancedClickI").style.transform = "rotate(180deg)";
                }
            })
            $(".detailsClick").click(function() {
                var $this = $(this),
                    $content = $(".detailsContent"),
                    $detailsClickI = $("#detailsClickI"); //$this.find(".content");detailsClickI
                if (!$this.hasClass("closed")) {
                    TweenLite.to($content, 0.5, {
                        height: 0
                    })
                    $this.addClass("closed")
                    document.getElementById("detailsClickI").style.transform = "rotate(0deg)";
                } else {
                    TweenLite.set($content, {
                        height: "auto"
                    })
                    TweenLite.from($content, 0.5, {
                        height: 0
                    })
                    $this.removeClass("closed");
                    document.getElementById("detailsClickI").style.transform = "rotate(180deg)";
                }
            })
            $(document).ready(function() {

                var business_id_input = document.getElementById('business_id')
                var business_id = null
                if(business_id_input)
                {
                    business_id = business_id_input.value
                }
                if(!business_id)    business_id = document.getElementById('business_select').value;
                if(business_id) fillContacts(business_id)

                $('#business_select').on('change', function() {
                    var business_id = $(this).val();
                    if (business_id) {
                        fillContacts(business_id)
                    } else {
                        $('#major_track_id').empty();
                    }
                });

                function fillContacts(business_id){
                    $.ajax({
                            url: '/businesses/' + business_id + '/contacts',
                            type: "GET",
                            data: {
                                "_token": "{{ csrf_token() }}"
                            },
                            dataType: "json",
                            success: function(data) {
                                if (data) {
                                    $('#contact_id').empty();
                                    $('#contact_id').append(
                                        '<option hidden disabled value selected>-- Choose Contact --</option>'
                                    );
                                    $.each(data, function(key, contact) {
                                        $('select[name="contact_id"]').append(
                                            '<option value="' + contact.id + '">' +
                                            contact
                                            .name + '</option>');
                                    });
                                } else {
                                    $('#contact_id').empty();
                                }
                            }
                        });
                }
            });
        </script>

    </div>

@endsection
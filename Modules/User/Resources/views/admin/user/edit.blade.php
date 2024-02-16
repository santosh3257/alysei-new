@extends('admin.layouts.app')
<style>
.container {
    width: 600px;
    margin: 100px auto;
}

.progressbar {
    counter-reset: step;
}

.basicInfo th,
td {
    font-size: 12px;
}

td .progressbar li {
    list-style-type: none;
    width: 25%;
    float: left;
    font-size: 12px;
    position: relative;
    text-align: center;
    text-transform: uppercase;
    color: #7d7d7d;
}

.progressbar li:before {
    width: 30px;
    height: 30px;
    content: counter(step);
    counter-increment: step;
    line-height: 30px;
    border: 2px solid #7d7d7d;
    display: block;
    text-align: center;
    margin: 0 auto 10px auto;
    border-radius: 50%;
    background-color: white;
}

.progressbar li:after {
    width: 100%;
    height: 2px;
    content: '';
    position: absolute;
    background-color: #7d7d7d;
    top: 15px;
    left: -50%;
    z-index: -1;
}

.progressbar li:first-child:after {
    content: none;
}

.progressbar li.active {
    color: green;
}

.progressbar li.active:before {
    border-color: #55b776;
}

.progressbar li.active+li:after {
    background-color: #55b776;
}
.select2-container{
    width: 100% !important;
}
.select2-container--default .select2-dropdown.select2-dropdown--below{
    max-width: 22rem;
    width: 100% !important;
}
</style>
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Users</h1>
                <span>User-#{{$user->user_id}}</span>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('login/dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        @if (\Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {!! \Session::get('success') !!}
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {!! \Session::get('error') !!}
                </div>
            @endif
        <div class="card">
            <div class="card-header d-flex p-0">
                <h3 class="card-title p-3">Manage user</h3>
                <ul class="nav nav-pills ml-auto p-2">
                    <li class="nav-item"><a class="nav-link active" href="#tab_1" data-toggle="tab">Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab_5" data-toggle="tab">Edit Info</a></li>
                    @if($user->role_id == 3 || $user->role_id == 4 || $user->role_id == 5 || $user->role_id == 6)
                    <li class="nav-item"><a class="nav-link" href="#tab_7" data-toggle="tab">Product Type</a></li>
                    @endif
                    <li class="nav-item"><a class="nav-link" href="#tab_6" data-toggle="tab">Hubs</a></li>
                    @if($user->role_id != 10)
                    <li class="nav-item"><a class="nav-link" href="#tab_2" data-toggle="tab">Membership State</a></li>
                    @endif
                    <li class="nav-item"><a class="nav-link" href="#tab_3" data-toggle="tab">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab_4" data-toggle="tab">Privacy</a></li>

                </ul>
            </div>

            <div class="card-body">

                <div class="tab-content">

                    <div class="tab-pane active" id="tab_1">
                        <h4>User Info</h4>
                        <div class="card-bodys">
                            <table class="table table-bordered table-striped basicInfo">
                                <tbody>
                                    <?php //dd($fields); ?>
                                    @foreach($fields as $field)
                                        <tr>
                                            <td>{{ $field->title }}</td>
                                            <td>
                                                @if(is_array($field->value))
                                                    @foreach($field->value as $key=>$value)
                                                    <h6 class="upperTitle">{{$key}}</h6>
                                                        @if(is_array($value))
                                                        @foreach($value as $k=>$v)
                                                        <div class="propertyType">
                                                            <h6>{{$k}}</h6>
                                                            <span>{{implode(',',$v)}}</span>
                                                        </div>
                                                            <br />
                                                        @endforeach
                                                        @endif
                                                    @endforeach
                                                @else 
                                                    @if($field->value == 621)
                                                    Yes
                                                    @elseif($field->value == 622)
                                                    No
                                                    @elseif($field->value == 623)
                                                    Private label
                                                    @elseif($field->value == 624)
                                                    My Label
                                                    @elseif($field->value == 741)
                                                    Private/Own
                                                    @elseif($field->value == 625)
                                                    Yes
                                                    @elseif($field->value == 626)
                                                    No
                                                    @else
                                                    {{$field->value}}
                                                    @endif  
                                                @endif
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2" style="margin-top: 70px;margin-bottom: 50px;">
                        <div class="manage" style="float: right;">
                            <!-- <a href="javascript:void(0)" data-toggle="modal" data-target="#myModal"><i class="fa fa-desktop" title="View Details"></i></a> -->
                            <button class="manage form-control" data-toggle="modal"
                                data-target="#myModal">Manage</button>
                        </div>
                        <ul class="progress-indicator stepped ">
                            <!-- stacked -->
                            <li @if($user->alysei_review == 1) class="completed" @else class="" @endif>

                                <span class="bubble"></span>
                                <span class="stacked-text">
                                    <span class="fa fa-eye"></span> Review
                                    <!-- <span class="subdued">/ Added a thing. <em>Pssst... I'm a link!</em></span> -->
                                </span>

                            </li>
                            <li @if($user->alysei_certification == 1) class="completed" @else class="" @endif>
                                <span class="bubble"></span>
                                <span class="stacked-text">
                                    <span class="fa fa-certificate"></span> Alysei Certification

                                    <!--  <span class="subdued">/ Some stuff happened. It was amazing.</span> -->
                                </span>
                            </li>
                            <li @if($user->alysei_recognition == 1) class="completed" @else class="" @endif>
                                <span class="bubble"></span>
                                <span class="stacked-text">
                                    <span class="fa fa-id-card"></span> Recognition
                                    <!-- <span class="subdued">/ What a wild day!</span> -->
                                </span>
                            </li>
                            <li @if($user->alysei_qualitymark == 1) class="completed" @else class="" @endif>
                                <span class="bubble"></span>
                                <span class="stacked-text">
                                    <span class="fa fa-check"></span> Quality Mark
                                    <!-- <span class="subdued">/ This day is toooo long.</span> -->
                                </span>
                            </li>

                        </ul>



                        <div id="myModal" class="modal fade" role="dialog">
                            <div class="modal-dialog">

                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title" style="font-weight: 400;">Membership State</h3>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="{{url('dashboard/update-progress',[$user->user_id])}}" method="post">
                                        @csrf
                                        <div class="modal-body">
                                            <?php  
                          if($user->alysei_review == '0') 
                          {
                              $level = 'Alysei Review'; 
                              $setLevel = 'alysei_review';
                          }
                          elseif($user->alysei_certification == '0')
                          {
                              $level = 'Alysei Certification'; 
                              $setLevel = 'alysei_certification';
                          }
                          elseif($user->alysei_recognition == '0')
                          {
                              $level = 'Alysei Recognition'; 
                              $setLevel = 'alysei_recognition';
                          }
                          elseif($user->alysei_qualitymark == '0')
                          {
                              $level = 'Alysei Quality Mark'; 
                              $setLevel = 'alysei_qualitymark';
                          }
                          else
                          {
                              $level = ''; 
                              $setLevel = 'level_empty';
                          }
                        ?>
                                            <input type="hidden" value="{{$setLevel}}" name="progress_level">

                                            <div class="alert alert-warning" role="alert">Setting user to
                                                <strong>{{$level}}</strong>.
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <textarea placeholder="Notification message to user."
                                                            class="form-control"></textarea>

                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-default" type="submit"> Submit</button>



                                        </div>
                                    </form>
                                    <div class="modal-footer">

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_3">
                        <table class="table table-bordered table-striped basicInfo">
                                <tbody>
                                        <tr>
                                            <td>Email</td>
                                            <td>{{ $contact->email }}</td>
                                        </tr>
                                        <tr>
                                            <td>Country Code</td>
                                            @if(strpos($contact->country_code, "+") !== false)
                                            <td>{{ $contact->country_code }}</td>
                                            @else
                                            <td>+{{ $contact->country_code }}</td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <td>Phone</td>
                                            <td>{{ $contact->phone }}</td>
                                        </tr>
                                        <tr>
                                            <td>Address</td>
                                            <td>{{ $contact->address }}</td>
                                        </tr>
                                        <tr>
                                            <td>Website</td>
                                            <td>{{ $contact->website }}</td>
                                        </tr>
                                        <tr>
                                            <td>Facebook Link</td>
                                            <td>{{ $contact->fb_link }}</td>
                                        </tr>
                                        <tr>
                                            <td>About</td>
                                            <td>{{ $contact->about }}</td>
                                        </tr>
                                </tbody>
                            </table>
                    </div>

                    <div class="tab-pane" id="tab_4">
                        <form>
                            <div class="form__item mb-4">
                                <label class="font-16 text-normal">WHO CAN SEND YOU A PRIVATE MESSAGE?</label>
                                    <select class="form-select">
                                        <option value="anyone" <?php ($privacyData['allow_message_from'] == 'anyone') ? 'selected' : '' ?>>Anyone</option>
                                        <option value="followers" <?php ($privacyData['allow_message_from'] == 'followers') ? 'selected' : '' ?>>Followers</option>
                                        <option value="connections" <?php ($privacyData['allow_message_from'] == 'connections') ? 'selected' : '' ?>>Connections</option>
                                        <option value="just me" <?php ($privacyData['allow_message_from'] == 'just me') ? 'selected' : '' ?>>Just me</option>
                                    </select>
                                </div>
                                <div class="form__item mb-4">
                                    <label class="font-16 text-normal">WHO CAN VIEW YOUR PROFILE?</label>
                                        <select class="form-select">
                                            <option value="anyone">Anyone</option>
                                            <option value="followers">Followers</option>
                                            <option value="connections">Connections</option>
                                            <option value="just me">Just me</option>
                                        </select>
                                    </div>
                                    <div class="form__item mb-4">
                                        <label class="font-16 text-normal">WHO CAN CONNECT WITH YOU?</label>
                                        <div class="form-checkbox-container">
                                            <div class="form-checkbox-container-items">
                                                <?php $who_can_connect =  explode(',',$privacyData['who_can_connect']); ?>
                                                @foreach($roles as $key => $role)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="{{$role->role_id}}" {{ (in_array($role->role_id, $who_can_connect)) ? 'checked' : '' }} >
                                                    <label>{{ $role->name }}</label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form__item mb-4">
                                        <label class="font-16 text-normal">Email Preferences</label>
                                        <div class="form-checkbox-container">
                                            <div class="form-checkbox-container-items">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="2" value="1" <?php ($messagePreference['private_messages'] == 1) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="2">Private messages</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="3" value="1" <?php ($messagePreference['when_someone_request_to_follow'] == 1) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="3">When someone request to follow</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="4" value="1" <?php ($messagePreference['weekly_updates'] == 1) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="4">Weekly updates</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                    <!-- /.tab-pane -->
                    </div>
                    <div class="tab-pane" id="tab_5">
                        <form method="post" action="{{url('dashboard/users/update')}}">
                            {{ csrf_field() }}
                              @if($user->role_id === 7 || $user->role_id === 10)
                              <div class="form-group">
                                <label for="first name">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" value="{{ $user->first_name }}">
                              </div>
                              <div class="form-group">
                                <label for="last name">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" value="{{ $user->last_name }}">
                              </div>
                              @endif
                              @if($user->role_id === 3 || $user->role_id === 4 || $user->role_id === 5 || $user->role_id === 6 || $user->role_id === 8)
                              <div class="form-group">
                                <label for="company_name">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Company Name" value="{{ $user->company_name }}">
                              </div>
                              @endif
                              @if($user->role_id === 9)
                              <div class="form-group">
                                <label for="Restaurant name">Restaurant Name</label>
                                <input type="text" class="form-control" id="restaurant" name="restaurant_name" placeholder="Restaurant Name" value="{{ $user->restaurant_name }}">
                              </div>
                              @endif
                              <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                              <input type="hidden" name="user_id" value="{{ $user->user_id }}">
                              <button type="submit" class="btn btn-primary">Submit</button>      
                        </form>
                    </div>
                    <div class="tab-pane" id="tab_6">
                        <div class="row">
                            @foreach($selectedhubs as $hub)
                                <div class="col-md-3" id="{{$hub->hub_id}}">
                                    <div class="hub-inner" style="padding:20px;background: #ccc;margin-bottom: 10px;"> 
                                        <span>{{ $hub->hub->title }}</span>
                                        <span style="float:right">
                                            <a href="javascript:void(0)" data-hub_id="{{$hub->hub_id}}" data-user_id="{{$hub->user_id}}" class="remove_hub">
                                                <i class="fa fa-times"></i>
                                            </a>
                                            </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <button class="form-control btn btn-primary" data-toggle="modal"
                                data-target="#new_hub">Add new hub</button>    
                            </div>
                        </div>

                        <div id="new_hub" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title" style="font-weight: 400;">Choose Hubs</h3>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="{{url('dashboard/users/add/hubs')}}" method="post">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="hidden" value="{{$user->user_id}}" name="user_id">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="select hub">Select Hub</label>
                                                        </div>
                                                        <div class="col-md-6 text-right">
                                                            <input type="checkbox" id="selectAll"> Select All
                                                        </div>
                                                        </div>
                                                        <select class="form-control" name="hub_id[]" id="hubs_select" multiple="multiple" required>
                                                        @foreach($hubs as $hub)
                                                            <option value="{{$hub->id}}">{{$hub->title}}</option>
                                                        @endforeach
                                                        </select>
                                                      </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-default" type="submit"> Submit</button>
                                        </div>
                                    </form>
                                    
                                    <div class="modal-footer">

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="tab-pane" id="tab_7">
                        <div class="row productType">
                            <form action="" method="post">
                                <ul>
                                    @if(count($roleFields) > 0)
                                    @foreach($roleFields as $field)
                                        @if($field->title == 'Product type')
                                            @if(count($field->options) > 0)
                                                @foreach($field->options as $idx=>$opt)
                                                <li class="typeProduct">
                                                    <input type="checkbox" data-id="{{$opt->user_field_option_id}}" class="consProperty productTypeOption" name="productType" value="{{$opt->user_field_option_id}}" {{$opt->is_selected ? 'checked' : ''}}><label for="option">{{$opt->option}}</label>
                                                    @if(count($opt->options) > 0)
                                                    <div class="propertyMethod {{$opt->is_selected ? '' : 'is_hide'}}" id="{{$opt->user_field_option_id}}">
                                                        @foreach($opt->options as $methods)
                                                        @if($methods->optionType == 'conservation')
                                                        <ul class="methods">
                                                        <h4>{{$methods->option}}</h4>
                                                        @if(count($methods->options) > 0)
                                                            @foreach($methods->options as $can)
                                                                <li><label><input type="checkbox" class="consProperty method_{{$opt->user_field_option_id}}" name="productType"  value="{{$can->user_field_option_id}}" {{$can->is_selected ? 'checked' : ''}}> {{$can->option}}</label></li>
                                                            @endforeach
                                                        @endif
                                                        </ul>
                                                        @else
                                                        <ul class="methods">
                                                        <h4>{{$methods->option}}</h4>
                                                        @if(count($methods->options) > 0)
                                                            @foreach($methods->options as $can)
                                                                <li><label><input type="checkbox" class="consProperty method_{{$opt->user_field_option_id}}" name="productType"  value="{{$can->user_field_option_id}}" {{$can->is_selected ? 'checked' : ''}}> {{$can->option}}</label></li>
                                                            @endforeach
                                                        @endif
                                                        </ul>
                                                        @endif
                                                        @endforeach
                                                    </div>
                                                    @endif
                                                </li>
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                    @endif
                                </ul>
                                <input type="hidden" name="userId" class="userID" value="{{$user->user_id}}">
                                <div class="text-center"><button type="button" class="btn btn-primary" id="submitSave">Save</button></div>
                            </form>
                        </div>
                    </div>
                <!-- /.tab-content -->


            </div>


        </div>
    </div>
</section>

@endsection

@push('footer_script')
<script>
$(document).ready(function() {
    $('.userstatus').change(function() {
        let status = $(this).val();
        let id = $(this).data("status_id");
        handleStatus(id, status);
    });
});
var dataId = [];

function handleStatus(id, status) {
    if (id != '') {
        dataId = [id];
    }
    if (confirm("Are you sure you want to change the status?") == true) {
        $.ajax({
            url: "{{url('login/user-status')}}",
            type: 'post',
            data: {
                'id': dataId,
                'status': status,
                '_token': '{{ csrf_token() }}'
            },
            success: function(path) {
                location.reload();
            }
        });
    } else {
        return false;
    }
}



</script>
@endpush

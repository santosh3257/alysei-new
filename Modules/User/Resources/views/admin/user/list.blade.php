@extends('admin.layouts.app')

@section('content')
<?php
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    $role_id = isset($_GET['role']) ? $_GET['role'] : -1;
?>
<style>
#deleteAllUsers {
    font-size: 12px;
    padding: 5px 10px;
    position: absolute;
    right: 5px;
    top: 8px;
}
</style>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Users</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                    <form class="form-inline" method="get">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="inputPassword2" class="sr-only">Email/Name</label>
                            <input type="text" class="form-control" value="{{$keyword}}" id="keyword" placeholder="Email/Name" name="keyword">
                        </div>
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="inputPassword2" class="sr-only">Roles</label>
                            <select class="form-control" id="role" name="role">
                              <option value="-1">Select Role</option>
                              @foreach($roles as $role)
                                <option value="{{ $role->role_id }}" {{($role->role_id == $role_id) ? 'selected' : ''}}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                      <button type="submit" class="btn btn-primary mb-2">Filter</button>
                      <button type="reset" class="btn btn-primary mb-2 reset ml-2">Reset</button>
                    </form>

            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 7.75rem;position: relative;"><input type="checkbox" name="" onchange="selectAll();" class="allSelect"> All <button type="button" id="deleteAllUsers" onClick="deleteUsers();" class="btn btn-primary mb-2">Delete</button></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr role="row">
                            <td><input type="checkbox" name="rowId" class="singleSelect" value="{{$user->user_id}}"></td>
                            <td>
                                @if($user->role_id == 9)
                                {{$user->restaurant_name}}
                                @elseif(!empty($user->first_name))
                                {{$user->first_name.' '.$user->last_name}}
                                @elseif(!empty($user->company_name))
                                {{$user->company_name}}
                                @else
                                {{$user->name}}
                                @endif
                            </td>
                            <td>{{$user->email}}
                                @if($user->alysei_review == '0' && $user->role_id != 10)
                                <span class="badge bg-danger"> {{'Pending Review'}}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->alysei_qualitymark == 1)
                                QUALITY MARK
                                @elseif($user->alysei_recognition == 1)
                                RECOGNITION
                                @elseif($user->alysei_certification == 1)
                                ALYSEI CERTIFICATION
                                @elseif($user->alysei_review == 1)
                                REVIEW
                                @elseif($user->role_id !== 10)
                                Pending
                                @endif
                            </td>
                            <td>@if($user->role_id == 3) Italian F&B Producers @elseif($user->role_id == 7) Voice Of
                                Expert @elseif($user->role_id == 8) Travel Agencies @elseif($user->role_id == 9)
                                Restaurants @elseif($user->role_id == 10) Voyagers @elseif($user->role_id == 6) Importer
                                & Distributor @elseif($user->role_id == 4) Importer @elseif($user->role_id == 5) Distributor @endif</td>
                            <td>
                                {{date('F j, Y', strtotime($user->created_at))}}
                            </td>

                            <td>
                                <a class="fa fa-edit" href="{{url('dashboard/users/edit', [$user->user_id])}}" title="Edit"></a> |
                                <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('/dashboard/users/delete', [$user->user_id])}}"
                                        title="Delete"></a></td>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="dataTables_info" id="file_export_info" role="status" aria-live="polite">Showing
                    {{$users->firstItem()}} to {{$users->lastItem()}} of {{$users->total()}} entries
                </div>


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                {{$users->appends($_GET)->links()}}
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    function deleteUser(id) {

        if (confirm("Are you sure you want to delete?") == true) {
            $.ajax({
                url: "{{url('/dashboard/users/delete')}}",
                type: 'post',
                data: {
                    'id': id,
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
<script>
    $(function() {
        $('.userstatus').change(function() {
            let status = $(this).val();
            let id = $(this).data("status_id");
            handleStatus(id, status);
        });

        $(".reset").click(function(){
            $("#keyword").val("");
            $("#role").val("");
            $("form").submit();
        });
    });
    function isReview(id, status) {
        if (confirm("Are you sure you want to change the status?") == true) {
            $.ajax({
                url: "{{url('/login/review-status')}}",
                type: 'post',
                data: {
                    'id': id,
                    'status': status,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(path) {
                    location.reload();
                }
            });
        }
    }

    function isCertified(id, status) {
        if (confirm("Are you sure you want to change the status?") == true) {
            $.ajax({
                url: "{{url('/login/certified-status')}}",
                type: 'post',
                data: {
                    'id': id,
                    'status': status,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(path) {
                    location.reload();
                }
            });
        }
    }

    function isRecognised(id, status) {
        if (confirm("Are you sure you want to change the status?") == true) {
            $.ajax({
                url: "{{url('/login/recognised-status')}}",
                type: 'post',
                data: {
                    'id': id,
                    'status': status,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(path) {
                    location.reload();
                }
            });
        }
    }

    function isQM(id, status) {
        if (confirm("Are you sure you want to change the status?") == true) {
            $.ajax({
                url: "{{url('/login/qm-status')}}",
                type: 'post',
                data: {
                    'id': id,
                    'status': status,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(path) {
                    location.reload();
                }
            });
        }
    }

    var dataId = [];

    function handleStatus(id, status) {
        if (id != '') {
            dataId = [id];
        }

        if (confirm("Are you sure you want to change the status?") == true) {
            $.ajax({
                url: "{{url('dashboard/user-status')}}",
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

    function selectAll() {
        if ($('.allSelect').is(':checked')) {
            $('.singleSelect').prop('checked', true);
        } else {
            $('.singleSelect').prop('checked', false);
        }
    }



   

    function Action(e) {
        var inputValue = $('#action').val();

        var arrayValue = ['active', 'inactive', 'expired', 'incomplete'];
        var status = '';
        if (inputValue == '') {
            return false;
        } else if (arrayValue.includes(inputValue)) {

            var inputs = $(".singleSelect");
            for (var i = 0; i < inputs.length; i++) {
                if ($(inputs[i]).is(":checked")) {
                    dataId.push($(inputs[i]).data('id'));
                }
            }
            if (dataId.length < 1) {
                alert('Select Min 1 Row')
            }
            // else if(inputValue == 'Delete') {
            //     isDeleted();
            // }
            else if (inputValue == 'active') {
                status = 'active';
            } else if (inputValue == 'inactive') {
                status = 'inactive';
            } else if (inputValue == 'expired') {
                status = 'expired';
            } else if (inputValue == 'incomplete') {
                status = 'incomplete';
            }
            handleStatus('', status)
        }
    }

    function deleteUsers(){
        let userIds = [];
        var checkboxes = document.querySelectorAll('input[name="rowId"]:checked');
        for (var checkbox of checkboxes) {
            userIds.push(checkbox.value);
        }
        if(userIds.length > 0){
            if (confirm("Are you sure you want delete?") == true) {

                $.ajaxSetup({
                      headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      }
                  });
                  $.ajax({
                      type: 'POST', //THIS NEEDS TO BE GET
                      url: '/dashboard/deleteusers',
                      data: {ids:userIds},
                      dataType: 'json',

                      success: function (response) {
                        if(response.success){
                            location.reload();
                        }
                        else{
                            alert(response.message);
                        }
                      },
                      error: function() { 
                          console.log('cfgdfgsdf');
                      }

                  });
            } else {
                return false;
            }
        }
        else{
            alert("please select atleast one record.");
        }
    }
</script>
@endsection
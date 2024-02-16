@extends('admin.layouts.app')

@section('content')
<?php
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
?>
<section class="content-header">
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
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>User Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/users')}}">Users</a></li>
                    <li class="breadcrumb-item active">User Report</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="card-header">
                    <form class="form-inline" method="get">
                        <div class="form-group mx-sm-3 mb-2">
                            <input type="text" class="form-control" value="{{$keyword}}" id="keyword" placeholder="Hub/State name" name="keyword">
                        </div>
                      <button type="submit" class="btn btn-primary mb-2">Search</button>
                      <button type="reset" class="btn btn-primary mb-2 reset ml-2">Reset</button>
                    </form>

            </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <!-- <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();" class="allSelect"> All </th> -->
                                <th>SL. No.</th>
                                <th>Report By</th>
                                <th>User</th>
                                <th>Reason Report</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($reportUsers)
                            @foreach($reportUsers as $key=>$user)
                            <tr role="row">
                                <td>
                                    {{$key+1}}
                                </td>
                                <td>
                                 @if($user->report_by_user_info->first_name != '')
                                    {{ucfirst($user->report_by_user_info->first_name)}} {{ucfirst($user->report_by_user_info->last_name)}}
                                 @else
                                    {{ucfirst($user->report_by_user_info->company_name)}}
                                 @endif
                                </td>
                                <td>
                                @if($user->report_to_user_info->first_name != '')
                                    {{ucfirst($user->report_to_user_info->first_name)}} {{ucfirst($user->report_to_user_info->last_name)}}
                                 @elseif($user->report_to_user_info->company_name !='')
                                    {{ucfirst($user->report_to_user_info->company_name)}}
                                 @else
                                    {{ucfirst($user->report_to_user_info->restaurant_name)}}
                                 @endif
                                </td>
                                <td>
                                    @if($user->report_as == 'Other')
                                        {{$user->message}}
                                    @else
                                        {{$user->report_as}}
                                    @endif
                                </td>
                                <td>
                                <a class="fa fa-eye" href="{{url('dashboard/users/edit', [$user->user_id])}}" title="View"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="#" title="Delete"></a>

                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{$reportUsers->links()}}
                </div>
            </div>
        </div>
</section>
<script>
$(function() {
    $(".reset").click(function(){
        $("#keyword").val("");
        $("form").submit();
    });
});
</script>
@endsection

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
                <h1>Hubs</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/users')}}">Users</a></li>
                    <li class="breadcrumb-item active">Hubs</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div style="float:right;position: relative;z-index: 999;">
                    <a class="nav-link" href="{{url('dashboard/user/hub/add')}}">
                        Add New
                    </a>
                </div>

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
                                <th>Country</th>
                                <th>Hub Name</th>
                                <th>State</th>
                                <th>Image</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hubs as $hub)
                            <tr role="row">
                                <!-- <td><input type="checkbox" name="" class="singleSelect" data-id="{{$hub->id}}"></td> -->
                                <td>
                                    {{ $hub->country->name }}
                                </td>
                                <td>
                                    {{ $hub->title }}
                                </td>
                                <td>
                                    {{ $hub->name }}
                                </td>
                                <td>
                                    <img src="{{ $hub->attachment->base_url }}{{ $hub->attachment->attachment_url }}" width="50px">
                                </td>
                                <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/user/hub/edit', [$hub->id])}}" title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/user/hub/delete', [$hub->id])}}" title="Delete"></a>

                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{$hubs->links()}}
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

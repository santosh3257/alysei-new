@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Roles</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/users')}}">Users</a></li>
                    <li class="breadcrumb-item active">Roles</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Roles List</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/user/role/add')}}">
                        Add New
                    </a>
                </div>
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();"
                                    class="allSelect"> All </th>
                            <th>Name</th>
                            <th>Role type</th>
                            <th>Description</th>
                            <th>Added Date</th>
                            <th>Order</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $role)
                        <tr role="row">
                            <td><input type="checkbox" name="" class="singleSelect" data-id="{{$role->role_id}}"></td>
                            <td>
                                {{$role->name}}
                            </td>
                            <td>
                                {{$role->type}}
                            </td>
                            <td>
                                {{$role->description_en}}
                            </td>
                            <td>
                                {{date('F j, Y', strtotime($role->created_at))}}
                            </td>
                            <td>{{$role->order}}</td>

                            <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/user/role/edit', [$role->role_id])}}" title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/user/role/delete', [$role->role_id])}}"
                                        title="Delete"></a>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
            {{$roles->links()}}
            </div>
        </div>
    </div>
</section>
@endsection

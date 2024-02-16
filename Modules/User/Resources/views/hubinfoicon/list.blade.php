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
                    <li class="breadcrumb-item active">Hub Info Icon</li>
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
            <div class="card-header">
                <h3 class="card-title">Hub info icon</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/hub-infoicon/add')}}">
                        Add New
                    </a>
                </div>
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Message En</th>
                            <th>Message It</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($data)
                            @foreach($data as $key=>$value)
                                <tr>
                                    <td>{{$value->roledata->name}}</td>
                                    <td>{{$value->message_en}}</td>
                                    <td>{{$value->message_it}}</td>
                                    <td> <a class="fa fa-edit" href="{{url('dashboard/hub-infoicon/edit', [$value->id])}}" title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/hub-infoicon/delete', [$value->id])}}"
                                        title="Delete"></a></td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
            {{$data->links()}}
            </div>
        </div>
    </div>
</section>
@endsection

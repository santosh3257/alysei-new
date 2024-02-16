@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Registration form fields</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">fields</li>
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
                <h3 class="card-title">Form fields</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/registration/field/add')}}">
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
                            <th>Title</th>
                            <th>placeholder</th>
                            <th>Field Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($fields)
                            @foreach($fields as $key=>$field)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect" data-id="{{$field->user_field_id}}"></td>
                                <td>
                                    {{ $field->title_en }}
                                </td>
                                <td>
                                {{ $field->placeholder_en }}
                                </td>
                                <td>
                                {{ $field->type }}
                                </td>
                                <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/registration/field/edit', [$field->user_field_id])}}"
                                        title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/registration/field/delete', [$field->user_field_id])}}"
                                        title="Delete"></a>

                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
            {{$fields->links()}}
            </div>
        </div>
    </div>
</section>
@endsection

@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Registration form field options</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">options</li>
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
                            <th>Option name</th>
                            <th>Hint</th>
                            <th>Parent</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($fieldoptions)
                            @foreach($fieldoptions as $key=>$option)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect" data-id="{{$option->user_field_option_id}}"></td>
                                <td>
                                    {{ $option->option_en }}
                                </td>
                                <td>
                                {{ $option->hint_en }}
                                </td>
                                <td>
                                
                                </td>
                                <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/registration/field/edit', [$option->user_field_option_id])}}"
                                        title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/registration/field/delete', [$option->user_field_option_id])}}"
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
            {{$fieldoptions->links()}}
            </div>
        </div>
    </div>
</section>
@endsection

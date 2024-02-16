@extends('admin.layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Site Localization</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Localization</li>
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
                <form class="form-inline" method="get">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="inputPassword2" class="sr-only">Key/English/Italian</label>
                            <input type="text" class="form-control" id="keyword" placeholder="Enter Key/English/Italian" name="keyword" value="{{$keyword}}">
                        </div>
                          <button type="submit" class="btn btn-primary mb-2">Filter</button>
                      <a class="btn btn-primary mb-2 ml-2" href="?clear=true"  class="btn btn-primary mb-2 reset ml-2">Reset</a>
                    </form>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/localization/create')}}">
                        Add New
                    </a>
                </div>

                <!-- /.card-header -->
                <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <!-- <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();" class="allSelect"> All </th> -->
                                    <th>Key</th>
                                    <th>English</th>
                                    <th>Italian</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($languages as $language)
                                <tr role="row">
                                    <td>
                                        {{ $language->key }}
                                    </td>
                                    <td>
                                        {{ $language->en }}
                                    </td>
                                    <td>
                                        {{ $language->it }}
                                    </td>
                                    <td>
                                        <a class="fa fa-edit" href="{{url('dashboard/localization/edit', [$language->id])}}" title="Edit"></a> |
                                        <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/localization/delete', [$language->id])}}" title="Delete"></a>

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                <!-- /.card-body -->

                <div class="card-footer clearfix">
                    {{$languages->links()}}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

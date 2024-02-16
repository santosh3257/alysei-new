@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>App Version Manage</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">App Version</li>
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
                <h3 class="card-title">Alysei News</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/version-manager/create')}}">
                        Add New
                    </a>
                </div>
              
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <!-- <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();"
                                    class="allSelect"> All </th> -->
                            <th>Android version</th>
                            <th>IOS version</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($appVersions)
                            @foreach($appVersions as $key=>$version)
                            <tr role="row">
                                <!-- <td><input type="checkbox" name="" class="singleSelect" data-id="{{$version->id}}"></td> -->
                                <td>
                                    {{ $version->android }}
                                </td>
                                <td>
                                    {{$version->ios}}
                                </td>
                                <td>
                                    @if($version->status == '1')
                                        Current
                                    @else
                                        Old
                                    @endif
                                </td>
                                <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/version-manager/edit', [$version->id])}}"
                                        title="Edit"></a>

                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                {{$appVersions->links()}}
            </div>
        </div>
    </div>
</section>
@endsection

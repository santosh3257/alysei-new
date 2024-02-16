@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Inco-Terms</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Inco-Terms</li>
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
                <h3 class="card-title">Inco-Terms</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/marketplace/add/inco-term')}}">
                        Add New
                    </a>
                </div>
               
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 4.75em;"># </th>
                            <th>Inco Terms</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($incoterms)
                            @foreach($incoterms as $key=>$incoterm)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect" data-id="{{$incoterm->id}}"></td>
                                <td>
                                    {{ $incoterm->incoterms }}
                                </td>
                               
                                <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/marketplace/inco-term/edit', [$incoterm->id])}}"
                                        title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href=""
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
                {{$incoterms->appends($_GET)->links()}}
            </div>
        </div>
    </div>
</section>
@endsection

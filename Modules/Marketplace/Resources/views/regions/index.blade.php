@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Marketplace Regions</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/marketplace')}}">Marketplace</a></li>
                    <li class="breadcrumb-item active">Regions</li>
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
                <!-- <h3>Melas</h3> -->
                <!-- <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/marketplace/region/add')}}">
                        Add New
                    </a>
                </div> -->


                <!-- /.card-header -->
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();"
                                        class="allSelect"> All </th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($regions as $region)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect"
                                        data-id="{{$region->id}}"></td>
                                <td>
                                    {{ $region->name }}
                                </td>
                                <td>
                                    <img src="{{ $region->attachment->base_url }}{{ $region->attachment->attachment_url }}"
                                        width="50px">
                                </td>
                               
                                <td>
                                    <a class="fa fa-edit"
                                        href="{{url('dashboard/marketplace/region/edit', [$region->id])}}"
                                        title="Edit"></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{$regions->links()}}
                </div>
            </div>
        </div>
</section>

@endsection
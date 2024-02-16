@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Medals</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Medals</li>
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
                <h3>Ingredients</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/award-medal/add')}}">
                        Add New
                    </a>
                </div>


                <!-- /.card-header -->
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();"
                                        class="allSelect"> All </th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($medals as $medal)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect"
                                        data-id="{{$medal->medal_id}}"></td>
                                <td>
                                    {{ $medal->name }}
                                </td>                                

                                <td>
                                    <a class="fa fa-edit"
                                        href="{{url('dashboard/award-medal/edit', [$medal->medal_id])}}"
                                        title="Edit"></a> | <!--  |
                                    <a class="fa fa-trash" title="Delete"></a> -->
                                    <a onclick="return confirm('Are you sure? You want to delete it.')"
                                        class="fa fa-trash"
                                        href="{{url('dashboard/award-medal/delete', [$medal->medal_id])}}"
                                        class="fa fa-trash" title="Delete"></a>

                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{$medals->links()}}
                </div>
            </div>
        </div>
</section>

@endsection

@push('footer_script')
<script>
</script>
@endpush
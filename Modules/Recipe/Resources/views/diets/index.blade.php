@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Recipe Diets</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/recipe')}}">Recipe</a></li>
                    <li class="breadcrumb-item active">Diets</li>
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
                <h3>Diets</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/recipe/diet/add')}}">
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
                                <th>Image</th>
                                <th>Featured</th>
                                <!-- <th>Priority</th> -->
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($diets as $diet)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect"
                                        data-id="{{$diet->recipe_diet_id}}"></td>
                                <td>
                                    {{ $diet->name_en }}
                                </td>
                                <td>
                                    <img src="{{ $diet->attachment->base_url }}{{ $diet->attachment->attachment_url }}"
                                        width="50px">
                                </td>
                                <td>
                                    {{ $diet->featured == 1 ? 'yes' : 'No' }}
                                </td>
                                <!-- <td>
                                    {{ $diet->priority }}
                                </td> -->

                                <td>
                                    <a class="fa fa-edit"
                                        href="{{url('dashboard/recipe/diet/edit', [$diet->recipe_diet_id])}}"
                                        title="Edit"></a> | <a onclick="return confirm('Are you sure? You want to delete it.')"
                                        class="fa fa-trash"
                                        href="{{url('dashboard/recipe/diet/delete', [$diet->recipe_diet_id])}}"
                                        class="fa fa-trash" title="Delete"></a>

                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{$diets->links()}}
                </div>
            </div>
        </div>
</section>

@endsection

@push('footer_script')
<script>
</script>
@endpush
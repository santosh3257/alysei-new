@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Recipe Meals</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/recipe')}}">Recipe</a></li>
                    <li class="breadcrumb-item active">Meals</li>
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
                <h3>Meals</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/recipe/meal/add')}}">
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
                            @foreach($meals as $meal)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect"
                                        data-id="{{$meal->recipe_meal_id}}"></td>
                                <td>
                                    {{ $meal->name_en }}
                                </td>
                                <td>
                                    <img src="{{ $meal->attachment->base_url }}{{ $meal->attachment->attachment_url }}"
                                        width="50px">
                                </td>
                                <td>
                                    {{ $meal->featured == 1 ? 'yes' : 'No' }}
                                </td>
                                <!-- <td>
                                    {{ $meal->priority }}
                                </td> -->

                                <td>
                                    <a class="fa fa-edit"
                                        href="{{url('dashboard/recipe/meal/edit', [$meal->recipe_meal_id])}}"
                                        title="Edit"></a> | <!-- |
                                    <a class="fa fa-trash" title="Delete"></a> -->
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/recipe/meal/delete', [$meal->recipe_meal_id])}}" title="Delete"></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{$meals->links()}}
                </div>
            </div>
        </div>
</section>

@endsection

@push('footer_script')
<script>
</script>
@endpush
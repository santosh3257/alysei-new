@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Recipe Tools</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/recipe')}}">Recipe</a></li>
                    <li class="breadcrumb-item active">Tools</li>
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
                <h3>Tools</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/recipe/tool/add')}}">
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
                                <th>Title</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Featured</th>
                                <!-- <th>Priority</th> -->
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tools as $tool)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect"
                                        data-id="{{$tool->recipe_tool_id}}"></td>
                                <td>
                                    {{ $tool->title_en }}
                                </td>
                                <td>
                                    {{ $tool->name }}
                                </td>
                                <td>
                                    <img src="{{ $tool->attachment->base_url }}{{ $tool->attachment->attachment_url }}"
                                        width="50px">
                                </td>
                                <td>
                                    {{ $tool->featured == 1 ? 'yes' : 'No' }}
                                </td>
                                <!-- <td>
                                    {{ $tool->priority }}
                                </td> -->

                                <td>
                                    <a class="fa fa-edit"
                                        href="{{url('dashboard/recipe/tool/edit', [$tool->recipe_tool_id])}}"
                                        title="Edit"></a>  | 
                                    <a onclick="return confirm('Are you sure? You want to delete it.')"
                                        class="fa fa-trash"
                                        href="{{url('dashboard/recipe/tool/delete', [$tool->recipe_tool_id])}}"
                                        title="Delete"></a>

                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{$tools->links()}}
                </div>
            </div>
        </div>
</section>

@endsection

@push('footer_script')
<script>
</script>
@endpush
@extends('admin.layouts.app')

@section('content')
<?php
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
?>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Countries</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/users')}}">Users</a></li>
                    <li class="breadcrumb-item active">Countries</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
        <div class="card-header">
                    <form class="form-inline" method="get">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="inputPassword2" class="sr-only">Email/Name</label>
                            <input type="text" class="form-control" value="{{$keyword}}" id="keyword" placeholder="Country Name" name="keyword">
                        </div>
                      <button type="submit" class="btn btn-primary mb-2">Filter</button>
                      <button type="reset" class="btn btn-primary mb-2 reset ml-2">Reset</button>
                    </form>
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();"
                                    class="allSelect"> All </th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Country Flag</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($countries as $country)
                        <tr role="row">
                            <td><input type="checkbox" name="" class="singleSelect" data-id="{{$country->id}}"></td>
                            <td>
                                {{$country->name}}
                            </td>
                            <td>
                                {{$country->status == 1 ? 'Active' : 'Inactive'}}
                            </td>
                            <td>
                            <img src="{{ $country->flagImg->base_url }}{{ $country->flagImg->attachment_url }}" width="75px">
                            </td>
                            <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/user/country/edit', [$country->id])}}" title="Edit"></a>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
            {{$countries->links()}}
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
   function selectAll() {
        if ($('.allSelect').is(':checked')) {
            $('.singleSelect').prop('checked', true);
        } else {
            $('.singleSelect').prop('checked', false);
        }
    }
    $(".reset").click(function(){
            $("#keyword").val("");
            $("form").submit();
        });
  </script>
@endsection

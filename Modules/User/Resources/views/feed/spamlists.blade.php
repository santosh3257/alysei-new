@extends('admin.layouts.app')

@section('content')
<?php
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
?>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Activity Spams</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Feed's</li>
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
       
          <div class="card-header">
                    <form class="form-inline" method="get">
                        <div class="form-group mx-sm-3 mb-2">
                            <label for="inputPassword2" class="sr-only">Email/Name</label>
                            <input type="text" class="form-control" value="{{$keyword}}" id="keyword" placeholder="Search" name="keyword">
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
                            <!-- <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();"
                                    class="allSelect"> All </th> -->
                            
                            <th>Id</th>
                            <th>Author name</th>
                            <th>Privacy</th>
                            <th>Body</th>
                            <th>Report Count</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($feeds)
                            @foreach($feeds as $key=>$feed)
                                <tr>
                                    <!-- <td><input type="checkbox" name="" class="singleSelect" data-id="{{$feed->activity_action_id}}"></td>
                                     --><td>{{$feed->activity_action_id}}</td>
                                    <td>@if($feed->role_id == 9)
                                        {{$feed->restaurant_name}}
                                        @elseif(!empty($feed->first_name))
                                        {{$feed->first_name.' '.$feed->last_name}}
                                        @elseif(!empty($feed->company_name))
                                        {{$feed->company_name}}
                                        @else
                                        {{$feed->name}}
                                        @endif
                                    </td>
                                    <td>{{$feed->privacy}}</td>
                                    <td>{{$feed->body}}</td>
                                    <td>{{$feed->total}}</td>
                                    <td><a class="fa fa-eye" href="{{url('dashboard/feed/spam/view', [$feed->activity_action_id])}}" title="View"></a> <!-- |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/feed/delete', [$feed->activity_action_id])}}"
                                        title="Delete"></a> --></td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="clearfix">
            {{$feeds->appends($_GET)->links()}}
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

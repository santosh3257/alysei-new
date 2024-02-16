@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Notifications</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">All Notifications</li>
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
                <h3 class="card-title">All Push Notification</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/push-notification/create')}}">
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
                            <th>Title</th>
                            <!-- <th>Message</th> -->
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($cronsData)
                            @foreach($cronsData as $key=>$cron)
                            <tr role="row">
                                <!-- <td><input type="checkbox" name="" class="singleSelect" data-id="{{$cron->cron_job_id}}"></td> -->
                                <td>
                                    {{ $cron->cron_job_title }}
                                </td>
                                <!-- <td>
                                    {{ $cron->message_en }}
                                </td> -->
                                <td>
                                    @if($cron->cron_status == 0)
                                    Pending
                                    @elseif($cron->cron_status == 1)
                                    Success
                                    @elseif($cron->cron_status == 2)
                                    Success
                                    @else
                                    Faild
                                    @endif
                                </td>
                                <td>
                                    <button type="button" data-id="{{$cron->cron_job_id}}" class="fa fa-eye viewJob"
                                        title="View"></button>

                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                {{$cronsData->links()}}
            </div>

             <!-- /.Notification Status -->
             <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 4.75em;"> Sl </th> 
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="allNotification">
                        
                    </tbody>
                </table>
               


            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
$(document).on('click', '.viewJob', function(){
    var cronId = $(this).attr('data-id');
    var ajaxurl = '{{url('dashboard/push-notification/cron', 'id')}}';
    ajaxurl = ajaxurl.replace('id', cronId);
    $.ajax({
        url: ajaxurl,
        type: "GET",
        success: function(data){
            $data = $(data); // the HTML content that controller has produced
            $('#item-container').hide().html($data).fadeIn();
        }
    });
});

</script> 
@endsection

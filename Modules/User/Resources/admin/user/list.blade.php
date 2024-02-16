@extends('admin.layouts.app')

@section('content')



<div class="page-breadcrumb border-bottom">
    <div class="row">
        <div class="col-lg-3 col-md-4 col-xs-12 justify-content-start d-flex align-items-center">
            <h5 class="font-medium text-uppercase mb-0">Alysei Users</h5>
        </div>
        <div class="col-lg-9 col-md-8 col-xs-12 d-flex justify-content-start justify-content-md-end align-self-center">
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
            <!-- <button class="btn btn-danger text-white ml-3 d-none d-md-block"></button> -->
        </div>
    </div>
</div>

<div class="page-content container-fluid">
<div class="row">
    <div class="col-12">
        <div class="material-card card">
            <div class="card-body">
                <h4 class="card-title">File export</h4>
                <h6 class="card-subtitle">test </h6>
                <div class="table-responsive">
                    <div id="file_export_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                    <table id="file_export" class="table table-striped border display dataTable" role="grid" aria-describedby="file_export_info">
                        <thead>

                        <tr role="row">
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Name: activate to sort column descending" style="width: 0px;">Name</th>
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Email: activate to sort column descending" style="width: 0px;">Email</th>
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Status: activate to sort column descending" style="width: 0px;">Status</th>
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Action: activate to sort column descending" style="width: 0px;">Action</th>
                        </tr>

                        </thead>
                        <tbody>
                            @foreach($users as $user)  
                            <tr role="row">
                                <td>{{($user->name) ? ($user->name) : ($user->first_name.' '.$user->last_name)}}</td>
                                <td>{{$user->email}}</td>
                                <td>
                                    <select style="width:50%;" class="userstatus form-control"  data-status_id="{{$user->user_id}}">
                                      <option value="active" @if($user->account_enabled == 'active' )selected @endif">Active</option>
                                      <option value="inactive" @if($user->account_enabled == 'inactive' )selected @endif">InActive</option>
                                      <option value="expired" @if($user->account_enabled == 'expired' )selected @endif">Expired</option>
                                      <option value="incomplete" @if($user->account_enabled == 'incomplete' )selected @endif">Incomplete</option>
                                    </select>
                                </td>
                                <td>
                                    <a href="{{url('dashboard/users/edit', [$user->user_id])}}" class="pr-2" data-toggle="tooltip" title="" data-original-title="Edit"><i class="ti-marker-alt"></i></a>

                                    <!-- <a href="" title=""data-toggle="tooltip" data-original-title="Delete"><i class="ti-trash"></i></a> -->
                                </td>
                            </tr>
                            @endforeach
                            
                        </tbody>
                       
                    </table>
                    <div class="dataTables_info" id="file_export_info" role="status" aria-live="polite">Showing 1 to 10 of 57 entries</div>
                    <div style="float: right;">
                        {{$users->links()}}
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
                
@endsection            

@push('footer_script')
<script>
$(document).ready(function(){
   $('.userstatus').change(function(){
     let status =$(this).val();
     let id =$(this).data("status_id");
       handleStatus(id,status);   
   });
});
var dataId = [];
 function handleStatus(id,status){
     if(id != ''){
        dataId=[id];
     }
     if (confirm("Are you sure you want to change the status?") == true) {
     $.ajax({
                 url:"{{url('dashboard/user-status')}}",
                type:'post',
                data:{'id':dataId,'status':status,'_token':'{{ csrf_token() }}'},
                success: function(path){
                  location.reload();
                }
            });
    }
    else
    {
        return false;
    }
}
</script>
@endpush
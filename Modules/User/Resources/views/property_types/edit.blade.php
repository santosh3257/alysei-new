@extends('admin.layouts.app')

@section('content')
<style>
    #deleteResponse ul {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-template-rows: repeat(3, 31px);
    grid-gap: 10px;
    padding-top:20px;
}
</style>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Product Types</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/users')}}">Users</a></li>
                    <li class="breadcrumb-item active">Product Types</li>
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
                <h3 class="card-title">{{ $option }}</h3>
                <!-- <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/user/role/add')}}">
                        Add New
                    </a>
                </div> -->
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <form method="post" action="{{url('dashboard/user/property-types/update')}}">
                    {{ csrf_field() }}
                    <div class="row">
                    @foreach($options as $key => $option)
                        <div class="col-md-9 ">
                            <div class="form-group">
                                <input type="text" class="form-control" name="{{$option->user_field_option_id}}" value="{{$option->option}}" max="50" required>
                                <span class="add_option" data-key="{{$key}}"><i class="fa fa-plus"></i></span>
                                <input type="hidden" name="head{{$key}}_id" value="{{$option->user_field_option_id}}">
                            </div>
                        </div>
                        <div class="main_childs"> 
                        @foreach($option->options as $value)
                            <div class="col-md-8" style="margin-left:4rem">
                                <div class="form-group">
                                    <span>
                                        <span>
                                            <input type="text" class="form-control" name="{{$value->user_field_option_id}}" value="{{$value->option}}" max="50" required>
                                        </span>
                                        <span>
                                            <a href="javascript:void(0)" onclick="deleteOption({{$value->user_field_option_id}})"> <i class="fa fa-trash"> </i></a>        
                                        </span>({{ $value->count }})
                                    </span>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    @endforeach
                    </div>
                    <div class="card-footer">
                      <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" style="font-weight: 400;">Can't delete</h3>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="deleteResponse"></div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    function deleteOption(optionId){
            var confirm = window.confirm("Are you sure?");
            
            if(confirm){
                var url = "<?php echo url('dashboard/user/property-types/delete/'); ?>"+"/"+optionId;
                
                $.ajax({
                    method: "POST",
                    url: url,
                    dataType:'json',
                    data: {
                        "_token": "<?php echo csrf_token();?>",
                        }
                    }).done(function( msg ) {
                    if(msg.success == true){
                        //$('.sucess-status-update').html(msg.message);
                        alert(msg.message);
                        window.location.reload();
                    }else{
                        $('#deleteResponse').html(msg.message);
                        $("#myModal").modal('show');
                        //var reconfirm = window.confirm(msg.message);
                        // if(reconfirm){
                        //     var url = "<?php echo url('dashboard/user/property-types/delete/confirm'); ?>"+"/"+optionId;
                
                        //         $.ajax({
                        //             method: "POST",
                        //             url: url,
                        //             dataType:'json',
                        //             data: {
                        //                 "_token": "<?php echo csrf_token();?>",
                        //                 }
                        //             }).done(function( msg ) {
                        //             if(msg.success == true){
                        //                 //$('.sucess-status-update').html(msg.message);
                        //                 alert(msg.message);
                        //                 window.location.reload();
                        //             }else{
                        //                 alert(msg.message);
                        //             }
                        //         });
                        // }
                    }
                });
            }
        }

        
        $(function(){
            $("body").on("click",".add_option",function(){
                var optionKey = $(this).data("key");
                var optionName = "head_"+optionKey+"[]";
                $(this).parent().parent().next(".main_childs").append('<div class="col-md-8" style="margin-left:4rem"><div class="form-group"><span><span><input type="text" class="form-control" name="'+optionName+'" max="50" required></span><span><a href="javascript:void(0)" class="recently_added_option"> <i class="fa fa-trash"></i></a></span></span></div></div>');
            });

            $("body").on("click",".recently_added_option",function(){
                $(this).parent().parent().parent().remove();
            })
        });
</script>
@endsection

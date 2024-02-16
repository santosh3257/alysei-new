@extends('admin.layouts.app')

@section('content')

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
        <div class="card">
            <div class="card-header">
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/user/property-types/create')}}">
                        Add New
                    </a>
                </div>
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Selected</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($userFieldParents as $key => $userFieldParent)
                        <tr role="row">
                            <td>
                                <span class="title_{{$key}}">{{$userFieldParent->option}}</span>
                                <span class="input_{{$key}}" style="display:none">
                                    <input type="text" name="{{$userFieldParent->user_field_option_id}}" value="{{$userFieldParent->option}}">
                                </span>
                                <span class="button_{{$key}}" style="display:none">
                                    <a class="btn btn-success update" title="Edit" data-key="{{$key}}" data-optionid="{{$userFieldParent->user_field_option_id}}">Update</a>
                                </span>
                                <a class="fa fa-edit property_edit edit_{{$key}}" title="Edit" data-key="{{$key}}"></a>
                                <a class="fa fa-times property_close close_{{$key}}" style="display: none;float: none" title="Edit" data-key="{{$key}}"></a>
                            </td>
                            <td>{{$userFieldParent->count}}</td>
                            <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/user/property-types/edit', [$userFieldParent->user_field_id,$userFieldParent->user_field_option_id,base64_encode($userFieldParent->option)])}}" title="Edit"></a> |
                                        <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/user/property-types/delete/confirm', [$userFieldParent->user_field_option_id])}}" title="Delete"></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
        </div>
    </div>
</section>
<script type="text/javascript">
    $(function(){
        $("body").on("click",".property_edit",function(){
            var key = $(this).data("key");
            var title = "title_"+key;
            var input = "input_"+key;
            var button = "button_"+key;
            var close = "close_"+key;
            var edit = "edit_"+key;

            $("."+title).toggle();
            $("."+input).toggle();
            $("."+button).toggle();
            $("."+close).toggle();
            $("."+edit).toggle();
        });

        $("body").on("click",".property_close",function(){
            var key = $(this).data("key");
            var title = "title_"+key;
            var input = "input_"+key;
            var button = "button_"+key;
            var close = "close_"+key;
            var edit = "edit_"+key;

            $("."+title).toggle();
            $("."+input).toggle();
            $("."+button).toggle();
            $("."+close).toggle();
            $("."+edit).toggle();
        });

        $("body").on("click",".update",function(){
            var confirm = window.confirm("Are you sure?");
            var key = $(this).data("key");
            var input = "input_"+key;
            var optionId = $(this).data("optionid");
            var value = $("input[name='"+optionId+"']").val();
            if(confirm){
                var url = "<?php echo url('dashboard/user/property-types/update-option'); ?>";
                
                $.ajax({
                    method: "POST",
                    url: url,
                    dataType:'json',
                    data: {
                        "_token": "<?php echo csrf_token();?>",
                        "optionId" : optionId,
                        "option" : value
                        }
                    }).done(function( msg ) {
                    if(msg.success == true){
                        //$('.sucess-status-update').html(msg.message);
                        alert(msg.message);
                        window.location.reload();
                    }else{
                        alert(msg.message);
                        //$('.error-favourite-message').html(msg.message);
                    }
                });
            }
        })
    });

    // function updateOption(key){

    //         var confirm = window.confirm("Are you sure?");
    //         var input = "input_"+key;
    //         var optionId = $(this).data("optionid");
    //         var value = $("."+input).val();
    //         console.log(optionId);
    //         console.log(value);
    //         //if(confirm){
    //             var url = "<?php echo url('dashboard/user/property-types/update-option'); ?>";
                
    //             $.ajax({
    //                 method: "POST",
    //                 url: url,
    //                 dataType:'json',
    //                 data: {
    //                     "_token": "<?php echo csrf_token();?>",
    //                     "optionId" : optionId,
    //                     "option" : value
    //                     }
    //                 }).done(function( msg ) {
    //                 if(msg.success == true){
    //                     //$('.sucess-status-update').html(msg.message);
    //                     alert(msg.message);
    //                     window.location.reload();
    //                 }else{
    //                     alert(msg.message);
    //                     //$('.error-favourite-message').html(msg.message);
    //                 }
    //             });
    //         //}
    //     }
</script>
@endsection

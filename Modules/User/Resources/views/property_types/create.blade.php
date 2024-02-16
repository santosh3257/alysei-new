@extends('admin.layouts.app')

@section('content')

<style>
    .addBlock {
        max-width: 50%;
        width: 100%;
    }   
    .addBlock .col-md-12{
        padding-left: 7.5px;
        padding-right: 7.5px;
        margin-bottom: 0.75rem;
    }
    .addBlock .row{
        margin-left: -1.5px;
        margin-right: -1.5px;
    }
    .addBlock .col-md-12:first-child {
        margin-top: 0.75rem;
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
            
            <!-- /.card-header -->
            <div class="card-body">
                <form method="post" action="{{url('dashboard/user/property-types/save')}}">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Product Type Name</label>
                                <input type="text" class="form-control" name="main_property" required>
                            </div>
                        </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Conservation Type</label>
                                    <input type="text" class="form-control" name="head1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="head1_option[]" required>
                            </div>
                            <div class="col-md-6">
                                <span class="add_new_button add_conservation_option"><i class="fa fa-plus"></i></span>
                            </div>
                            <div class="addBlock conservation_block">
                                <div class="row">
                                </div>
                            </div> 

                        <div class="col-md-12"> 
                            <div class="form-group">
                                <label>Proptery Type</label>
                                <input type="text" class="form-control" name="head2" required>
                            </div>    
                        </div>
                        <div class="col-md-6">
                                <input type="text" class="form-control" name="head2_option[]" required>
                            </div>
                            <div class="col-md-6">
                                <span class="add_new_button add_property_option"><i class="fa fa-plus"></i></span>
                            </div>
                            <div class="addBlock property_block">
                                <div class="row">
                                </div>
                            </div> 
                    </div>
                    <div class="card-footer">
                      <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</section>
<script type="text/javascript">
    $(function(){
        $("body").on("click",".add_conservation_option",function(){
            var div = '<div class="col-md-12"><input type="text" class="form-control" name="head1_option[]" required><span class="delete_option"><i class="fa fa-trash"></i></div>';
            $(".conservation_block").find(".row").append(div);
        });

        $("body").on("click",".add_property_option",function(){
            var div = '<div class="col-md-12"><input type="text" class="form-control" name="head2_option[]" required><span class="delete_option"><i class="fa fa-trash"></i></div>';
            $(".property_block").find(".row").append(div);
        });

        $("body").on("click",".delete_option", function(){
            $(this).parent().remove();
        });
    })
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
                        alert(msg.message);
                        //$('.error-favourite-message').html(msg.message);
                    }
                });
            }
        }
</script>
@endsection

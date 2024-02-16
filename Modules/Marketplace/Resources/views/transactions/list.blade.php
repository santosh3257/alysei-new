@extends('admin.layouts.app')

@section('content')
<?php
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    $time = isset($_GET['time']) ? $_GET['time'] : '';
?>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>All Transactions</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Transactions</li>
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
            <form class="form-inline" method="get">
                <div class="form-group mx-sm-3 mb-2">
                    <input type="text" class="form-control" value="{{$keyword}}" id="keyword" placeholder="Search order, transaction ID" name="keyword">
                </div>
                <div class="form-group mx-sm-3 mb-2">
                    <input type="date" class="form-control" value="{{$date}}" id="date" name="date">
                </div>
                <div class="form-group mx-sm-3 mb-2">
                    <input type="time" class="form-control" value="{{$time}}" id="time" name="time" step="1">
                </div>
                <button type="submit" class="btn btn-primary mb-2">Search</button>
                <button type="reset" class="btn btn-primary mb-2 reset ml-2">Reset</button>
            </form>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>T. ID</th>
                            <th>O. ID</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>You pay</th>
                            <th>P Status</th>
                            <th>O Status</th>
                            <th>Date</th>
                            <th style="width: 220px;">Admin Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($transactions)
                            @foreach($transactions as $key=>$transaction)
                            <tr role="row">
                                
                                <td>
                                    {{ $transaction->id }}
                                </td>
                                <td>
                                    {{ $transaction->order_id }}
                                </td>
                                <td>
                                    {{ $transaction->orderInfo->num_items_sold }}
                                </td>
                                <td>
                                    {{ $transaction->orderInfo->currency }}{{ $transaction->paid_amount }}
                                </td>
                                @php
                                $amount = $transaction->paid_amount;
                                $dis_amount = ($amount*10)/100;
                                $payAmount = $amount-$dis_amount;
                                @endphp
                                <td>
                                {{ $transaction->orderInfo->currency }}{{ $payAmount  }}
                                </td>
                                <td>
                                    @if($transaction->status == 'requires_payment_method')
                                        cancelled
                                    @else
                                        {{ $transaction->status }}
                                    @endif
                                </td>
                                <td>
                                    {{ $transaction->orderInfo->status }}
                                </td>
                                <td>
                                    {{ $transaction->created_at }}
                                </td>
                                <td>
                                @if($transaction->admin_payment_made == 0)
                                    @if($transaction->orderInfo->status == 'completed')
                                        <?php 
                                            $orderAmount = ($transaction->paid_amount)*10/100;
                                            $adminPayAmount = $transaction->paid_amount - $orderAmount;
                                        ?>
                                        <button type="button" class="order btn adminTransferOrderAmount" admin-amount="{{ $adminPayAmount }}" order-amount="{{ $transaction->paid_amount }}" data-id="{{$transaction->id}}">Pay Now</button>
                                    @endif
                                @else
                                <button type="button" class="order btn" data-id="{{$transaction->id}}">Paid</button>
                                @endif
                                  <a href="/dashboard/marketplace/order/view/{{ $transaction->order_id }}" target="_blank" class="order btn">View Order</a>
                                <button type="button" data-toggle="modal" data-target="#myModal-{{ $transaction->id }}" class="order btn">View Bank</button>
                                <div id="myModal-{{ $transaction->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog modal-lg">
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h3 class="modal-title" style="font-weight: 400;">Producer Bank Details</h3>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                            @if(!empty($transaction->bankInfo) && empty($transaction->default_payment))
                                                <table width="100%" border="1">
                                                    @if($transaction->bankInfo->default_payment == 'bank')
                                                    <thead>
                                                        <th>Account Holder Name</th>
                                                        <th>Bank Name</th>
                                                        <th>Account Number</th>
                                                        <th>Swift Code</th>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{{$transaction->bankInfo->account_holder_name}}</td>
                                                            <td>{{$transaction->bankInfo->bank_name}}</td>
                                                            <td>{{$transaction->bankInfo->account_number}}</td>
                                                            <td>{{$transaction->bankInfo->swift_code}}</td>
                                                        </tr>
                                                    </tboday>
                                                    @else
                                                    <thead>
                                                        <th>Paypal Id</th>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{{$transaction->bankInfo->paypal_id}}</td>
                                                        </tr>
                                                    </tboday>
                                                    @endif
                                                </table>
                                            @else
                                            
                                                <table width="100%" border="1">
                                                    @if($transaction->default_payment == 'bank')
                                                    <thead>
                                                        <th>Account Holder Name</th>
                                                        <th>Bank Name</th>
                                                        <th>Account Number</th>
                                                        <th>Swift Code</th>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{{$transaction->account_holder_name}}</td>
                                                            <td>{{$transaction->bank_name}}</td>
                                                            <td>{{$transaction->account_number}}</td>
                                                            <td>{{$transaction->swift_code}}</td>
                                                        </tr>
                                                    </tboday>
                                                    @else
                                                    <thead>
                                                        <th>Paypal Id</th>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{{$transaction->paypal_id}}</td>
                                                        </tr>
                                                    </tboday>
                                                    @endif
                                                </table>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" data-toggle="modal" data-target="#myModal-{{ $transaction->charge_id }}" class="order btn">View Charge Id</button>
                                <div id="myModal-{{ $transaction->charge_id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog modal-lg">
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h3 class="modal-title" style="font-weight: 400;">Charge Id Details</h3>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <table width="100%" border="1">
                                                    <thead>
                                                        <th>Charge Id</th>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{{$transaction->charge_id}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                {{$transactions->links()}}
            </div>
        </div>
    </div>
</section>
<script>
$(function() {
    $(".reset").click(function(){
        $("#keyword").val("");
        $("#date").val("");
        $("#time").val("");
        $("form").submit();
    });
});
</script>
@endsection

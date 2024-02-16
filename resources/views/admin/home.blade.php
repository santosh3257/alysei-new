@extends('admin.layouts.app')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Dashboard</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active">Dashboard v1</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Small boxes (Stat box) -->
    <h4>Registered Users</h4>
    <hr>
    <div class="row">
      @if($roles)
      @foreach($roles as $key=>$role)
      <div class="col-lg-3 col-6">
        <!-- small box -->
        @if($role->totalUsers <= 20 && $role->totalUsers >= 0)
          @php $colur = "bg-danger"; @endphp
          @elseif($role->totalUsers >= 21 && $role->totalUsers <= 50) @php $colur="bg-warning" ; @endphp @elseif($role->totalUsers >= 51 && $role->totalUsers <= 70) @php $colur="bg-info" ; @endphp @else @php $colur="bg-success" ; @endphp @endif <div class="small-box {{$colur}}">
              <div class="inner">
                <h3>{{$role->totalUsers}}</h3>

                <p>{{$role->name}}</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="/dashboard/users?keyword=&role={{$role->role_id}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    @endforeach
    @endif
  </div>
  <!-- /.row -->
  <!-- Main row -->
  <div class="row">
    <!-- <div class="col-lg-3 col-6">
      <h4>Posts</h4>
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{$posts}}</h3>

          <p>Alysei Posts</p>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div> -->
    <div class="col-lg-3 col-6">
      <h4>Stores</h4>
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{$stores}}</h3>

          <p>Alysei Stores</p>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="{{ url('dashboard/marketplace/stores') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <h4>Products</h4>
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{$products}}</h3>

          <p>Alysei Products</p>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="{{ url('dashboard/marketplace/products') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <h4>Users</h4>
      <div class="chart-container" style="position: relative; height:45vh; width:38vw">
            <canvas id="Users"></canvas>
      </div>
    </div> 
    <div class="col-md-6">
      <h4>Posts</h4>
      <div class="chart-container" style="position: relative; height:45vh; width:38vw">
            <canvas id="Posts"></canvas>
      </div>
    </div> 
  </div>
  <div class="row">
    <div class="col-md-6">
      <h4>Products</h4>
      <div class="chart-container" style="position: relative; height:45vh; width:38vw">
            <canvas id="Products"></canvas>
      </div>
    </div> 
    <div class="col-md-6">
      <h4>Stores</h4>
      <div class="chart-container" style="position: relative; height:45vh; width:38vw">
            <canvas id="Stores"></canvas>
      </div>
    </div> 
  </div>
  <br />
  <br />
    
  <!-- /.row (main row) -->
  </div><!-- /.container-fluid -->
</section>
<script type="text/javascript">
    var labels = ["Jan","Feb","Mar","Apr","May","Jun","July","Aug","Sep","Oct","Nov","Dec"];
    var borderColor = ['rgba(255,99,132,1)',
                         'rgba(200,100,132,1)',
                         'rgba(26,80,138,1)',
                         'rgba(100,19,132,1)',
                         'rgba(131,99,132,1)',
                         'rgba(200,199,132,1)',
                         'rgba(25,190,132,1)',
                         'rgba(20,180,12,1)'
                         ];
    var usersData =  <?php echo $userStats;?>;

    for (var i = 0; i < usersData.length; i++) {
      usersData[i].borderColor = borderColor[i];
   }

    var data = {
      labels: labels,
      datasets: usersData
    };

    var ctx = document.getElementById('Users').getContext('2d');
    var Users = new Chart(ctx, {
        type: 'line',
        data: data
    });


    var postStats =  <?php echo $postStats;?>;
    var data = {
      labels: labels,
      datasets: [postStats]
    };

    var ctx = document.getElementById('Posts').getContext('2d');
    var Users = new Chart(ctx, {
        type: 'line',
        data: data
    });

    var storeStats =  <?php echo $storeStats;?>;
    var data = {
      labels: labels,
      datasets: [storeStats]
    };

    var ctx = document.getElementById('Stores').getContext('2d');
    var Users = new Chart(ctx, {
        type: 'line',
        data: data
    });

    var productStats =  <?php echo $productStats;?>;
    var data = {
      labels: labels,
      datasets: [productStats]
    };

    var ctx = document.getElementById('Products').getContext('2d');
    var Users = new Chart(ctx, {
        type: 'line',
        data: data
    });
</script>
@endsection

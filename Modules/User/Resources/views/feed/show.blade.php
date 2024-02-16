@extends('admin.layouts.app')
<style>
.container {
    width: 600px;
    margin: 100px auto;
}

.progressbar {
    counter-reset: step;
}

.basicInfo th,
td {
    font-size: 12px;
}

td .progressbar li {
    list-style-type: none;
    width: 25%;
    float: left;
    font-size: 12px;
    position: relative;
    text-align: center;
    text-transform: uppercase;
    color: #7d7d7d;
}

.progressbar li:before {
    width: 30px;
    height: 30px;
    content: counter(step);
    counter-increment: step;
    line-height: 30px;
    border: 2px solid #7d7d7d;
    display: block;
    text-align: center;
    margin: 0 auto 10px auto;
    border-radius: 50%;
    background-color: white;
}

.progressbar li:after {
    width: 100%;
    height: 2px;
    content: '';
    position: absolute;
    background-color: #7d7d7d;
    top: 15px;
    left: -50%;
    z-index: -1;
}

.progressbar li:first-child:after {
    content: none;
}

.progressbar li.active {
    color: green;
}

.progressbar li.active:before {
    border-color: #55b776;
}

.progressbar li.active+li:after {
    background-color: #55b776;
}
</style>
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Feed</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Feed</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">

                <div class="tab-content">

                    <div class="tab-pane active" id="tab_1">
                        <h4>Feed Info</h4>
                        <div class="card-bodys">
                            <table class="table table-bordered table-striped basicInfo">
                                <tbody>
                                    <tr>
                                        <td>Id</td>
                                        <td>
                                        {{$activityPost['activity_action_id']}}
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>Post Author</td>
                                        <td>
                                            @if($activityPost->subjectId->role_id == 9)
                                            {{$activityPost->subjectId->restaurant_name}}
                                            @elseif(!empty($activityPost->subjectId->first_name))
                                            {{$activityPost->subjectId->first_name.' '.$activityPost->subjectId->last_name}}
                                            @elseif(!empty($activityPost->subjectId->company_name))
                                            {{$activityPost->subjectId->company_name}}
                                            @else
                                            {{$activityPost->subjectId->name}}
                                            @endif
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>Privacy</td>
                                        <td>
                                        {{$activityPost['privacy']}}
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>Body</td>
                                        <td>
                                        {{$activityPost['body']}}
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>Feed Image</td>
                                        <td>
                                            @foreach($activityPost->attachments as $key=>$attch)
                                            <img src="{{$attch->attachment_link->base_url}}{{ $attch->attachment_link->attachment_url }}" width="75px">
                                                        
                                                    
                                            @endforeach
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.tab-content -->
            </div>
        </div>
    </div>
</section>
@endsection
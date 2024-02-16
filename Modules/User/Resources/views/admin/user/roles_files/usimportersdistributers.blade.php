<div class="card-bodys">
    <table class="table table-bordered table-striped basicInfo">
        <thead>
            <tr>
                <th>Email</th>
                <td>{{$user->email}}</td>
            </tr>
            <tr>
                <th>company name</th>
                <td>{{$user->company_name}}</td>
            </tr>
            <tr>
                <th>Country</th>
                <td>{{$user->country}}</td>
            </tr>
            <tr>
                <th>State</th>
                <td>{{$user->state}}</td>
            </tr>
            <tr>
                <th>City</th>
                <td>{{$user->city}}</td>
            </tr>
            <tr>
                <th>Product Type</th>
                <td>
                    @if($user->product_type)
                    @php $types = ''; @endphp
                    @foreach($user->product_type as $key=>$type)
                    @php $types .= $type.', '; @endphp
                    @endforeach
                    {{rtrim($types,', ')}}
                    @endif
                </td>
            </tr>
            <tr>
                <th>City</th>
                <td>{{$user->city}}</td>
            </tr>
        </thead>

    </table>
</div>
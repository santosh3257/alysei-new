<!DOCTYPE html>
<html>
<head>
	<title>Table</title>
</head>
<style type="text/css">
	table.tableData {
	    margin: auto;
	    max-width: 50rem;
	    width: 100%;
	}
	td.rating .fa {
	    color: #FDCC0D;
	}
	.tableTitle{
		text-align: center;
	}
	table {
	  font-family: arial, sans-serif;
	  border-collapse: collapse;
	  width: 100%;
	}
	td, th {
	  border: 1px solid #dddddd;
	  text-align: left;
	  padding: 8px;
	}
	tr:nth-child(even) {
	  background-color: #dddddd;
	}
	.rateingCount p {
	    display: flex;
	    justify-content: space-between;
	    align-items: center;
	}
	.progress{
		width: 100%;
	    background: #E8E8E8;
	    height: 0.625rem;
	    border-radius: 0.375rem;
	    max-width: 100%;
	    font-size: 0.5rem;
	    color: #fff;
	}
	.rateingCount p{
		margin: 0;
	}
	.progress-bar{
		background: #37A282;
	    height: 0.625rem;
	    border-radius: 0.375rem;
	    font-size: 0.75rem;
	    color: #fff;
	    width: 10rem;
	}
	.ratingCount {
	    display: flex;
	    align-items: center;
	}
	span.rateCount {
	    min-width: 16%;
	}
</style>
<body>
	<table class="tableData">
		<h2 class="tableTitle">Products {{$title}}</h2>
  <tr>
    <th>Name</th>
    <th>Like(Count)</th>
    <th>Rating</th>
  </tr>
  <tr>
    <td>xxxxxxx</td>
    <td>5</td>
    <td class="rating">
    	<div class="ratingCount">
			<span class="rateCount">
				<i class="fa fa-star" aria-hidden="true"></i>
				1
			</span>
			<div class="progress">
			  <span class="progress-bar" role="progressbar" aria-valuenow="90"
			  aria-valuemin="0" aria-valuemax="100">
			  </span>
			</div>
			<span>100%</span>
    	</div>
    </td>
  </tr>
  <tr>
    <td>xxxxxxx</td>
    <td>5</td>
    <td class="rating">
    	<div class="ratingCount">
			<span class="rateCount">
				<i class="fa fa-star" aria-hidden="true"></i>
				1
			</span>
			<div class="progress">
			  <span class="progress-bar" role="progressbar" aria-valuenow="90"
			  aria-valuemin="0" aria-valuemax="100">
			  </span>
			</div>
			<span>100%</span>
    	</div>
    </td>
  </tr>
  <tr>
    <td>xxxxxxx</td>
    <td>5</td>
    <td class="rating">
    	<div class="ratingCount">
			<span class="rateCount">
				<i class="fa fa-star" aria-hidden="true"></i>
				1
			</span>
			<div class="progress">
			  <span class="progress-bar" role="progressbar" aria-valuenow="90"
			  aria-valuemin="0" aria-valuemax="100">
			  </span>
			</div>
			<span>100%</span>
    	</div>
    </td>
  </tr>
</table>

</body>
</html>
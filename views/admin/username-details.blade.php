@extends('layout')

@section('content')
	<div class="row">
	  <div class="col-sm-12">
	      <section class="panel">
	          <header class="panel-heading">
	              Details of coupon username
	          </header>
	          <div class="panel-body">
		          <div class="adv-table">
		          <table  class="display table table-bordered table-striped" id="dynamic-table">
		          <thead>
		          <tr>
		              <th>Username(s)</th>
		              <th>Received by</th>
		              <th>Date</th>
		              <th>Plan</th>
		              <th>Issued by</th>
		              <th>ID proof number</th>
		              <th>ID proof file</th>
		          </tr>
		          </thead>
		          <tbody>
		          @foreach($username_details as $detail)
			          <tr>
			          	<td>{{$detail['username']}}</td>
			          	<td>{{$detail['name']}}</td>
			          	<td>{{$detail['date']}}</td>
			          	<td>{{$detail['plan_name']}}</td>
			          	<td>{{$detail['operator']}}</td>
			          	<td>{{$detail['id_proof_number']}}</td>
			          	<td><a data-toggle="lightbox" href="{{$site_url}}images/id-proofs/{{$detail['filename']}}">View Proof</a></td>
			          </tr>
		          @endforeach


		          </table>
		          </div>
		          <p class="clearfix"></p>
		          <button class="btn btn-primary print"><i class="fa fa-print"></i> Print</button>

	          </div>

	      </section>
	  </div>
	</div>

	<div class="print-username-details">
		<h4>Details of username - {{$detail['username']}}</h4>
		<table  class="table table-bordered table-striped">
	    <thead>
	    <tr>
	        <th>Received by</th>
	        <th>Date</th>
	        <th>Plan</th>
	        <th>Issued by</th>
	        <th>ID proof number</th>
	    </tr>
	    </thead>
	    <tbody>
	      @foreach($username_details as $detail)
	        <tr>
	        	<td>{{$detail['name']}}</td>
	        	<td>{{$detail['date']}}</td>
	        	<td>{{$detail['plan_name']}}</td>
	        	<td>{{$detail['operator']}}</td>
	        	<td>{{$detail['id_proof_number']}}</td>
	        </tr>
	      @endforeach
	    </tbody>
		</table>
	</div><!-- /.print-username-details -->
@stop
@extends('layouts.app')
@section('content')
<div class="card" style="max-width:620px"><h1>Add customer</h1><form method="POST" action="{{ route('customers.store') }}">@csrf<label>Name</label><input class="input" name="name" required><label>Phone</label><input class="input" name="phone"><label>External customer ID</label><input class="input" name="external_customer_id"><label>PIN</label><input class="input" name="pin">@if($errors->any())<div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif<button class="btn">Save customer</button></form></div>
@endsection

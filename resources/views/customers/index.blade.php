@extends('layouts.app')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center"><h1>Customers</h1><a class="btn" href="{{ route('customers.create') }}">Add customer</a></div>
<div class="card"><table style="width:100%;border-collapse:collapse"><tr><th style="text-align:left;padding:10px">Name</th><th style="text-align:left;padding:10px">Phone</th><th style="text-align:left;padding:10px">Status</th><th></th></tr>@forelse($customers as $customer)<tr><td style="padding:10px">{{ $customer->name }}</td><td style="padding:10px">{{ $customer->phone ?: '—' }}</td><td style="padding:10px">{{ $customer->status }}</td><td style="padding:10px"><form method="POST" action="{{ route('customers.destroy', $customer) }}">@csrf @method('DELETE')<button>Remove</button></form></td></tr>@empty<tr><td colspan="4" style="padding:20px">No customers yet.</td></tr>@endforelse</table>{{ $customers->links() }}</div>
@endsection

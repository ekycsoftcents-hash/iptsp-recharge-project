@extends('layouts.app')
@section('content')
<div class="card" style="max-width:480px;margin:40px auto"><h1>Reseller login</h1><form method="POST" action="{{ route('login') }}">@csrf<label>Email</label><input class="input" type="email" name="email" value="{{ old('email') }}" required><label>Password</label><input class="input" type="password" name="password" required>@error('email')<p class="error">{{ $message }}</p>@enderror<button class="btn">Login</button></form></div>
@endsection

@extends('mail.layout')

@section('content')
<h3>{{ trans('mail.hey') }}</h3>
<h3>{{ trans('mail.greeting') }}, {{ $user->name }}!</h3>

<p>{{ trans('mail.confirmation') }}</p>
<p><a href="{{ config('app.client_url') }}/verify/{{ $verification }}" class="button">{{ trans('ui.confirm') }}</a></p>
@endsection
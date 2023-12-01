@extends('mail.layout')

@section('content')
<h3>{{ trans('mail.hey') }} {{ $user->name }}</h3>

<p>{{ trans('mail.reset') }}</p>
<p><a href="{{ $link }}" class="button">{{ trans('ui.reset') }}</a></p>
@endsection
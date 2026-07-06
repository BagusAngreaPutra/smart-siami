@extends('layouts.app')

@section('title', $title . ' - SMART SIAMI')
@section('page_title', $title)

@section('content')
    <div class="panel">
        <h3 class="panel-title">{{ $title }}</h3>
        <p class="muted">
            Area ini sudah berada di balik autentikasi dan guard role. Implementasi fitur detail akan dibangun pada tahap berikutnya.
        </p>
    </div>
@endsection

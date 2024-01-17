@extends('layouts.app')
@section('content')
    <section class="container">
        @auth
            <h1>Benvenuto admin {{ Auth::user()->name }}</h1>
        @else
            <h1>Bentornato {{ Auth::user()->name }}</h1>
        @endauth
    </section>
@endsection

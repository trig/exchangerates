@extends('layouts.main')

@section('title', 'Page Title from child')

@section('content')
<div class='row page_content'>
    <form class="form-inline">
        <div class="form-group">
            <label class="sr-only" for="exampleInputEmail3"></label>
            <input type="email" class="form-control" id="currency_codes" placeholder="EUR, USD ...">
        </div>
        <button type="submit" class="btn btn-info">Show rates</button>
    </form>
</div>
@endsection
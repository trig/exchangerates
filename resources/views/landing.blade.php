@extends('layouts.main')

@section('title', 'Page Title from child')

@section('content')
<div class='row page_content'>
    <div class="col-md-6">
        <form>
            <label>CBRF</label>
            <select data-name="cbrf" class="js_currency_code form-control">
                @foreach($currency_codes as $name => $abbrev)
                <option value="{{ $name }}"@if('USD' == $name)selected @endif>{{ $name }} {{ $abbrev }}</option>
                @endforeach
            </select>
        </form>
    </div>
     <div class="col-md-6">
        <form>
            <label>YAHOO</label>
            <select data-name="cbrf" class="js_currency_code form-control">
                @foreach($currency_codes as $name => $abbrev)
                <option value="{{ $name }}"@if('USD' == $name)selected @endif>{{ $name }} {{ $abbrev }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>
@endsection
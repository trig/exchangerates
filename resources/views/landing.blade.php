@extends('layouts.main')

@section('title', 'Currencyexchange')

@section('content')
<div class='row page_content'>
    <div class="col-md-6">
        <form>
            <label>CBRF</label>
            <select data-provider="cbrf" class="js_currency_code form-control">
                @foreach($currency_codes as $name => $abbrev)
                <option value="{{ $name }}"@if('USD' == $name)selected @endif>{{ $name }} {{ $abbrev }}</option>
                @endforeach
            </select>
        </form>
        <div class="currency_results">
             <h1 id="cbrf_results">n/a</h1>
        </div>
    </div>
     <div class="col-md-6">
        <form>
            <label>YAHOO</label>
            <select data-provider="yhoo" class="js_currency_code form-control">
                @foreach($currency_codes as $name => $abbrev)
                <option value="{{ $name }}"@if('USD' == $name)selected @endif>{{ $name }} {{ $abbrev }}</option>
                @endforeach
            </select>
        </form>
          <div class="currency_results">
              <h1 id="yhoo_results">n/a</h1>
        </div>
    </div>
</div>
@endsection
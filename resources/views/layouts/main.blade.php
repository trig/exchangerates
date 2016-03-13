<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.ico">

    <title>@section('title', 'Exchangerates')</title>
    <link href="{{ elixir('css/app.css') }}" rel="stylesheet" type="text/css">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <div class="container">
     @include('blocks.header')

     @section('content', 'no content available')

     @include('blocks.footer')

    </div>
    <script src="{{ elixir('js/app.js') }}"></script>
  </body>
</html>



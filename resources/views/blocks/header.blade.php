<div class="header clearfix">
    <nav>
        <ul class="nav nav-pills pull-right">
            <li role="presentation"@if (request()->url() == route('landing'))class="active"@endif>
                <a href="{{ route('landing') }}">Home</a>
            </li>
            <li role="presentation"@if (request()->url() == route('about'))class="active"@endif>
                <a href="{{ route('about') }}">About</a>
            </li>
            <li role="presentation"@if (request()->url() == route('contact'))class="active"@endif>
                <a href="{{ route('contact') }}">Contact</a>
            </li>
        </ul>
    </nav>
    <h3 class="text-muted">Exchangerates</h3>
</div>
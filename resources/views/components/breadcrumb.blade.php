<div class="row mb-3">
    <div class="col-sm-12">
        @if (isset($title))
            <h4 class="mb-2">{{ $title }}</h4>
        @endif
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="background-color: transparent; padding: 0;">
                @foreach ($items ?? [] as $item)
                    <li class="breadcrumb-item {{ !isset($item['url']) ? 'active' : '' }}">
                        @if (isset($item['url']))
                            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                        @else
                            {{ $item['label'] }}
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
</div>

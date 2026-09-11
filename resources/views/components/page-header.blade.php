@props(['title' => '', 'eyebrow' => 'Halaman', 'lead' => '', 'breadcrumbs' => []])

<div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-2 gap-sm-3">
    <div class="min-width-0">
        @if ($eyebrow)
            <div class="eyebrow">{{ $eyebrow }}</div>
        @endif
        <h1 class="mb-1">{{ $title }}</h1>
        @if ($lead)
            <p class="lead mb-0 d-none d-sm-block">{{ $lead }}</p>
        @endif
    </div>
    <div>
        @if ($breadcrumbs)
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ $breadcrumbs[0]['url'] ?? '#' }}" aria-label="Beranda"><i data-lucide="home" aria-hidden="true"></i></a>
                    </li>
                    @foreach (array_slice($breadcrumbs, 1) as $crumb)
                        @if (isset($crumb['url']))
                            <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                        @else
                            <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        @if (isset($actions))
            <div class="d-flex gap-2 flex-wrap {{ $breadcrumbs ? 'mt-2' : '' }}">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
@props(['icon' => 'inbox', 'title' => 'Belum ada data', 'description' => ''])

<div class="empty-state">
    <div class="ico"><i data-lucide="{{ $icon }}" aria-hidden="true"></i></div>
    <h5 class="mb-1">{{ $title }}</h5>
    @if ($description)
        <p class="text-muted mb-0 mx-auto" style="max-width:380px">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-3">{{ $action }}</div>
    @endif
</div>

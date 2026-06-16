{{-- resources/views/settings/partials/_pg-tab-item.blade.php --}}
@php
    $m = $gwMeta[$gw->slug] ?? ['color'=>'#6c757d','icon'=>'fas fa-credit-card'];
@endphp
<a href="#"
   class="list-group-item list-group-item-action d-flex align-items-center pg-tab-link"
   data-slug="{{ $gw->slug }}">
    <i class="{{ $m['icon'] }} mr-2" style="color:{{ $m['color'] }}"></i>
    <span>{{ $gw->name }}</span>
</a>

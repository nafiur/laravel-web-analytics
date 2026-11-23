@php
    $flagCode = \Nafiur\WebAnalytics\Services\CountryFlag::code($country ?? null);
@endphp
@if($flagCode)
    <img src="https://flagcdn.com/w20/{{ strtolower($flagCode) }}.png"
         width="18" height="12" alt="{{ $flagCode }}" title="{{ $country }}"
         loading="lazy" decoding="async"
         class="inline-block rounded-sm shrink-0 align-middle"
         onerror="this.style.display='none';this.nextElementSibling?.classList.remove('hidden');">
    <span class="hidden text-sm leading-none" aria-hidden="true">{{ $emoji ?? '' }}</span>
@elseif(!empty($emoji))
    <span class="text-sm leading-none" aria-hidden="true">{{ $emoji }}</span>
@endif

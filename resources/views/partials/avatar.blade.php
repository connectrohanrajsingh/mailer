@php
    $base = trim($name ?: $email ?: '?');
    $initial = mb_strtoupper(mb_substr($base, 0, 1));
    $palette = ['ml-av-0', 'ml-av-1', 'ml-av-2', 'ml-av-3', 'ml-av-4', 'ml-av-5'];
    $colorClass = $palette[abs(crc32(mb_strtolower($email ?: $base))) % count($palette)];
@endphp
<span class="ml-avatar {{ $colorClass }} {{ ($size ?? '') === 'lg' ? 'ml-avatar-lg' : '' }}">{{ $initial }}</span>

@php
    $flashStyles = [
        'success' => 'bg-teal-100 text-teal-500',
        'error' => 'bg-red-100 text-danger',
        'warning' => 'bg-amber-100 text-amber-500',
        'info' => 'bg-blue-100 text-blue-600',
    ];
@endphp
@foreach ($flashStyles as $type => $style)
    @if (session($type))
        <div role="{{ $type === 'error' ? 'alert' : 'status' }}" data-flash class="mb-4 flex items-start justify-between gap-3 rounded-md px-4 py-3 text-sm font-bold shadow-card {{ $style }}">
            <span>{{ session($type) }}</span>
            <button type="button" data-flash-close aria-label="Tutup pesan" class="flex-none text-base leading-none">&times;</button>
        </div>
    @endif
@endforeach
@if ($errors->any() && ! $errors->hasAny(['whatsapp_number', 'password', 'name', 'title', 'category', 'severity', 'location_id', 'description', 'message', 'area', 'fish_name', 'quantity', 'note', 'image', 'share', 'role', 'agree', 'region', 'rarity', 'image_url', 'status', 'current_password']))
    <div role="alert" class="mb-4 rounded-md bg-red-100 px-4 py-3 text-sm font-bold text-danger shadow-card">
        Periksa kembali data yang Anda isi.
    </div>
@endif

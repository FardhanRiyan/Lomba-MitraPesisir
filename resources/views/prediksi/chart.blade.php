@if (! $chart)
    <div class="rounded-md bg-sky-50 p-6 text-center text-sm text-grey-600">Data grafik belum tersedia.</div>
@else
    @php
        $isPrediction = $chart['variant'] === 'prediction';
        $gradId = $isPrediction ? 'tideGradPred' : 'tideGradHist';
    @endphp
    <svg viewBox="0 0 {{ $chart['w'] }} {{ $chart['h'] }}" role="img" aria-label="{{ $chart['summary'] }}" class="h-auto w-full">
        <defs>
            <linearGradient id="{{ $gradId }}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#2f8fef" stop-opacity="{{ $isPrediction ? 0.35 : 0.18 }}"/>
                <stop offset="100%" stop-color="#2f8fef" stop-opacity="0.02"/>
            </linearGradient>
        </defs>

        @if ($isPrediction)
            @foreach ($chart['ticks'] as $t)
                <line x1="{{ round($t['x'], 1) }}" x2="{{ round($t['x'], 1) }}" y1="{{ $chart['padTop'] }}" y2="{{ $chart['baseY'] }}" stroke="#e3eaf1" stroke-width="1"/>
            @endforeach
        @endif

        <path d="{{ $chart['area'] }}" fill="url(#{{ $gradId }})"/>
        <path d="{{ $chart['line'] }}" fill="none" stroke="{{ $isPrediction ? '#1e7fe0' : '#0a2647' }}" stroke-width="{{ $isPrediction ? 2.2 : 1.6 }}"/>

        @if ($isPrediction)
            <line x1="{{ round($chart['nowX'], 1) }}" x2="{{ round($chart['nowX'], 1) }}" y1="{{ $chart['padTop'] - 10 }}" y2="{{ $chart['baseY'] }}" stroke="#d9534f" stroke-width="1.6" stroke-dasharray="4 3"/>
            <circle cx="{{ round($chart['nowX'], 1) }}" cy="{{ round($chart['nowY'], 1) }}" r="4.5" fill="#d9534f"/>
            @foreach ($chart['extremes'] as $e)
                <g>
                    <circle cx="{{ round($e['x'], 1) }}" cy="{{ round($e['y'], 1) }}" r="4.5" fill="#0a2647"/>
                    <text x="{{ round($e['x'], 1) }}" y="{{ round($e['labelY'], 1) }}" text-anchor="{{ $e['anchor'] }}" font-size="10" font-weight="700" fill="#0a2647" paint-order="stroke" stroke="#fff" stroke-width="3" stroke-linejoin="round">{{ $e['text'] }}</text>
                </g>
            @endforeach
            @foreach ($chart['ticks'] as $t)
                <text x="{{ round($t['x'], 1) }}" y="{{ $chart['h'] - 12 }}" text-anchor="{{ $t['anchor'] }}" font-size="12" fill="#93a4b8">{{ $t['label'] }}</text>
            @endforeach
        @else
            <circle cx="{{ round($chart['high']['x'], 1) }}" cy="{{ round($chart['high']['y'], 1) }}" r="4" fill="#0a2647"/>
            <circle cx="{{ round($chart['low']['x'], 1) }}" cy="{{ round($chart['low']['y'], 1) }}" r="4" fill="#0a2647"/>
            @foreach ($chart['dayTicks'] as $t)
                <text x="{{ round($t['x'], 1) }}" y="{{ $chart['h'] - 10 }}" text-anchor="middle" font-size="12" fill="#5c7089">{{ $t['label'] }}</text>
            @endforeach
        @endif
    </svg>
@endif

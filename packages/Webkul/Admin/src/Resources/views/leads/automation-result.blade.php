@php
    $automation = $lead->automation_result ?? null;
@endphp

@if ($automation)
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Automation Result</p>
                <h3 class="mt-1 text-lg font-bold dark:text-white">{{ $automation['label'] ?? 'Lead analyzed' }}</h3>
            </div>
            <span class="rounded-full px-3 py-1 text-sm font-semibold {{ ($automation['level'] ?? 'cold') === 'hot' ? 'bg-red-100 text-red-700' : (($automation['level'] ?? 'cold') === 'warm' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700') }}">
                {{ strtoupper($automation['level'] ?? 'cold') }}
            </span>
        </div>
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $automation['summary'] ?? 'This lead was analyzed automatically.' }}</p>
        @if (! empty($automation['actions']))
            <ul class="mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                @foreach ($automation['actions'] as $action)
                    <li>• {{ $action }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif

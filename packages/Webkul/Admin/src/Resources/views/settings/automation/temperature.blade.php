<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.automation.temperature.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.settings.automation.temperature.breadcrumbs.before') !!}
                <x-admin::breadcrumbs name="settings.automation.temperature" />
                {!! view_render_event('admin.settings.automation.temperature.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.settings.automation.temperature.title')
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex items-center gap-4 border-b border-gray-200 dark:border-gray-700">
            <a
                href="{{ route('admin.settings.automation.index') }}"
                class="pb-3 text-sm font-medium {{ request()->routeIs('admin.settings.automation.index') ? 'border-b-2 border-brandColor text-brandColor' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}"
            >
                @lang('admin::app.settings.automation.index.tabs.rules')
            </a>
            <a
                href="{{ route('admin.settings.automation.temperature') }}"
                class="pb-3 text-sm font-medium {{ request()->routeIs('admin.settings.automation.temperature') ? 'border-b-2 border-brandColor text-brandColor' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}"
            >
                @lang('admin::app.settings.automation.index.tabs.temperature')
            </a>
            <a
                href="{{ route('admin.settings.automation.logs') }}"
                class="pb-3 text-sm font-medium {{ request()->routeIs('admin.settings.automation.logs') ? 'border-b-2 border-brandColor text-brandColor' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}"
            >
                @lang('admin::app.settings.automation.index.tabs.logs')
            </a>
        </div>

        <!-- Temperature Configuration Panel -->
        <div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    @lang('admin::app.settings.automation.temperature.section.scoring')
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    @lang('admin::app.settings.automation.temperature.section.scoring-desc')
                </p>
            </div>

            <div class="p-6">
                <!-- Score Legend -->
                <div class="mb-6 grid grid-cols-3 gap-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-900/20">
                        <div class="flex items-center gap-2">
                            <span class="flex h-3 w-3 rounded-full bg-red-500"></span>
                            <span class="font-semibold text-red-700 dark:text-red-400">@lang('admin::app.settings.automation.temperature.hot')</span>
                        </div>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-300">@lang('admin::app.settings.automation.temperature.hot-desc')</p>
                        <p class="mt-2 text-xs text-red-500">≥ 15 points</p>
                    </div>

                    <div class="rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-900 dark:bg-orange-900/20">
                        <div class="flex items-center gap-2">
                            <span class="flex h-3 w-3 rounded-full bg-orange-500"></span>
                            <span class="font-semibold text-orange-700 dark:text-orange-400">@lang('admin::app.settings.automation.temperature.warm')</span>
                        </div>
                        <p class="mt-1 text-sm text-orange-600 dark:text-orange-300">@lang('admin::app.settings.automation.temperature.warm-desc')</p>
                        <p class="mt-2 text-xs text-orange-500">≥ 5 points</p>
                    </div>

                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
                        <div class="flex items-center gap-2">
                            <span class="flex h-3 w-3 rounded-full bg-blue-500"></span>
                            <span class="font-semibold text-blue-700 dark:text-blue-400">@lang('admin::app.settings.automation.temperature.cold')</span>
                        </div>
                        <p class="mt-1 text-sm text-blue-600 dark:text-blue-300">@lang('admin::app.settings.automation.temperature.cold-desc')</p>
                        <p class="mt-2 text-xs text-blue-500">Default</p>
                    </div>
                </div>

                <!-- Current Conditions -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        @lang('admin::app.settings.automation.temperature.conditions-title')
                    </h4>

                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('admin::app.settings.automation.temperature.col.keyword')</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('admin::app.settings.automation.temperature.col.operator')</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('admin::app.settings.automation.temperature.col.field')</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">@lang('admin::app.settings.automation.temperature.col.points')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($conditions as $condition)
                                    <tr class="{{ $condition['points'] >= 0 ? 'bg-red-50 dark:bg-red-900/10' : 'bg-blue-50 dark:bg-blue-900/10' }}">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $condition['value'] }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $condition['operator'] }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $condition['field'] }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-bold {{ $condition['points'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $condition['points'] >= 0 ? '+' : '' }}{{ $condition['points'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Actions Configuration -->
                <div class="mt-8 space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        @lang('admin::app.settings.automation.temperature.actions-title')
                    </h4>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h5 class="font-medium text-red-600">🔥 @lang('admin::app.settings.automation.temperature.hot')</h5>
                            <ul class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <li>• @lang('admin::app.settings.automation.temperature.action.notify')</li>
                                <li>• @lang('admin::app.settings.automation.temperature.action.create_task')</li>
                                <li>• @lang('admin::app.settings.automation.temperature.action.add_tag')</li>
                            </ul>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h5 class="font-medium text-orange-600">🌡️ @lang('admin::app.settings.automation.temperature.warm')</h5>
                            <ul class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <li>• @lang('admin::app.settings.automation.temperature.action.schedule_followup')</li>
                                <li>• @lang('admin::app.settings.automation.temperature.action.add_tag')</li>
                            </ul>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h5 class="font-medium text-blue-600">❄️ @lang('admin::app.settings.automation.temperature.cold')</h5>
                            <ul class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <li>• @lang('admin::app.settings.automation.temperature.action.nurture')</li>
                                <li>• @lang('admin::app.settings.automation.temperature.action.add_tag')</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>
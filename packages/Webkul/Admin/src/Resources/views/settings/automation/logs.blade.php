<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.automation.logs.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.settings.automation.logs.breadcrumbs.before') !!}
                <x-admin::breadcrumbs name="settings.automation.logs" />
                {!! view_render_event('admin.settings.automation.logs.breadcrumbs.after') !!}

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.settings.automation.logs.title')
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

        <!-- Logs DataGrid -->
        <x-admin::datagrid :src="route('admin.settings.automation.logs.index')">
            <x-admin::shimmer.datagrid />
        </x-admin::datagrid>
    </div>
</x-admin::layouts>
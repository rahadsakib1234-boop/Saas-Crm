<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.automation.outcomes.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="settings.automation.outcomes" />
                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.settings.automation.outcomes.title')
                </div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm text-gray-500">@lang('admin::app.settings.automation.outcomes.cards.qualified.title')</div>
                <div class="mt-2 text-3xl font-bold text-green-600">HOT</div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">@lang('admin::app.settings.automation.outcomes.cards.qualified.body')</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm text-gray-500">@lang('admin::app.settings.automation.outcomes.cards.followup.title')</div>
                <div class="mt-2 text-3xl font-bold text-orange-600">WARM</div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">@lang('admin::app.settings.automation.outcomes.cards.followup.body')</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-sm text-gray-500">@lang('admin::app.settings.automation.outcomes.cards.nurture.title')</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">COLD</div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">@lang('admin::app.settings.automation.outcomes.cards.nurture.body')</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold dark:text-white">@lang('admin::app.settings.automation.outcomes.story.title')</h3>
            <div class="mt-4 grid gap-3 md:grid-cols-4">
                <div class="rounded-md bg-gray-50 p-4 dark:bg-gray-800">Lead arrives</div>
                <div class="rounded-md bg-gray-50 p-4 dark:bg-gray-800">Score is calculated</div>
                <div class="rounded-md bg-gray-50 p-4 dark:bg-gray-800">Rule/action chosen</div>
                <div class="rounded-md bg-gray-50 p-4 dark:bg-gray-800">Lead outcome is visible to user</div>
            </div>
        </div>
    </div>
</x-admin::layouts>
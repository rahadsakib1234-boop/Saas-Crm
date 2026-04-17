<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.automation.edit.title')
    </x-slot>

    <x-admin::form :action="route('admin.settings.automation.update', $rule->id)">
        @method('PUT')
        <div class="flex flex-col gap-4">
            <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div>
                    <x-admin::breadcrumbs name="settings.automation.edit" />
                    <div class="text-xl font-bold dark:text-white">@lang('admin::app.settings.automation.edit.title')</div>
                </div>
                <button type="submit" class="primary-button">@lang('admin::app.settings.automation.edit.save-btn')</button>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">@lang('admin::app.settings.automation.create.name')</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="text" name="name" :value="$rule->name" rules="required" />
                    </x-admin::form.control-group>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>@lang('admin::app.settings.automation.create.trigger')</x-admin::form.control-group.label>
                        <x-admin::form.control-group.control type="select" name="trigger_event" :value="$rule->trigger_event" rules="required">
                            <option value="created">Lead created</option>
                            <option value="updated">Lead updated</option>
                        </x-admin::form.control-group.control>
                    </x-admin::form.control-group>
                </div>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.automation.create.title')
    </x-slot>

    <x-admin::form :action="route('admin.settings.automation.store')">
        <div class="flex flex-col gap-4">
            <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div>
                    <x-admin::breadcrumbs name="settings.automation.create" />
                    <div class="text-xl font-bold dark:text-white">@lang('admin::app.settings.automation.create.title')</div>
                </div>
                <button type="submit" class="primary-button">@lang('admin::app.settings.automation.create.save-btn')</button>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-lg font-semibold dark:text-white">@lang('admin::app.settings.automation.create.basic-details')</h3>
                    <div class="mt-4 space-y-4">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">@lang('admin::app.settings.automation.create.name')</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="text" name="name" rules="required" />
                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>@lang('admin::app.settings.automation.create.description')</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="textarea" name="description" rows="4" />
                            <x-admin::form.control-group.error control-name="description" />
                        </x-admin::form.control-group>

                        <div class="grid grid-cols-2 gap-4">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>@lang('admin::app.settings.automation.create.trigger')</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="select" name="trigger_event" rules="required">
                                    <option value="created">Lead created</option>
                                    <option value="updated">Lead updated</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>@lang('admin::app.settings.automation.create.logic')</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="select" name="condition_logic" rules="required">
                                    <option value="and">All conditions</option>
                                    <option value="or">Any condition</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-lg font-semibold dark:text-white">@lang('admin::app.settings.automation.create.outcome')</h3>
                    <p class="mt-2 text-sm text-gray-500">@lang('admin::app.settings.automation.create.outcome-info')</p>

                    <div class="mt-4 space-y-3 rounded-md border border-dashed border-gray-300 p-4 dark:border-gray-700">
                        <div class="text-sm font-medium">If lead matches:</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">urgent / asap / buy now</div>
                        <div class="text-sm font-medium">Then:</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Notify agent, create follow-up task, tag HOT</div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold dark:text-white">Conditions</h3>
                <div class="mt-4 space-y-3">
                    <div class="grid gap-3 md:grid-cols-3">
                        <input name="conditions[0][field]" class="rounded-md border px-3 py-2 dark:bg-gray-900" placeholder="field" value="description" />
                        <select name="conditions[0][operator]" class="rounded-md border px-3 py-2 dark:bg-gray-900">
                            <option value="contains">contains</option>
                            <option value="not_contains">not contains</option>
                            <option value="equals">equals</option>
                        </select>
                        <input name="conditions[0][value]" class="rounded-md border px-3 py-2 dark:bg-gray-900" placeholder="urgent" />
                    </div>
                    <p class="text-sm text-gray-500">Add more conditions later from the edit screen; this keeps the first version simple and usable.</p>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold dark:text-white">Actions</h3>
                <div class="mt-4 space-y-3">
                    <div class="grid gap-3 md:grid-cols-2">
                        <select name="actions[0][action]" class="rounded-md border px-3 py-2 dark:bg-gray-900">
                            <option value="notify">Notify agent</option>
                            <option value="create_task">Create follow-up task</option>
                            <option value="add_tag">Add tag</option>
                            <option value="move_to_stage">Move to stage</option>
                        </select>
                        <input name="actions[0][value]" class="rounded-md border px-3 py-2 dark:bg-gray-900" placeholder="Value / tag / task title" />
                    </div>
                    <p class="text-sm text-gray-500">This is the product layer buyers care about: a lead automatically becomes HOT, gets routed, and gets a follow-up.</p>
                </div>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
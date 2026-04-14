<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-mansa-black leading-tight">{{ __('tailor.new_client_measurement_title') }}</h2>
            <a href="{{ route('tailor.workspace') }}" class="text-sm text-gold-700 hover:text-gold-900 font-medium">{{ __('tailor.back_workspace') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-sm text-stone-600">{{ __('tailor.new_client_measurement_intro') }}</p>

            @if ($templates->isEmpty())
                <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950">
                    {{ __('clients.ask_owner_templates') }}
                </div>
            @else
                @php
                    $templatesJson = $templates->map(fn ($t) => [
                        'id' => (string) $t->id,
                        'name' => $t->name,
                        'fields' => $t->normalizedFields(),
                    ])->values();
                @endphp

                <div
                    class="bg-white shadow-sm border border-gold-100 sm:rounded-lg p-6"
                    x-data="{
                        templateId: @js((string) old('measurement_form_template_id', (string) ($templates->first()?->id ?? ''))),
                        templates: @js($templatesJson),
                        oldFieldValues: @json(old('field_values', [])),
                        get currentFields() {
                            const t = this.templates.find(x => String(x.id) === String(this.templateId));
                            return t ? t.fields : [];
                        },
                        pickTemplate(id) {
                            this.templateId = String(id);
                            this.$nextTick(() => this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' }));
                        }
                    }"
                >
                    <form method="POST" action="{{ route('tailor.clients.measurements.store') }}" class="space-y-8">
                        @csrf

                        <div class="space-y-4">
                            <h3 class="font-semibold text-mansa-black text-sm border-b border-gold-100 pb-2">{{ __('tailor.section_client') }}</h3>
                            <div>
                                <x-input-label for="name" :value="__('clients.name')" />
                                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="phone" :value="__('clients.phone')" />
                                    <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone')" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="email" :value="__('clients.email')" />
                                    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="notes" :value="__('clients.notes')" />
                                <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="rounded-lg border border-dashed border-gold-200 p-4 bg-gold-50/30 space-y-4">
                            <h3 class="font-semibold text-mansa-black text-sm">{{ __('tailor.section_model_measurements') }}</h3>
                            <p class="text-xs text-gray-600">{{ __('clients.new_measurement_help') }}</p>

                            <div class="flex flex-wrap gap-2">
                                @foreach ($templates as $tpl)
                                    <button
                                        type="button"
                                        @click="pickTemplate('{{ $tpl->id }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm transition"
                                        x-bind:class="String(templateId) === '{{ (string) $tpl->id }}'
                                            ? 'border-gold-600 bg-gold-200/60 text-mansa-black font-medium shadow-sm'
                                            : 'border-gray-300 bg-white text-gray-800 hover:border-gold-400'"
                                    >
                                        <span>{{ $tpl->name }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div class="sm:col-span-2">
                                    <x-input-label for="measurement_form_template_id" :value="__('tailor.model_form_label')" />
                                    <select id="measurement_form_template_id" name="measurement_form_template_id" x-model="templateId" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                                        @foreach ($templates as $tpl)
                                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('tailor.template_select_hint') }}</p>
                                    <x-input-error :messages="$errors->get('measurement_form_template_id')" class="mt-1" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="description" :value="__('tailor.measurement_description_label')" />
                                    <x-text-input id="description" name="description" class="block mt-1 w-full" :value="old('description')" required placeholder="{{ __('tailor.measurement_description_placeholder') }}" />
                                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <template x-for="f in currentFields" :key="f.key">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">
                                            <span x-text="f.label"></span>
                                            <span x-show="f.unit" class="text-gray-500" x-text="' (' + f.unit + ')'"></span>
                                        </label>
                                        <input
                                            :type="f.type === 'number' ? 'number' : 'text'"
                                            :name="'field_values[' + f.key + ']'"
                                            x-bind:step="f.type === 'number' ? '0.01' : false"
                                            x-bind:min="f.type === 'number' ? '0' : false"
                                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm"
                                            :value="oldFieldValues && oldFieldValues[f.key] !== undefined && oldFieldValues[f.key] !== null ? oldFieldValues[f.key] : ''"
                                        />
                                    </div>
                                </template>
                            </div>

                            <div>
                                <x-input-label for="measurement_notes" :value="__('clients.notes')" />
                                <textarea id="measurement_notes" name="measurement_notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('measurement_notes') }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('tailor.workspace') }}" class="text-sm text-gray-600 hover:text-gray-900 self-center">{{ __('clients.cancel') }}</a>
                            <x-primary-button>{{ __('tailor.submit_client_measurement') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

@pushonce('head:filepond')
    <link rel="stylesheet" href="https://unpkg.com/filepond/dist/filepond.css">
@endpushonce

@pushonce('js:livewire-sortable')
    <script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v0.x.x/dist/livewire-sortable.js"></script>
@endpushonce

@pushonce('js:filepond')
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
@endpushonce

<x-filament::field-group
    :errorKey="$field->errorKey"
    :for="$field->id"
    :help-message="__($field->helpMessage)"
    :hint="__($field->hint)"
    :label="__($field->label)"
    :required="$field->required"
>
    <div class="p-4 rounded border @error($field->errorKey) border-red-600 motion-safe:animate-shake @else border-gray-200 @enderror space-y-6">
        <div
            x-data
            x-init="
                FilePond.setOptions({
                    @isset($field->extraAttributes['placeholder'])
                labelIdle: '{{ $field->extraAttributes['placeholder'] }}',
                    @endisset
                allowMultiple: {{ isset($field->extraAttributes['multiple']) ? 'true' : 'false' }},
                    server: {
                        process:(fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                            @this.upload('{{ $field->model }}', file, load, error, progress)
                        },
                        revert: (filename, load, error) => {
                            console.log(filename, load)
                            @this.removeUpload('{{ $field->model }}', filename, load)
                        },
                    },
                });
                FilePond.create($refs.input);
            "
            wire:ignore
        >
            <input
                id="{{ $field->id }}"
                type="file"
                x-ref="input"
            />
        </div>

        @if ($field->value)
            <ol
                class="grid grid-cols-1 xl:grid-cols-2 gap-2 xl:gap-4"
                @if ($field->sortMethod) wire:sortable="{{ $field->sortMethod }}" @endif
            >
                @foreach ($field->value as $file)
                    <li
                        @if ($field->sortMethod)
                            wire:key="file-{{ $file }}"
                            wire:sortable.item="{{ $file }}"
                        @endif
                    >
                        <div class="col-span-1 p-2 bg-white shadow-sm rounded border border-gray-300 flex items-center space-x-2 @if ($field->sortMethod) cursor-move @endif">
                            @if (\Filament\is_image($file))
                                <x-filament::modal close-button class="flex-shrink-0 flex">
                                    <x-filament-image :src="$file" alt="{{ $file }}"
                                        :manipulations="[ 'w' => 48, 'h' => 48, 'fit' => 'crop' ]"
                                        width="48px" height="48px" loading="lazy"
                                        class="w-12 h-12 rounded"
                                    />

                                    <x-slot name="content">
                                        <img src="{{ FilamentManager::storage()->url($file) }}" alt="{{ $file }}" />
                                    </x-slot>
                                </x-filament::modal>
                            @endif

                            <div class="flex-grow overflow-scroll flex flex-col text-xs leading-tight">
                                <a
                                    href="{{ FilamentManager::storage()->url($file) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="link"
                                >
                                    {{ $file }}
                                </a>

                                <dl class="font-mono text-gray-500">
                                    <dt class="sr-only">{{ __('filament::file.mimeType') }}</dt>

                                    <dd>{{ FilamentManager::storage()->getMimeType($file) }}</dd>

                                    <dt class="sr-only">{{ __('filament::file.fileSize') }}</dt>

                                    <dd>{{ \Filament\format_bytes(FilamentManager::storage()->size($file)) }}</dd>
                                </dl>
                            </div>

                            @if ($field->deleteMethod)
                                <button
                                    type="button"
                                    wire:click="{{ $field->deleteMethod }}('{{ $file }}')"
                                    class="flex-shrink-0 text-gray-500 hover:text-red-600 transition-colors duration-200 flex"
                                >
                                    <x-heroicon-o-x class="w-4 h-4" />

                                    <span class="sr-only">{{ __('filament::file.delete') }}</span>
                                </button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</x-filament::field-group>
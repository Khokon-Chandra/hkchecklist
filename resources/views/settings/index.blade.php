<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Application Settings
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Customize your application logo, site name, and theme color.
                </p>
            </div>
        </div>
    </x-slot>

    {{-- Success Message --}}
    <x-flash.ok :message="session('success')" />

    <form x-data="settingsForm()" method="post" action="{{ route('settings.update') }}"
        enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Logo Upload Section --}}
        <x-card>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Application Logo
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Upload a custom logo for your application. Recommended size: 200x200px. Max file size: 5MB.
                </p>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                {{-- Logo Preview/Upload Area --}}
                <div class="lg:w-1/2">
                    <div
                        class="border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center text-center cursor-pointer transition-all duration-200 hover:border-opacity-70"
                        :class="dragOver ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-900/20' :
                            'border-gray-300 dark:border-gray-600 bg-gray-50/40 dark:bg-gray-800/40'"
                        @click="$refs.logoFile.click()" @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop($event)">
                        <template x-if="!previewUrl && !currentLogo">
                            <div class="text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-20 w-20 mb-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-3 text-sm font-medium">Drag &amp; drop or click to upload</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    PNG, JPG, WebP, SVG — up to 5MB
                                </p>
                            </div>
                        </template>

                        <template x-if="previewUrl || currentLogo">
                            <div class="w-full">
                                <img :src="previewUrl || currentLogo" alt="Logo Preview"
                                    class="rounded-lg object-contain h-48 w-48 mx-auto shadow-lg border-4 border-white dark:border-gray-700" />
                                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                                    Click or drop a new file to replace the logo.
                                </p>
                            </div>
                        </template>

                        <input type="file" name="application_logo" x-ref="logoFile" class="hidden"
                            @change="preview($event)" accept="image/*" />
                    </div>

                    @error('application_logo')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Current Logo Info --}}
                <div class="lg:w-1/2 flex flex-col justify-center">
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            Current Logo
                        </h4>
                        @if ($settings['application_logo_path'])
                            <div class="mb-4">
                                <img src="{{ asset('storage/' . $settings['application_logo_path']) }}"
                                    alt="Current Logo" class="h-24 w-24 object-contain rounded-lg shadow-md" />
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Your logo is currently being used throughout the application.
                            </p>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                No custom logo uploaded. The default logo is being used.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Logo Alignment Option --}}
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <x-form.label value="Logo Alignment" />
                <p class="mt-1 mb-3 text-sm text-gray-500 dark:text-gray-400">
                    Choose how the logo should be aligned in the sidebar header.
                </p>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="radio" name="logo_alignment" value="left"
                            {{ old('logo_alignment', $settings['logo_alignment'] ?? 'center') === 'left' ? 'checked' : '' }}
                            class="mr-2 text-indigo-600 focus:ring-indigo-500" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">Left</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="logo_alignment" value="center"
                            {{ old('logo_alignment', $settings['logo_alignment'] ?? 'center') === 'center' ? 'checked' : '' }}
                            class="mr-2 text-indigo-600 focus:ring-indigo-500" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">Center</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="logo_alignment" value="right"
                            {{ old('logo_alignment', $settings['logo_alignment'] ?? 'center') === 'right' ? 'checked' : '' }}
                            class="mr-2 text-indigo-600 focus:ring-indigo-500" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">Right</span>
                    </label>
                </div>
                @error('logo_alignment')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        {{-- Favicon Upload Section --}}
        <x-card>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Favicon
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Upload a custom favicon for your application. Recommended size: 32x32px or 16x16px. Max file size: 1MB. Supported formats: ICO, PNG, SVG, JPG.
                </p>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                {{-- Favicon Preview/Upload Area --}}
                <div class="lg:w-1/2">
                    <div
                        class="border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center text-center cursor-pointer transition-all duration-200 hover:border-opacity-70"
                        :class="faviconDragOver ? 'border-indigo-500 bg-indigo-50/40 dark:bg-indigo-900/20' :
                            'border-gray-300 dark:border-gray-600 bg-gray-50/40 dark:bg-gray-800/40'"
                        @click="$refs.faviconFile.click()" @dragover.prevent="faviconDragOver = true"
                        @dragleave.prevent="faviconDragOver = false" @drop.prevent="handleFaviconDrop($event)">
                        <template x-if="!faviconPreviewUrl && !currentFavicon">
                            <div class="text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 mb-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-3 text-sm font-medium">Drag &amp; drop or click to upload</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    ICO, PNG, SVG, JPG — up to 1MB
                                </p>
                            </div>
                        </template>

                        <template x-if="faviconPreviewUrl || currentFavicon">
                            <div class="w-full">
                                <img :src="faviconPreviewUrl || currentFavicon" alt="Favicon Preview"
                                    class="rounded-lg object-contain h-16 w-16 mx-auto shadow-lg border-4 border-white dark:border-gray-700" />
                                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                                    Click or drop a new file to replace the favicon.
                                </p>
                            </div>
                        </template>

                        <input type="file" name="favicon" x-ref="faviconFile" class="hidden"
                            @change="previewFavicon($event)" accept=".ico,.png,.svg,.jpg,.jpeg,image/x-icon,image/png,image/svg+xml,image/jpeg" />
                    </div>

                    @error('favicon')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Current Favicon Info --}}
                <div class="lg:w-1/2 flex flex-col justify-center">
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            Current Favicon
                        </h4>
                        @if ($settings['favicon_path'])
                            <div class="mb-4 flex items-center gap-4">
                                <img src="{{ asset('storage/' . $settings['favicon_path']) }}"
                                    alt="Current Favicon" class="h-16 w-16 object-contain rounded-lg shadow-md" />
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Your favicon is currently being used in browser tabs.
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        Format: {{ pathinfo($settings['favicon_path'], PATHINFO_EXTENSION) }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                No custom favicon uploaded. The default favicon is being used.
                            </p>
                        @endif

                        {{-- Browser Preview --}}
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs font-medium text-gray-900 dark:text-gray-100 mb-2">Browser Preview</p>
                            <div class="flex items-center gap-2 p-2 bg-white dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                @if ($settings['favicon_path'])
                                    <img src="{{ asset('storage/' . $settings['favicon_path']) }}"
                                        alt="Favicon" class="h-4 w-4" />
                                @else
                                    <div class="h-4 w-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
                                @endif
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ $siteName ?? config('app.name', 'HK Checklist') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Site Name Section --}}
        <x-card>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Site Name
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Change the name of your application as it appears throughout the system.
                </p>
            </div>

            <div class="max-w-md">
                <x-form.label value="Application Name" />
                <x-form.input name="site_name" type="text" class="w-full" required
                    :value="old('site_name', $settings['site_name'])" placeholder="Enter site name"
                    autofocus />
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    This name will appear in page titles, headers, and other UI elements.
                </p>
                @error('site_name')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        {{-- Theme Color Section --}}
        <x-card>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Theme Color
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Choose a primary color theme for your application. This color will be used for buttons, links, and
                    other interactive elements.
                </p>
            </div>

            <div class="space-y-6">
                {{-- Color Presets --}}
                <div>
                    <x-form.label value="Choose a Color" />
                    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3 mt-3">
                        @php
                            $presetColors = [
                                '#842eb8' => 'Purple',
                                '#6366f1' => 'Indigo',
                                '#3b82f6' => 'Blue',
                                '#10b981' => 'Green',
                                '#f59e0b' => 'Amber',
                                '#ef4444' => 'Red',
                                '#ec4899' => 'Pink',
                                '#8b5cf6' => 'Violet',
                                '#06b6d4' => 'Cyan',
                                '#14b8a6' => 'Teal',
                            ];
                        @endphp
                        @foreach ($presetColors as $color => $name)
                            <label
                                class="relative cursor-pointer group flex flex-col items-center justify-center p-3 rounded-lg border-2 transition-all hover:scale-105 hover:shadow-md"
                                :class="selectedColor === '{{ $color }}' || (!selectedColor && '{{ $settings['theme_color'] }}' === '{{ $color }}')
                                    ? 'border-gray-900 dark:border-gray-100 shadow-lg'
                                    : 'border-gray-200 dark:border-gray-700'">
                                <input type="radio" name="theme_color" value="{{ $color }}" x-model="selectedColor"
                                    class="sr-only"
                                    @change="updateCustomColor('{{ $color }}')"
                                    @checked($settings['theme_color'] === $color) />
                                <div class="w-10 h-10 rounded-full mb-2 shadow-sm"
                                    style="background-color: {{ $color }};"></div>
                                <span class="text-xs text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-100"
                                    x-show="selectedColor === '{{ $color }}' || (!selectedColor && '{{ $settings['theme_color'] }}' === '{{ $color }}')">
                                    ✓
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Custom Color Picker --}}
                <div>
                    <x-form.label value="Or Choose a Custom Color" />
                    <div class="flex items-center gap-4 mt-3">
                        <div class="flex-1">
                            <input type="color" x-model="customColor" @input="updateCustomColor(customColor)"
                                class="w-full h-12 rounded-lg cursor-pointer border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800"
                                value="{{ $settings['theme_color'] }}" />
                        </div>
                        <div class="flex-1">
                            <x-form.input type="text" x-model="customColor" @input="updateCustomColor(customColor)"
                                pattern="^#[0-9A-Fa-f]{6}$" placeholder="#842eb8"
                                class="w-full font-mono" />
                        </div>
                        <input type="hidden" name="theme_color" x-model="selectedColor" />
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Enter a hex color code (e.g., #842eb8) or use the color picker above.
                    </p>
                    @error('theme_color')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Color Preview --}}
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Preview
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        <button type="button"
                            class="px-4 py-2 rounded-lg text-white font-medium transition-colors"
                            :style="`background-color: ${selectedColor || '{{ $settings['theme_color'] }}'}`">
                            Primary Button
                        </button>
                        <a href="#" type="button"
                            class="px-4 py-2 rounded-lg font-medium transition-colors border-2"
                            :style="`color: ${selectedColor || '{{ $settings['theme_color'] }}'}; border-color: ${selectedColor || '{{ $settings['theme_color'] }}'}`">
                            Secondary Button
                        </a>
                        <div class="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                            <span class="font-medium"
                                :style="`color: ${selectedColor || '{{ $settings['theme_color'] }}'}`">
                                Colored Text
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Button Variants Section --}}
        <x-card>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Button Variant Colors
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Customize the colors for different button variants used throughout the application.
                </p>
            </div>

            <div class="space-y-6">
                @php
                    $buttonVariants = [
                        'primary' => ['label' => 'Primary', 'default' => '#842eb8', 'description' => 'Main action buttons'],
                        'success' => ['label' => 'Success', 'default' => '#10b981', 'description' => 'Success/confirmation actions'],
                        'danger' => ['label' => 'Danger', 'default' => '#ef4444', 'description' => 'Delete/destructive actions'],
                        'warning' => ['label' => 'Warning', 'default' => '#f59e0b', 'description' => 'Warning/caution actions'],
                        'info' => ['label' => 'Info', 'default' => '#06b6d4', 'description' => 'Informational actions'],
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($buttonVariants as $key => $variant)
                        @php
                            $settingKey = 'button_' . $key . '_color';
                            $inputId = $settingKey . '_input';
                            $currentValue = $settings[$settingKey] ?? $variant['default'];
                            $oldValue = old($settingKey, $currentValue);
                        @endphp
                        <div class="space-y-3">
                            <div>
                                <x-form.label :value="$variant['label'] . ' Button'" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $variant['description'] }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <input type="color"
                                        name="{{ $settingKey }}"
                                        value="{{ $oldValue }}"
                                        class="w-full h-10 rounded-lg cursor-pointer border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800"
                                        data-input-id="{{ $inputId }}"
                                        onchange="document.getElementById(this.dataset.inputId).value = this.value" />
                                </div>
                                <div class="flex-1">
                                    <x-form.input
                                        type="text"
                                        id="{{ $inputId }}"
                                        name="{{ $settingKey }}"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        :value="$oldValue"
                                        placeholder="{{ $variant['default'] }}"
                                        class="w-full font-mono"
                                        data-color-input="{{ $settingKey }}"
                                        onchange="document.querySelector('input[type=color][name=' + this.dataset.colorInput + ']').value = this.value" />
                                </div>
                            </div>
                            {{-- Preview --}}
                            <div class="mt-2">
                                <button type="button"
                                    class="px-4 py-2 rounded-md text-white font-medium text-sm transition-colors"
                                    style="background-color: {{ $oldValue }};"
                                    onmouseover="this.style.opacity='0.9'"
                                    onmouseout="this.style.opacity='1'">
                                    {{ $variant['label'] }} Button
                                </button>
                            </div>
                            @error($settingKey)
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>

                {{-- All Variants Preview --}}
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 mt-6">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        All Button Variants Preview
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($buttonVariants as $key => $variant)
                            @php
                                $previewKey = 'button_' . $key . '_color';
                                $previewValue = old($previewKey, $settings[$previewKey] ?? $variant['default']);
                            @endphp
                            <button type="button"
                                class="px-4 py-2 rounded-md text-white font-medium transition-colors"
                                style="background-color: {{ $previewValue }};"
                                onmouseover="this.style.opacity='0.9'"
                                onmouseout="this.style.opacity='1'">
                                {{ $variant['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Submit Buttons --}}
        <div class="flex flex-wrap justify-end gap-3">
            <x-button type="submit" class="bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7" />
                </svg>
                Save Settings
            </x-button>
            <x-button variant="secondary" href="{{ route('dashboard') }}">
                Cancel
            </x-button>
        </div>
    </form>

    {{-- Alpine.js helpers --}}
    <script>
        function settingsForm() {
            return {
                previewUrl: null,
                dragOver: false,
                faviconPreviewUrl: null,
                faviconDragOver: false,
                selectedColor: '{{ $settings['theme_color'] }}',
                customColor: '{{ $settings['theme_color'] }}',
                currentLogo: @json($settings['application_logo_path'] ? asset('storage/' . $settings['application_logo_path']) : null),
                currentFavicon: @json($settings['favicon_path'] ? asset('storage/' . $settings['favicon_path']) : null),

                preview(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.previewUrl = URL.createObjectURL(file);
                },

                handleDrop(evt) {
                    this.dragOver = false;
                    const file = evt.dataTransfer.files?.[0];
                    if (!file) return;

                    this.$refs.logoFile.files = evt.dataTransfer.files;
                    this.preview({
                        target: {
                            files: this.$refs.logoFile.files
                        }
                    });
                },

                previewFavicon(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.faviconPreviewUrl = URL.createObjectURL(file);
                },

                handleFaviconDrop(evt) {
                    this.faviconDragOver = false;
                    const file = evt.dataTransfer.files?.[0];
                    if (!file) return;

                    this.$refs.faviconFile.files = evt.dataTransfer.files;
                    this.previewFavicon({
                        target: {
                            files: this.$refs.faviconFile.files
                        }
                    });
                },

                updateCustomColor(color) {
                    this.selectedColor = color;
                    this.customColor = color;
                }
            }
        }
    </script>
</x-app-layout>


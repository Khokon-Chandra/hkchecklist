@props([
    'disabled' => false,
])

@php
    use App\Models\Setting;

    // Get theme color first, then button_primary_color as override
    $themeColor = Setting::get('theme_color', '#842eb8');
    $buttonPrimaryColor = Setting::get('button_primary_color');
    // Use button_primary_color if it exists, otherwise use theme_color
    $primaryColor = $buttonPrimaryColor ?: $themeColor;

    // Generate unique ID for this checkbox instance
    $checkboxId = $attributes->get('id', 'checkbox-' . uniqid());
@endphp

<input
    type="checkbox"
    data-checkbox-theme="{{ $primaryColor }}"
    style="accent-color: {{ $primaryColor }}; --checkbox-theme-color: {{ $primaryColor }};"
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge([
        'id' => $checkboxId,
        'class' => 'border-gray-300 rounded focus:ring focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-dark-eval-1 dark:focus:ring-offset-dark-eval-1',
    ])->except(['style']) !!}
>

@once
    <style>
        [data-checkbox-theme]:checked {
            background-color: var(--checkbox-theme-color);
            border-color: var(--checkbox-theme-color);
        }

        [data-checkbox-theme]:focus {
            border-color: var(--checkbox-theme-color);
            --tw-ring-color: var(--checkbox-theme-color);
        }

        /* Dark mode specific adjustments */
        .dark [data-checkbox-theme]:checked {
            background-color: var(--checkbox-theme-color);
            border-color: var(--checkbox-theme-color);
        }

        .dark [data-checkbox-theme]:focus {
            border-color: var(--checkbox-theme-color);
            --tw-ring-color: var(--checkbox-theme-color);
        }
    </style>
@endonce

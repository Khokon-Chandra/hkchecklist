@props([
    'disabled' => false,
    'withicon' => false,
])

@php
    use App\Models\Setting;

    $withiconClasses = $withicon ? 'pl-11 pr-4' : 'px-4';
    $themeColor = Setting::get('theme_color', '#842eb8');
@endphp

<input {{ $disabled ? 'disabled' : '' }}
       data-focus-ring="{{ $themeColor }}"
       {!! $attributes->merge([
           'class' =>
               $withiconClasses .
               ' py-1 border-gray-300 rounded-md focus:border-gray-500 focus:ring
                   focus:ring-offset-2 focus:ring-offset-white dark:border-gray-700 dark:bg-dark-eval-1
                   dark:text-gray-300 dark:focus:ring-offset-dark-eval-1',
       ]) !!}>

<style>
    [data-focus-ring="{{ $themeColor }}"]:focus {
        --tw-ring-color: {{ $themeColor }} !important;
    }
</style>

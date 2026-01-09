@props([
    'as' => 'a',     // 'a'|'button'|'form'|'div'
    'href' => null,  // for links/forms
    'method' => null // for forms
])

@php
    use App\Models\Setting;

    // Get theme color
    $themeColor = Setting::get('theme_color', '#842eb8');
    $buttonPrimaryColor = Setting::get('button_primary_color');
    $primaryColor = $buttonPrimaryColor ?: $themeColor;

    // Helper function to convert hex to rgba with opacity
    if (!function_exists('hexToRgba')) {
        function hexToRgba($hex, $opacity = 1) {
            $hex = str_replace('#', '', $hex);
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "rgba({$r}, {$g}, {$b}, {$opacity})";
        }
    }

    // Low density hover background
    $hoverBg = hexToRgba($primaryColor, 0.1);
    $hoverBgDark = hexToRgba($primaryColor, 0.2);

    $base = 'flex w-full items-center gap-2 px-3 py-2 text-sm
             rounded outline-none transition-colors';
    $inlineStyles = "--dropdown-hover-bg: {$hoverBg}; --dropdown-hover-bg-dark: {$hoverBgDark}; --dropdown-hover-text: {$primaryColor}; --dropdown-text-light: rgb(55, 65, 81); --dropdown-text-dark: rgb(229, 231, 235);";
@endphp

@if($as === 'button')
    <button type="button" data-menu-item class="{{ $base }}" style="{{ $inlineStyles }}"
            x-init="const updateTextColor = () => { const isDark = document.querySelector('.dark') !== null; $el.style.color = isDark ? 'var(--dropdown-text-dark)' : 'var(--dropdown-text-light)'; }; updateTextColor(); const observer = new MutationObserver(updateTextColor); observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] }); $el._textColorObserver = observer;"
            {{ $attributes }}>
        {{ $slot }}
    </button>
@elseif($as === 'form')
    <form method="{{ $method ?? 'POST' }}" action="{{ $href }}" {{ $attributes->except(['class']) }}>
        @csrf
        <button type="submit" data-menu-item class="{{ $base }}" style="{{ $inlineStyles }}"
                x-init="const updateTextColor = () => { const isDark = document.querySelector('.dark') !== null; $el.style.color = isDark ? 'var(--dropdown-text-dark)' : 'var(--dropdown-text-light)'; }; updateTextColor(); const observer = new MutationObserver(updateTextColor); observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] }); $el._textColorObserver = observer;">
            {{ $slot }}
        </button>
    </form>
@elseif($as === 'div')
    <div tabindex="0" data-menu-item class="{{ $base }}" style="{{ $inlineStyles }}"
         x-init="const updateTextColor = () => { const isDark = document.querySelector('.dark') !== null; $el.style.color = isDark ? 'var(--dropdown-text-dark)' : 'var(--dropdown-text-light)'; }; updateTextColor(); const observer = new MutationObserver(updateTextColor); observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] }); $el._textColorObserver = observer;"
         {{ $attributes }}>
        {{ $slot }}
    </div>
@else
    <a href="{{ $href }}" data-menu-item class="{{ $base }}" style="{{ $inlineStyles }}"
       x-init="const updateTextColor = () => { const isDark = document.querySelector('.dark') !== null; $el.style.color = isDark ? 'var(--dropdown-text-dark)' : 'var(--dropdown-text-light)'; }; updateTextColor(); const observer = new MutationObserver(updateTextColor); observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] }); $el._textColorObserver = observer;"
       {{ $attributes }}>
        {{ $slot }}
    </a>
@endif

<style>
    [data-menu-item][style*="--dropdown-hover-bg"]:hover {
        background-color: var(--dropdown-hover-bg) !important;
        color: var(--dropdown-hover-text) !important;
    }

    /* Dark theme hover */
    .dark [data-menu-item][style*="--dropdown-hover-bg"]:hover,
    body:has(.dark) [data-menu-item][style*="--dropdown-hover-bg"]:hover {
        background-color: var(--dropdown-hover-bg-dark) !important;
        color: var(--dropdown-hover-text) !important;
    }

    [data-menu-item][style*="--dropdown-hover-bg"]:focus {
        background-color: var(--dropdown-hover-bg) !important;
        color: var(--dropdown-hover-text) !important;
    }

    /* Dark theme focus */
    .dark [data-menu-item][style*="--dropdown-hover-bg"]:focus,
    body:has(.dark) [data-menu-item][style*="--dropdown-hover-bg"]:focus {
        background-color: var(--dropdown-hover-bg-dark) !important;
        color: var(--dropdown-hover-text) !important;
    }
</style>

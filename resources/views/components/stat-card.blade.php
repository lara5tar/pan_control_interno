@props(['icon', 'label', 'value', 'bgColor' => 'bg-gray-800', 'iconColor' => 'text-white', 'formatNumber' => false])

<div class="bg-white rounded-lg border border-gray-200 p-6">
    <div class="flex items-center">
        <div class="p-3 {{ $bgColor }} rounded-full">
            <i class="{{ $icon }} {{ $iconColor }} text-2xl"></i>
        </div>
        <div class="ml-4">
            <p class="text-sm text-gray-600">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900">
                {{ $formatNumber ? number_format($value, 2) : (is_numeric($value) ? number_format($value) : $value) }}
            </p>
        </div>
    </div>
</div>

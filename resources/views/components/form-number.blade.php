@props([
    'name',
    'label',
    'value' => '',
    'required' => false,
    'min' => null,
    'max' => null,
    'step' => null,
    'placeholder' => '',
    'helpText' => '',
    'prefix' => null,
    'icon' => null
])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    <div class="relative">
        @if($prefix)
            <span class="absolute left-3 top-2 text-gray-500">{{ $prefix }}</span>
        @endif
        
        @if($icon)
            <span class="absolute left-3 top-2.5 text-gray-400">
                <i class="{{ $icon }}"></i>
            </span>
        @endif
        
        <input 
            type="number" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            @if($step !== null) step="{{ $step }}" @endif
            class="w-full {{ $prefix ? 'pl-8' : ($icon ? 'pl-10' : 'pl-4') }} pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error($name) border-red-500 @enderror"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >
    </div>
    
    @if($helpText)
        <p class="mt-1 text-sm text-gray-500">{{ $helpText }}</p>
    @endif
    
    @error($name)
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

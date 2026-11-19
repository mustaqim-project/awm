@props([
    'placeholder' => null,
    'searchPlaceholder' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'options' => null,
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'required' => false,
    'disabled' => false,
    'containerClass' => '',
    'buttonClass' => '',
])

@php
    $placeholderText = $placeholder ?? __('common.select', [], 'id') ?? 'Pilih...';
    $searchPlaceholderText = $searchPlaceholder ?? __('common.search', [], 'id') ?? 'Cari...';
@endphp

<div {{ $attributes->merge(['class' => 'relative w-full ' . $containerClass]) }}
     x-data="coocaSearchableSelect({
         placeholder: @js($placeholderText),
         searchPlaceholder: @js($searchPlaceholderText)
     })"
     x-init="init()"
     @keydown.escape.stop="close()">

    {{-- Native Select: Slot (Mode A) or Auto-Generated (Mode B) --}}
    @if($slot->isNotEmpty())
        {{ $slot }}
    @else
        <select name="{{ $name }}"
                @if($id) id="{{ $id }}" @endif
                @if($required) required @endif
                @if($disabled) disabled @endif
                class="sr-only">
            @if($placeholder)
                <option value="">{{ $placeholderText }}</option>
            @endif
            @if($options)
                @foreach($options as $k => $opt)
                    @php
                        $val = is_array($opt) ? ($opt[$optionValue] ?? $k) : (is_object($opt) ? ($opt->{$optionValue} ?? $k) : $k);
                        $lbl = is_array($opt) ? ($opt[$optionLabel] ?? $opt) : (is_object($opt) ? ($opt->{$optionLabel} ?? $opt) : $opt);
                        $isSelected = (string)$val === (string)$value;
                    @endphp
                    <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            @endif
        </select>
    @endif

    {{-- Bento Apple HIG Trigger Button --}}
    <button type="button"
            x-ref="triggerButton"
            @click="toggle()"
            @keydown="onKeyDown($event)"
            :disabled="nativeSelect && nativeSelect.disabled"
            :class="{
                'ring-2 ring-[#007AFF]/50 border-[#007AFF]/60': open,
                'opacity-50 cursor-not-allowed': nativeSelect && nativeSelect.disabled
            }"
            class="w-full h-10 sm:h-9 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.07] dark:hover:bg-white/[0.1] border border-black/10 dark:border-white/10 rounded-[10px] sm:rounded-[12px] px-3 text-[13px] flex items-center justify-between transition focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 cursor-pointer text-left {{ $buttonClass }}">
        <span class="truncate pr-2"
              :class="(selectedValue !== '' && selectedValue !== null && selectedValue !== undefined) ? 'text-black dark:text-white font-medium' : 'text-black/40 dark:text-white/40'"
              x-text="currentLabel">
        </span>
        <svg class="w-4 h-4 text-black/40 dark:text-white/40 shrink-0 transition-transform duration-200"
             :class="{ 'rotate-180 text-[#007AFF]': open }"
             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    {{-- Bento Apple HIG Popover Card --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1 scale-98"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-98"
         @click.outside="close()"
         class="absolute z-50 left-0 right-0 top-full mt-1.5 p-1.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 shadow-[0_12px_36px_rgba(0,0,0,0.25)] flex flex-col space-y-1.5 max-h-[300px]">

        {{-- Search Input with Clear Button --}}
        <div class="relative px-0.5 pt-0.5">
            <svg class="w-3.5 h-3.5 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text"
                   x-ref="searchInput"
                   x-model="search"
                   @keydown="onKeyDown($event)"
                   placeholder="{{ $searchPlaceholderText }}"
                   class="w-full h-8 pl-8 pr-7 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
            <button type="button"
                    x-show="search.length > 0"
                    @click="search = ''; $refs.searchInput.focus()"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 flex items-center justify-center text-black/30 dark:text-white/30 hover:text-black dark:hover:text-white transition">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Filtered Options List --}}
        <div class="overflow-y-auto space-y-0.5 max-h-[220px] custom-scrollbar px-0.5">
            <template x-for="(opt, index) in filteredOptions" :key="opt.value + '-' + index">
                <div>
                    {{-- Optional Group Header --}}
                    <div x-show="opt.group && (index === 0 || filteredOptions[index - 1].group !== opt.group)"
                         class="px-2 pt-1.5 pb-0.5 text-[10.5px] font-semibold text-black/40 dark:text-white/40 tracking-wider uppercase"
                         x-text="opt.group">
                    </div>

                    {{-- Option Item --}}
                    <div @click="selectOption(opt)"
                         :class="{
                             'bg-[#007AFF]/10 text-[#007AFF] font-medium dark:bg-[#007AFF]/20': isSelected(opt),
                             'bg-black/[0.05] dark:bg-white/[0.08]': isHighlighted(index),
                             'text-black dark:text-white': !isSelected(opt),
                             'opacity-50 cursor-not-allowed pointer-events-none': opt.disabled
                         }"
                         class="px-2.5 py-1.5 rounded-[8px] text-[12.5px] cursor-pointer flex items-center justify-between transition hover:bg-black/[0.04] dark:hover:bg-white/[0.06]">
                        <span x-text="opt.text" class="truncate"></span>
                        <template x-if="isSelected(opt)">
                            <svg class="w-4 h-4 text-[#007AFF] shrink-0 ml-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="filteredOptions.length === 0"
                 class="px-3 py-4 text-center text-[12px] text-black/40 dark:text-white/40">
                {{ __('common.no_data_found', [], 'id') ?? 'Tidak ada data ditemukan' }}
            </div>
        </div>
    </div>
</div>

@once
<script>
    if (typeof window.coocaSearchableSelect === 'undefined') {
        window.coocaSearchableSelect = function(config = {}) {
            return {
                open: false,
                search: '',
                options: [],
                highlightedIndex: -1,
                selectedLabel: '',
                selectedValue: '',
                nativeSelect: null,
                placeholder: config.placeholder || 'Pilih...',
                searchPlaceholder: config.searchPlaceholder || 'Cari...',
                observer: null,

                init() {
                    this.$nextTick(() => {
                        this.nativeSelect = this.$refs.nativeSelect || this.$el.querySelector('select');
                        if (this.nativeSelect) {
                            this.nativeSelect.style.cssText = 'position: absolute !important; opacity: 0 !important; width: 1px !important; height: 1px !important; pointer-events: none !important; margin: 0 !important; border: 0 !important; padding: 0 !important;';
                            this.nativeSelect.setAttribute('tabindex', '-1');
                            this.nativeSelect.setAttribute('aria-hidden', 'true');

                            this.syncFromNative();

                            this.nativeSelect.addEventListener('change', () => this.syncFromNativeValue());
                            this.nativeSelect.addEventListener('input', () => this.syncFromNativeValue());
                            this.nativeSelect.addEventListener('focus', () => {
                                if (this.$refs.triggerButton) this.$refs.triggerButton.focus();
                            });
                            this.nativeSelect.addEventListener('invalid', () => {
                                this.open = true;
                                if (this.$refs.triggerButton) this.$refs.triggerButton.focus();
                            });

                            if (window.MutationObserver) {
                                this.observer = new MutationObserver(() => {
                                    this.syncFromNative();
                                });
                                this.observer.observe(this.nativeSelect, {
                                    childList: true,
                                    subtree: true,
                                    characterData: true,
                                    attributes: true,
                                    attributeFilter: ['selected', 'value', 'disabled']
                                });
                            }
                        }
                    });
                },

                destroy() {
                    if (this.observer) {
                        this.observer.disconnect();
                    }
                },

                syncFromNative() {
                    if (!this.nativeSelect) return;
                    const opts = [];
                    const children = Array.from(this.nativeSelect.children);
                    for (const el of children) {
                        if (el.tagName && el.tagName.toLowerCase() === 'optgroup') {
                            const grp = el.getAttribute('label') || '';
                            const grpChildren = Array.from(el.children);
                            for (const optEl of grpChildren) {
                                if (optEl.tagName && optEl.tagName.toLowerCase() === 'option') {
                                    opts.push({
                                        value: optEl.value,
                                        text: optEl.textContent.trim(),
                                        group: grp,
                                        disabled: optEl.disabled,
                                        selected: optEl.selected
                                    });
                                }
                            }
                        } else if (el.tagName && el.tagName.toLowerCase() === 'option') {
                            opts.push({
                                value: el.value,
                                text: el.textContent.trim(),
                                group: null,
                                disabled: el.disabled,
                                selected: el.selected
                            });
                        }
                    }
                    this.options = opts;
                    this.syncFromNativeValue();
                },

                syncFromNativeValue() {
                    if (!this.nativeSelect) return;
                    const curVal = this.nativeSelect.value;
                    this.selectedValue = curVal;
                    const matched = this.options.find(o => String(o.value) === String(curVal));
                    if (matched) {
                        this.selectedLabel = matched.text;
                    } else {
                        const emptyOpt = this.options.find(o => String(o.value) === '');
                        if (emptyOpt && emptyOpt.text) {
                            this.selectedLabel = emptyOpt.text;
                        } else {
                            this.selectedLabel = this.placeholder;
                        }
                    }
                },

                get currentLabel() {
                    if (this.nativeSelect && String(this.nativeSelect.value) !== String(this.selectedValue)) {
                        this.syncFromNativeValue();
                    }
                    if (this.selectedLabel && this.selectedLabel.trim()) {
                        return this.selectedLabel;
                    }
                    return this.placeholder;
                },

                get filteredOptions() {
                    if (!this.search || !this.search.trim()) {
                        return this.options;
                    }
                    const q = this.search.toLowerCase().trim();
                    return this.options.filter(opt => {
                        const matchText = (opt.text || '').toLowerCase().includes(q);
                        const matchGroup = opt.group ? opt.group.toLowerCase().includes(q) : false;
                        return matchText || matchGroup;
                    });
                },

                toggle() {
                    if (this.nativeSelect && this.nativeSelect.disabled) return;
                    this.open = !this.open;
                    if (this.open) {
                        this.search = '';
                        this.highlightedIndex = -1;
                        this.$nextTick(() => {
                            if (this.$refs.searchInput) {
                                this.$refs.searchInput.focus();
                            }
                        });
                    }
                },

                close() {
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                },

                selectOption(opt) {
                    if (!opt || opt.disabled) return;
                    this.selectedValue = opt.value;
                    this.selectedLabel = opt.text;
                    if (this.nativeSelect) {
                        this.nativeSelect.value = opt.value;
                        this.nativeSelect.dispatchEvent(new Event('input', { bubbles: true }));
                        this.nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    this.close();
                    if (this.$refs.triggerButton) {
                        this.$refs.triggerButton.focus();
                    }
                },

                isSelected(opt) {
                    return String(opt.value) === String(this.selectedValue);
                },

                isHighlighted(index) {
                    const enabledOpts = this.filteredOptions.filter(o => !o.disabled);
                    return enabledOpts[this.highlightedIndex] === this.filteredOptions[index];
                },

                onKeyDown(event) {
                    if (!this.open) {
                        if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                            event.preventDefault();
                            this.toggle();
                        }
                        return;
                    }

                    const enabledOpts = this.filteredOptions.filter(o => !o.disabled);
                    if (enabledOpts.length === 0) return;

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        this.highlightedIndex = (this.highlightedIndex + 1) % enabledOpts.length;
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        this.highlightedIndex = (this.highlightedIndex - 1 + enabledOpts.length) % enabledOpts.length;
                    } else if (event.key === 'Enter') {
                        event.preventDefault();
                        if (this.highlightedIndex >= 0 && this.highlightedIndex < enabledOpts.length) {
                            this.selectOption(enabledOpts[this.highlightedIndex]);
                        }
                    } else if (event.key === 'Escape') {
                        event.preventDefault();
                        this.close();
                    } else if (event.key === 'Tab') {
                        this.close();
                    }
                }
            };
        };

        document.addEventListener('alpine:init', () => {
            if (window.Alpine) {
                window.Alpine.data('coocaSearchableSelect', window.coocaSearchableSelect);
            }
        });
    }
</script>
@endonce

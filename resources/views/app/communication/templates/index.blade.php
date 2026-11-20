@extends('layouts.app', [
    'title' => __('communication.templates_title'),
    'headerTitle' => __('communication.module_title'),
    'headerSubtitle' => __('communication.templates_subtitle')
])

@section('content')
@php
    $templatesJson = [];
    foreach ($available as $k => $tpl) {
        $saved = $savedTemplates->get($k);
        $templatesJson[$k] = [
            'key' => $k,
            'name' => $tpl['name'],
            'description' => $tpl['description'],
            'subject' => $saved?->subject ?? $tpl['default_subject'],
            'body_html' => $saved?->body_html ?? $tpl['default_body'],
            'is_custom' => (bool) $saved,
            'variables' => $tpl['variables'],
        ];
    }
@endphp

<div class="max-w-[1200px] mx-auto space-y-6 pb-20" x-data="{
    templates: {{ json_encode($templatesJson) }},
    activeKey: 'quotation',
    currentSubject: '',
    currentBody: '',
    init() {
        this.selectTemplate('quotation');
    },
    selectTemplate(key) {
        this.activeKey = key;
        if (this.templates[key]) {
            this.currentSubject = this.templates[key].subject;
            this.currentBody = this.templates[key].body_html;
        }
    },
    insertVariable(placeholder) {
        const textarea = document.getElementById('body_html_textarea');
        if (!textarea) {
            this.currentBody += ' ' + placeholder;
            return;
        }
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = this.currentBody;
        this.currentBody = text.substring(0, start) + placeholder + text.substring(end);
        this.$nextTick(() => {
            textarea.focus();
            textarea.setSelectionRange(start + placeholder.length, start + placeholder.length);
        });
    }
}">
    <!-- 1. NAVIGATION TABS -->
    @include('app.communication.partials.nav-tabs')

    <!-- 2. TEMPLATE SELECTOR STRIP -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($available as $k => $tpl)
            @php $saved = $savedTemplates->get($k); @endphp
            <button type="button"
                    @click="selectTemplate('{{ $k }}')"
                    class="p-4 rounded-[14px] text-left transition-all border flex items-start justify-between gap-3 cursor-pointer"
                    :class="activeKey === '{{ $k }}'
                        ? 'bg-white dark:bg-[#1C1C1E] border-[#007AFF] shadow-[0_3px_10px_rgba(0,122,255,0.12)] ring-2 ring-[#007AFF]/20'
                        : 'bg-white/60 dark:bg-[#1C1C1E]/60 border-black/5 dark:border-white/5 hover:bg-white dark:hover:bg-[#1C1C1E]'">
                <div class="min-w-0">
                    <div class="text-[13px] font-semibold text-black dark:text-white truncate">
                        {{ $tpl['name'] }}
                    </div>
                    <p class="text-[11px] text-black/50 dark:text-white/50 line-clamp-1 mt-0.5">
                        {{ $tpl['description'] }}
                    </p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide shrink-0"
                      :class="'{{ $saved ? 'bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]' : 'bg-black/5 dark:bg-white/5 text-black/40 dark:text-white/40' }}'">
                    {{ $saved ? 'Custom' : 'Default' }}
                </span>
            </button>
        @endforeach
    </div>

    <!-- 3. TEMPLATE EDITOR & PLACEHOLDERS (Bento HIG) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Editor Column (2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-5 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-4">
                    <div>
                        <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight" x-text="templates[activeKey]?.name"></h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5" x-text="templates[activeKey]?.description"></p>
                    </div>
                    <template x-if="templates[activeKey]?.is_custom">
                        <form action="{{ route('communication.templates.reset') }}" method="POST" onsubmit="return confirm('{{ __('communication.reset_confirm') }}')">
                            @csrf
                            <input type="hidden" name="template_key" :value="activeKey">
                            <button type="submit" class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 transition-all flex items-center gap-1 cursor-pointer">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                <span>{{ __('communication.reset_template') }}</span>
                            </button>
                        </form>
                    </template>
                </div>

                <form action="{{ route('communication.templates.update') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="template_key" :value="activeKey">

                    <!-- Subject -->
                    <div>
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.subject_label') }} *</label>
                        <input type="text" name="subject" x-model="currentSubject" required
                               class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <!-- Body Editor -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('communication.body_label') }} *</label>
                            <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('communication.body_help') }}</span>
                        </div>
                        <textarea id="body_html_textarea" name="body_html" x-model="currentBody" rows="12" required
                                  class="w-full p-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] font-mono leading-relaxed text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] resize-y"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="submit" class="h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)]">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>{{ __('communication.save_template') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Placeholders & Live Preview Column (1 col) -->
        <div class="space-y-6">
            <!-- Placeholders Card -->
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                <div>
                    <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight flex items-center gap-1.5">
                        <i data-lucide="tag" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>{{ __('communication.available_variables') }}</span>
                    </h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">{{ __('communication.variables_help') }}</p>
                </div>

                <div class="space-y-2">
                    <template x-for="(desc, placeholder) in (templates[activeKey]?.variables || {})" :key="placeholder">
                        <button type="button"
                                @click="insertVariable(placeholder)"
                                class="w-full text-left p-2 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.04] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all group flex items-start justify-between gap-2 cursor-pointer border border-transparent hover:border-[#007AFF]/30">
                            <div>
                                <code class="text-[11px] font-bold text-[#007AFF] dark:text-[#0A84FF]" x-text="placeholder"></code>
                                <p class="text-[10px] text-black/60 dark:text-white/60 mt-0.5 leading-snug" x-text="desc"></p>
                            </div>
                            <i data-lucide="plus" class="w-3.5 h-3.5 text-black/30 dark:text-white/30 group-hover:text-[#007AFF] shrink-0 mt-0.5"></i>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-3 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight flex items-center gap-1.5">
                    <i data-lucide="eye" class="w-4 h-4 text-[#34C759]"></i>
                    <span>{{ __('communication.live_preview') }}</span>
                </h3>
                <div class="p-3.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-2">
                    <div class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase">Subjek:</div>
                    <div class="text-[12px] font-bold text-black dark:text-white" x-text="currentSubject"></div>
                    <div class="border-t border-black/5 dark:border-white/5 pt-2">
                        <div class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase mb-1">Konten:</div>
                        <div class="text-[12px] text-black/70 dark:text-white/70 prose dark:prose-invert max-w-none text-xs leading-relaxed" x-html="currentBody"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<x-filament-panels::page>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

    <style>
        .sortable-ghost {
            opacity: 0.3;
            background: #fef3c7 !important;
        }

        .drag-handle {
            cursor: grab;
            user-select: none;
        }

        .drag-handle:active {
            cursor: grabbing;
        }
    </style>
    <style>
        .sortable-ghost {
            opacity: 0.3;
            background: #fef3c7 !important;
        }

        .drag-handle {
            cursor: grab;
            user-select: none;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .cat-layout {
            display: -webkit-inline-box;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: start;
        }

        @media (max-width: 768px) {
            .cat-layout {
                display: block;
            }
        }
    </style>
    <div class="cat-layout">


        {{-- ── SOL: SIRALAMA PANELİ ── --}}
        <div x-data="{
            saving: false,
            pendingOrder: [],
            init() { this.$nextTick(() => this.initSortable()) },
            initSortable() {
                const self = this
                const root = document.getElementById('cat-sortable')
                if (!root) return
        
                Sortable.create(root, {
                    group: {
                        name: 'root',
                        pull: true,
                        put: function(to, from) {
                            // Sadece child-sortable'dan çıkan elemanlar ana listeye gelebilir
                            return from.el.classList.contains('child-sortable')
                        }
                    },
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    onEnd() { self.rebuildOrder() }
                })
        
                root.querySelectorAll('.child-sortable').forEach(el => {
                    Sortable.create(el, {
                        group: {
                            name: 'child',
                            pull: true,
                            put: function(to, from, dragEl) {
                                // Hedef child-sortable'ın parent'ı zaten bir child mi?
                                // Yani bu child-sortable bir alt kategorinin içinde mi?
                                const toParentLi = to.el.closest('.cat-row')
                                const isNested = toParentLi?.closest('.child-sortable') !== null
                                if (isNested) return false
        
                                return from.el.classList.contains('child-sortable') ||
                                    from.el === root
                            }
                        },
                        animation: 150,
                        handle: '.drag-handle',
                        ghostClass: 'sortable-ghost',
                        onEnd() { self.rebuildOrder() }
                    })
                })
            },
            rebuildOrder() {
                const order = []
                document.getElementById('cat-sortable')
                    ?.querySelectorAll(':scope > .cat-row').forEach(el => {
                        const children = []
                        el.querySelector('.child-sortable')
                            ?.querySelectorAll(':scope > .cat-row')
                            .forEach(c => children.push({ id: parseInt(c.dataset.id) }))
                        order.push({ id: parseInt(el.dataset.id), children })
                    })
                this.pendingOrder = order
            },
            async save() {
                if (!this.pendingOrder.length) return
                this.saving = true
                await $wire.updateOrder(this.pendingOrder)
                this.saving = false
                this.pendingOrder = []
                this.$nextTick(() => this.initSortable())
            }
        }" x-init="init()" style="position: sticky; top: 4rem;">
            <x-filament::section>
                <x-slot name="heading">
                    <div style="display:flex; align-items:center; justify-content:space-between; width:100%;">
                        <span>Sıralama</span>
                        <button @click="save()" :disabled="saving || !pendingOrder.length"
                            style="display:flex; align-items:center; gap:0.4rem;
                               padding:0.25rem 0.75rem; border-radius:0.5rem;
                               font-size:0.75rem; font-weight:500;
                               background:#16a34a; color:white; border:none; cursor:pointer;"
                            :style="!pendingOrder.length ? 'opacity:0.4;cursor:not-allowed' : 'opacity:1'">
                            <span x-show="!saving">💾 Kaydet</span>
                            <span x-show="saving">...</span>
                        </button>
                    </div>
                </x-slot>

                <p style="font-size:0.75rem; color:#6b7280; margin-bottom:0.75rem;">
                    ☰ Sürükle &nbsp;|&nbsp; ↳ Alt kategori için içine bırak
                </p>

                <ul id="cat-sortable" wire:ignore
                    style="list-style:none; padding:0; margin:0;
                       display:flex; flex-direction:column; gap:0.35rem;
                       max-height: 70vh; overflow-y: auto;">

                    @foreach ($sortableCategories as $cat)
                        <li class="cat-row" data-id="{{ $cat['id'] }}"
                            style="border-radius:0.5rem; border:1px solid #e5e7eb;
                               background:white; overflow:hidden;">

                            {{-- Ana Kategori --}}
                            <div style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem;">
                                <span class="drag-handle" style="color:#d1d5db; flex-shrink:0;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </span>
                                <span style="flex:1; font-size:0.8rem; font-weight:600; color:#111827;">
                                    {{ $cat['name'] }}
                                </span>
                                @if (!empty($cat['children']))
                                    <span style="font-size:0.65rem; color:#9ca3af;">
                                        {{ count($cat['children']) }}↓
                                    </span>
                                @endif
                                <span
                                    style="width:8px; height:8px; border-radius:50%; flex-shrink:0;
                                         background: {{ $cat['is_active'] ? '#16a34a' : '#d1d5db' }};"></span>
                            </div>

                            {{-- Alt Kategoriler --}}
                            <ul class="child-sortable"
                                style="list-style:none; margin:0; padding:0 0.5rem 0.35rem 1.5rem;
                                   display:flex; flex-direction:column; gap:0.25rem; min-height:6px;">
                                @foreach ($cat['children'] as $child)
                                    <li class="cat-row" data-id="{{ $child['id'] }}"
                                        style="border-radius:0.375rem; border:1px solid #f3f4f6; background:#f9fafb;">
                                        <div
                                            style="display:flex; align-items:center; gap:0.5rem; padding:0.375rem 0.625rem;">
                                            <span class="drag-handle" style="color:#d1d5db; flex-shrink:0;">
                                                <svg width="14" height="14" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                                </svg>
                                            </span>
                                            <span style="color:#d1d5db; font-size:0.7rem;">└</span>
                                            <span style="flex:1; font-size:0.75rem; color:#374151;">
                                                {{ $child['name'] }}
                                            </span>
                                            <span
                                                style="width:6px; height:6px; border-radius:50%; flex-shrink:0;
                                                     background: {{ $child['is_active'] ? '#16a34a' : '#d1d5db' }};"></span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </x-filament::section>
        </div>

        {{-- ── SAĞ: TABLO ── --}}
        <div>
            {{ $this->table }}
        </div>

    </div>
</x-filament-panels::page>

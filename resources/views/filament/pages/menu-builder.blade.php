<x-filament-panels::page>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

    <style>
        .sortable-ghost {
            opacity: 0.3;
            background: #e8c9c1 !important;
        }

        .drag-handle {
            cursor: grab;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .child-sortable {
            min-height: 8px;
        }
    </style>

    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start;">

        {{-- ── SOL KOLON ── --}}
        <div style="display: flex; flex-direction: column; gap: 1rem;">

            <x-filament::section>
                <x-slot name="heading">Menü Seç</x-slot>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="selectedMenuId">
                        @foreach ($this->menus as $menu)
                            <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Öğe Ekle</x-slot>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">

                    <x-filament::input.wrapper label="Tür">
                        <x-filament::input.select wire:model.live="newType">
                            <option value="custom">Manuel URL</option>
                            <option value="category">Kategori</option>
                            <option value="page">Sayfa</option>
                            <option value="blog">Blog</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>

                    <x-filament::input.wrapper label="Başlık *">
                        <x-filament::input type="text" wire:model="newLabel" placeholder="Menü başlığı" />
                    </x-filament::input.wrapper>

                    @if ($newType === 'custom')
                        <x-filament::input.wrapper label="URL">
                            <x-filament::input type="text" wire:model="newUrl" placeholder="/ornek-sayfa" />
                        </x-filament::input.wrapper>
                    @endif

                    @if ($newType === 'category')
                        <x-filament::input.wrapper label="Kategori">
                            <x-filament::input.select wire:model="newLinkableId">
                                <option value="">Seçin...</option>
                                @foreach ($this->categories as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    @endif

                    @if ($newType === 'page')
                        <x-filament::input.wrapper label="Sayfa">
                            <x-filament::input.select wire:model="newLinkableId">
                                <option value="">Seçin...</option>
                                @foreach ($this->pages as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    @endif
                    @if ($newType === 'blog')
                        <x-filament::input.wrapper label="Blog Yazısı">
                            <x-filament::input.select wire:model="newLinkableId">
                                <option value="">Seçin...</option>
                                @foreach ($this->blogPosts as $id => $title)
                                    <option value="{{ $id }}">{{ $title }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    @endif
                    <x-filament::input.wrapper label="Hedef">
                        <x-filament::input.select wire:model="newTarget">
                            <option value="_self">Aynı Sekme</option>
                            <option value="_blank">Yeni Sekme</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>

                    <x-filament::button wire:click="addItem" icon="heroicon-o-plus" style="width:100%">
                        Menüye Ekle
                    </x-filament::button>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">İpuçları</x-slot>
                <div style="font-size:0.8rem; color:#6b7280; display:flex; flex-direction:column; gap:0.5rem;">
                    <div>☰ Öğeleri sürükleyerek sıralayın</div>
                    <div>↳ Alt menü için öğeyi başka öğenin altına sürükleyin</div>
                    <div>✓ Kaydet butonuna basarak kaydedin</div>
                </div>
            </x-filament::section>

        </div>

        
        <div x-data="{
            saving: false,
            pendingOrder: [],
            sortableInstances: [],
        
            init() {
                this.setupSortable()
        
                // Livewire her 'menuUpdated' dispatch ettiğinde DOM güncellenmiş olur,
                // biz de bir sonraki tick'te Sortable'ı yeniden kuruyoruz
                $wire.on('menuUpdated', () => {
                    this.$nextTick(() => {
                        this.destroySortable()
                        this.setupSortable()
                        this.pendingOrder = []
                    })
                })
            },
        
            destroySortable() {
                this.sortableInstances.forEach(s => s.destroy())
                this.sortableInstances = []
            },
        
            setupSortable() {
                const container = document.getElementById('menu-sortable')
                if (!container) return
        
                const self = this
        
                // Root liste
                const rootInstance = Sortable.create(container, {
                    group: { name: 'menu', pull: true, put: true },
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    onEnd() { self.rebuildFromDOM() }
                })
                this.sortableInstances.push(rootInstance)
        
                // Her child-sortable için ayrı instance
                container.querySelectorAll('.child-sortable').forEach(el => {
                    const childInstance = Sortable.create(el, {
                        group: { name: 'menu', pull: true, put: true },
                        animation: 150,
                        handle: '.drag-handle',
                        ghostClass: 'sortable-ghost',
                        onEnd() { self.rebuildFromDOM() }
                    })
                    this.sortableInstances.push(childInstance)
                })
            },
        
            rebuildFromDOM() {
                const order = []
                const container = document.getElementById('menu-sortable')
                if (!container) return
        
                container.querySelectorAll(':scope > .menu-item-row').forEach(el => {
                    const children = []
                    const childList = el.querySelector('.child-sortable')
                    if (childList) {
                        childList.querySelectorAll(':scope > .menu-item-row').forEach(c => {
                            children.push({ id: parseInt(c.dataset.id) })
                        })
                    }
                    order.push({ id: parseInt(el.dataset.id), children })
                })
                this.pendingOrder = order
            },
        
            async saveOrder() {
                if (!this.pendingOrder.length) return
                this.saving = true
                await $wire.updateOrder(this.pendingOrder)
                this.saving = false
            },
        
            removeItem(id) {
                $wire.removeItem(id)
            },
        
            toggleActive(id) {
                $wire.toggleActive(id)
            }
        }" x-init="init()">
            <x-filament::section>
                <x-slot name="heading">
                    <div style="display:flex; align-items:center; justify-content:space-between; width:100%;">
                        <span style="display:flex; align-items:center; gap:0.5rem;">
                            <x-filament::icon icon="heroicon-o-bars-3" class="w-4 h-4" />
                            Menü Yapısı
                        </span>
                        <button @click="saveOrder()" :disabled="saving || !pendingOrder.length"
                            style="display:flex; align-items:center; gap:0.5rem;
                               padding: 0.375rem 0.875rem; border-radius:0.5rem;
                               font-size:0.8rem; font-weight:500; transition: all 0.2s;
                               background: #16a34a; color: white; border: none; cursor: pointer;"
                            :style="(!pendingOrder.length) ? 'opacity:0.5; cursor:not-allowed;' : 'opacity:1;'">
                            <span x-show="!saving">💾 Sıralamayı Kaydet</span>
                            <span x-show="saving">Kaydediliyor...</span>
                        </button>
                    </div>
                </x-slot>

                @if (empty($menuItems))
                    <div style="text-align:center; padding:4rem 0; color:#9ca3af;">
                        <p style="font-size:0.9rem; font-weight:500;">Henüz menü öğesi yok</p>
                        <p style="font-size:0.8rem; margin-top:0.25rem;">Sol taraftan öğe ekleyin</p>
                    </div>
                @else
                   
                    <ul id="menu-sortable"
                        style="display:flex; flex-direction:column; gap:0.5rem; list-style:none; padding:0; margin:0;">
                        @foreach ($menuItems as $item)
                            <li class="menu-item-row" data-id="{{ $item['id'] }}"
                                style="border-radius:0.75rem; border:1px solid #e5e7eb; background:white; overflow:hidden;">

                                {{-- Ana Öğe --}}
                                <div style="display:flex; align-items:center; gap:0.75rem; padding:0.875rem 1rem;">
                                    <span class="drag-handle" style="color:#d1d5db; flex-shrink:0; user-select:none;">
                                        <svg width="20" height="20" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                    </span>

                                    <div style="flex:1; min-width:0;">
                                        <p style="font-size:0.875rem; font-weight:600; color:#111827; margin:0;">
                                            {{ $item['label'] }}
                                        </p>
                                        <p
                                            style="font-size:0.75rem; color:#9ca3af; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                            {{ $item['url'] ?: '— ' . $item['type'] }}
                                        </p>
                                    </div>

                                    <div style="display:flex; align-items:center; gap:0.5rem; flex-shrink:0;">
                                        <span @click="toggleActive({{ $item['id'] }})"
                                            style="cursor:pointer; padding:0.2rem 0.6rem; border-radius:9999px;
                                                 font-size:0.75rem; font-weight:500;
                                                 {{ $item['is_active'] ? 'background:#dcfce7; color:#16a34a;' : 'background:#f3f4f6; color:#6b7280;' }}">
                                            {{ $item['is_active'] ? 'Aktif' : 'Pasif' }}
                                        </span>
                                        <span @click="removeItem({{ $item['id'] }})"
                                            style="cursor:pointer; color:#f87171; padding:0.25rem;" title="Sil">
                                            <svg width="16" height="16" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                {{-- Alt Öğeler --}}
                                <ul class="child-sortable"
                                    style="list-style:none; padding:0 0.75rem 0.5rem 2.5rem; margin:0;
                                       display:flex; flex-direction:column; gap:0.375rem; min-height:8px;">
                                    @foreach ($item['children'] as $child)
                                        <li class="menu-item-row" data-id="{{ $child['id'] }}"
                                            style="border-radius:0.5rem; border:1px solid #f3f4f6;
                                               background:#f9fafb; overflow:hidden;">
                                            <div
                                                style="display:flex; align-items:center; gap:0.75rem; padding:0.625rem 0.875rem;">
                                                <span class="drag-handle"
                                                    style="color:#d1d5db; flex-shrink:0; user-select:none;">
                                                    <svg width="16" height="16" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                                    </svg>
                                                </span>
                                                <span style="color:#d1d5db; font-size:0.875rem;">└</span>
                                                <div style="flex:1; min-width:0;">
                                                    <p style="font-size:0.875rem; color:#374151; margin:0;">
                                                        {{ $child['label'] }}</p>
                                                    <p
                                                        style="font-size:0.75rem; color:#9ca3af; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                        {{ $child['url'] ?: '— ' . $child['type'] }}
                                                    </p>
                                                </div>
                                                <div
                                                    style="display:flex; align-items:center; gap:0.5rem; flex-shrink:0;">
                                                    <span @click="toggleActive({{ $child['id'] }})"
                                                        style="cursor:pointer; padding:0.2rem 0.6rem; border-radius:9999px;
                                                             font-size:0.75rem; font-weight:500;
                                                             {{ $child['is_active'] ? 'background:#dcfce7; color:#16a34a;' : 'background:#f3f4f6; color:#6b7280;' }}">
                                                        {{ $child['is_active'] ? 'Aktif' : 'Pasif' }}
                                                    </span>
                                                    <span @click="removeItem({{ $child['id'] }})"
                                                        style="cursor:pointer; color:#f87171; padding:0.25rem;"
                                                        title="Sil">
                                                        <svg width="16" height="16" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>

                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>

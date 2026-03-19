<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Page;

new #[Layout('layouts.app')] class extends Component {
    public ?Page $page = null;

    public function mount(string $slug): void
    {
        $this->page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();
    }

    public function with(): array
    {
        return [
            'pages' => Page::where('is_active', true)
                ->orderBy('title')
                ->get(['id', 'title', 'slug']),
        ];
    }
};
?>

<div>
    {{-- Breadcrumb --}}
    {{-- <div class="bg-blush-light/50 border-b border-sand/20">
        <div class="max-w-screen-xl mx-auto px-6 py-3">
            <nav class="flex items-center gap-2 font-body text-xs text-smoke">
                <a href="{{ route('home') }}" class="hover:text-ink transition-colors">Ana Sayfa</a>
                <span>/</span>
                <span class="text-ink">{{ $page->title }}</span>
            </nav>
        </div>
    </div> --}}

    <div class="max-w-screen-xl mx-auto">
        <div class="grid md:grid-cols-4 ">

            <aside class="md:col-span-1">
                <div class="border border-sand/30">
                    <div class="bg-white px-2 py-3">
                        <p class="font-body text-md tracking-widest uppercase text-red-600">
                            Daha Fazla Bilgi
                        </p>
                    </div>
                    <nav class="divide-y divide-sand/20">
                        @foreach ($pages as $p)
                            <a href="{{ route('page', $p->slug) }}"
                                class="block px-2 py-2 font-body text-xs transition-colors
                                      {{ $page->slug === $p->slug
                                          ? 'bg-blush-light text-ink font-medium'
                                          : 'text-smoke hover:text-ink hover:bg-blush-light/50' }}">
                                {{ $p->title }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>

            <div class="md:col-span-3">
                <div class="border border-sand/30 p-2">
                    <h1 class="font-display text-xl font-light text-red-600 mb-6 pb-4 border-b border-sand/30">
                        {{ $page->title }}
                    </h1>
                    <div
                        class="prose prose-sm max-w-none font-body text-smoke leading-relaxed
                                prose-headings:font-display prose-headings:font-light prose-headings:text-ink
                                prose-strong:text-ink prose-a:text-ink prose-a:underline px-2">
                        {!! $page->content !!}
                        @if (request()->route('slug') === 'iletisim')
                            <livewire:contact-widget />
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\BlogPost;

new #[Layout('layouts.app')] #[Title('Blog — Eren Abiye')] class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = BlogPost::where('is_published', true)->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%')->orWhere('content', 'like', '%' . $this->search . '%'))->latest('published_at');

        return [
            'posts' => $query->paginate(9),
            'featuredPost' => BlogPost::where('is_published', true)->latest('published_at')->first(),
        ];
    }
};
?>

<div>
    {{-- Breadcrumb --}}
    <div class="bg-blush-light/50 border-b border-sand/20">
        <div class="max-w-screen-xl mx-auto px-6 py-3">
            <nav class="flex items-center gap-2 font-body text-xs text-smoke">
                <a href="{{ route('home') }}" class="hover:text-ink transition-colors">Ana Sayfa</a>
                <span>/</span>
                <span class="text-ink">Blog</span>
            </nav>
        </div>
    </div>

    <div class="max-w-screen-xl mx-auto px-6 py-16">

        {{-- Başlık --}}
        <div class="text-center mb-12">
            <p class="font-body text-xs tracking-widest2 uppercase text-smoke mb-3">Haberler & İlham</p>
            <h1 class="font-display text-5xl font-light text-ink">Blog</h1>
        </div>

        {{-- Arama --}}
        <div class="max-w-md mx-auto mb-12">
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Blog'da ara..."
                    class="w-full border border-sand px-5 py-3 font-body text-sm
                              focus:outline-none focus:border-ink bg-transparent pr-12" />
                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-smoke" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607z" />
                </svg>
            </div>
        </div>

        {{-- Öne Çıkan Yazı --}}
        @if ($featuredPost && !$this->search)
            <div class="mb-16">
                <a href="{{ route('blog.show', $featuredPost->slug) }}"
                    class="group grid md:grid-cols-2 gap-0 overflow-hidden border border-sand/30
                          hover:border-ink transition-colors duration-300">
                    <div class="relative aspect-[4/3] overflow-hidden bg-blush-light">
                        @if ($featuredPost->image)
                            <img src="{{ asset('storage/' . $featuredPost->image) }}" alt="{{ $featuredPost->title }}"
                                class="w-full h-full object-cover transition-transform duration-700
                                        group-hover:scale-105" />
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light"></div>
                        @endif
                        <span
                            class="absolute top-4 left-4 bg-ink text-cream font-body
                                     text-[10px] tracking-widest uppercase px-3 py-1.5">
                            Öne Çıkan
                        </span>
                    </div>
                    <div class="flex flex-col justify-center p-8 md:p-12 bg-cream">
                        <p class="font-body text-xs tracking-widest uppercase text-smoke mb-4">
                            {{ $featuredPost->published_at?->format('d.m.Y') }}
                        </p>
                        <h2
                            class="font-display text-3xl md:text-4xl font-light text-ink mb-4 leading-snug
                                   group-hover:text-smoke transition-colors">
                            {{ $featuredPost->title }}
                        </h2>
                        @if ($featuredPost->excerpt)
                            <p class="font-body text-sm text-smoke leading-relaxed mb-6">
                                {{ $featuredPost->excerpt }}
                            </p>
                        @endif
                        <span
                            class="font-body text-xs tracking-widest2 uppercase text-ink
                                     underline hover:text-smoke transition-colors">
                            Devamını Oku →
                        </span>
                    </div>
                </a>
            </div>
        @endif

        {{-- Blog Grid --}}
        @if ($posts->isEmpty())
            <div class="text-center py-20">
                <p class="font-display text-3xl font-light text-smoke">Yazı bulunamadı</p>
                @if ($this->search)
                    <button wire:click="$set('search', '')"
                        class="mt-4 font-body text-xs tracking-widest2 uppercase border border-ink
                                   px-6 py-2 hover:bg-ink hover:text-cream transition-all">
                        Aramayı Temizle
                    </button>
                @endif
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($posts as $post)
                    <article class="group">
                        <a href="{{ route('blog.show', $post->slug) }}"
                            class="block relative aspect-[4/3] overflow-hidden bg-blush-light mb-5">
                            @if ($post->image)
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}"
                                    class="w-full h-full object-cover transition-transform
                                            duration-700 group-hover:scale-105" />
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light"></div>
                            @endif
                        </a>

                        <div>
                            <p class="font-body text-xs tracking-widest uppercase text-smoke mb-2">
                                {{ $post->published_at?->format('d.m.Y') }}
                            </p>
                            <h2 class="font-display text-2xl font-light text-ink mb-3 leading-snug">
                                <a href="{{ route('blog.show', $post->slug) }}"
                                    class="hover:text-smoke transition-colors">
                                    {{ $post->title }}
                                </a>
                            </h2>
                            @if ($post->excerpt)
                                <p class="font-body text-sm text-smoke leading-relaxed mb-4 line-clamp-3">
                                    {{ $post->excerpt }}
                                </p>
                            @endif
                            <a href="{{ route('blog.show', $post->slug) }}"
                                class="font-body text-xs tracking-widest2 uppercase text-ink
                                      hover:text-smoke transition-colors underline">
                                Devamını Oku →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($posts->hasPages())
                <div class="mt-16 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @endif
        @endif
    </div>

    @php
        $blogIndexSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            'name' => 'Eren Abiye Blog',
            'url' => route('blog.index'),
            'description' => 'Moda trendleri, abiye önerileri ve stil rehberleri',
        ];
    @endphp

    <script type="application/ld+json">{!! json_encode($blogIndexSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
</div>

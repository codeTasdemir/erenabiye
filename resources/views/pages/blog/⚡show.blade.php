<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\BlogPost;

new #[Layout('layouts.app')] class extends Component {
    public BlogPost $post;

    public function mount(string $slug): void
    {
        $this->post = BlogPost::where('slug', $slug)->where('is_published', true)->firstOrFail();
    }

    public function getTitle(): string
    {
        return ($this->post->meta_title ?: $this->post->title) . ' — Eren Abiye Blog';
    }

    public function with(): array
    {
        return [
            'related' => BlogPost::where('is_published', true)->where('id', '!=', $this->post->id)->latest('published_at')->take(3)->get(),
        ];
    }
};
?>

<div>
    {{-- Breadcrumb --}}
    <div class="bg-white border-b">
        <div class="max-w-screen-xl mx-auto  py-3">
            <nav class="flex items-center gap-1 font-body text-xs text-smoke flex-wrap">
                <a href="{{ route('home') }}" class="hover:text-ink transition-colors">Ana Sayfa</a>
                <span>/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-ink transition-colors">Blog</a>
                <span>/</span>
                <span class="text-ink line-clamp-1">{{ $post->title }}</span>
            </nav>
        </div>
    </div>

    {{-- Hero --}}
    @if ($post->image)
        <div class="relative h-[50vh] min-h-[400px] overflow-hidden bg-blush-light">
            <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}"
                class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-ink/30"></div>
            <div class="absolute inset-0 flex items-end">
                <div class="max-w-screen-xl mx-auto px-6 pb-12 w-full">
                    <p class="font-body text-xs tracking-widest uppercase text-cream/70 mb-3">
                        {{ $post->published_at?->format('d.m.Y') }}
                    </p>
                    <h1 class="font-display text-4xl md:text-6xl font-light text-cream max-w-3xl leading-snug">
                        {{ $post->title }}
                    </h1>
                </div>
            </div>
        </div>
    @else
        <div class="max-w-screen-xl mx-auto px-6 pt-16 pb-8">
            <p class="font-body text-xs tracking-widest uppercase text-smoke mb-4">
                {{ $post->published_at?->format('d m Y') }}
            </p>
            <h1 class="font-display text-4xl md:text-6xl font-light text-ink max-w-3xl leading-snug">
                {{ $post->title }}
            </h1>
        </div>
    @endif

    {{-- İçerik --}}
    <div class="max-w-screen-xl mx-auto px-6 py-16">
        <div class="max-w-3xl mx-auto">

            @if ($post->excerpt)
                <p
                    class="font-display text-xl text-smoke font-light leading-relaxed mb-10
                           pb-10 border-b border-sand/30">
                    {{ $post->excerpt }}
                </p>
            @endif

            <div
                class="prose prose-lg max-w-none font-body
                        prose-headings:font-display prose-headings:font-light
                        prose-a:text-ink prose-a:underline
                        prose-img:rounded-none">
                {!! $post->content !!}
            </div>

        </div>
    </div>

    {{-- İlgili Yazılar --}}
    @if ($related->count())
        <div class="border-t border-sand/30 bg-blush-light/20 py-16">
            <div class="max-w-screen-xl mx-auto px-6">
                <div class="text-center mb-10">
                    <p class="font-body text-xs tracking-widest2 uppercase text-smoke mb-3">Devamını Okuyun</p>
                    <h2 class="font-display text-3xl font-light text-ink">İlgili Yazılar</h2>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    @foreach ($related as $item)
                        <article class="group">
                            <a href="{{ route('blog.show', $item->slug) }}"
                                class="block relative aspect-[4/3] overflow-hidden bg-blush-light mb-4">
                                @if ($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}"
                                        class="w-full h-full object-cover transition-transform
                                                duration-700 group-hover:scale-105" />
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light"></div>
                                @endif
                            </a>
                            <p class="font-body text-xs text-smoke mb-2">
                                {{ $item->published_at?->format('d F Y') }}
                            </p>
                            <h3 class="font-display text-xl font-light text-ink">
                                <a href="{{ route('blog.show', $item->slug) }}"
                                    class="hover:text-smoke transition-colors">
                                    {{ $item->title }}
                                </a>
                            </h3>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @php
        $postUrl = route('blog.show', $post->slug);
        $postTitle = $post->meta_title ?: $post->title;
        $postDesc = $post->meta_description ?: $post->excerpt;
        $postImage = $post->image ? asset('storage/' . $post->image) : asset('images/og-default.jpg');

        $blogSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $postTitle,
            'description' => $postDesc,
            'image' => $postImage,
            'url' => $postUrl,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Eren Abiye',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Eren Abiye',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                ],
            ],
        ];
    @endphp

    <script type="application/ld+json">{!! json_encode($blogSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
</div>

<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Wishlist;

new class extends Component {
    public int $productId;
    public bool $inWishlist = false;
    public string $variant = 'absolute';

    public function mount(int $productId, string $variant = 'absolute'): void
    {
        $this->productId = $productId;
        $this->variant = $variant;

        $this->inWishlist = Wishlist::hasProduct($productId);
    }

    public function toggle(): void
    {
        if ($this->inWishlist) {
            Wishlist::forCurrentUser()->where('product_id', $this->productId)->delete();

            $this->inWishlist = false;
        } else {
            Wishlist::firstOrCreate(auth()->check() ? ['user_id' => auth()->id(), 'product_id' => $this->productId] : ['session_id' => session()->getId(), 'product_id' => $this->productId], auth()->check() ? ['session_id' => null] : ['user_id' => null]);

            $this->inWishlist = true;
        }

        $this->dispatch('wishlist-updated');
    }
};
?>

<button wire:click="toggle" wire:loading.attr="disabled"
    title="{{ $inWishlist ? 'Favorilerden çıkar' : 'Favorilere ekle' }}"
    class="{{ $variant }} top-2 right-2 z-10 w-8 h-8 flex items-center justify-center
           rounded-full transition-all duration-200
           {{ $inWishlist
               ? 'bg-white text-red-500 shadow-md'
               : 'bg-white/70 text-gray-400 hover:bg-white hover:text-red-400 shadow-sm' }}">

    {{-- Kalp ikonu --}}
    <svg wire:loading.remove class="w-4 h-4 transition-transform duration-150 {{ $inWishlist ? 'scale-110' : '' }}"
        viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>

    {{-- Loading spinner --}}
    <svg wire:loading class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
    </svg>
</button>

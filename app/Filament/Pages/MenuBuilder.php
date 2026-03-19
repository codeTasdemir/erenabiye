<?php

namespace App\Filament\Pages;

use App\Models\BlogPost;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\Page;
use Filament\Pages\Page as FilamentPage;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class MenuBuilder extends FilamentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Menü Oluşturucu';
    protected static ?string $title           = 'Menü Oluşturucu';
    protected static ?int    $navigationSort  = 1;
    protected string $view = 'filament.pages.menu-builder';


    public static function getNavigationGroup(): string
    {
        return 'İçerik';
    }

    // Seçili menü
    public ?int $selectedMenuId = null;

    // Menü öğeleri (nested JSON)
    public array $menuItems = [];

    // Yeni öğe formu
    public string $newLabel    = '';
    public string $newType     = 'custom';
    public string $newUrl      = '';
    public ?int   $newLinkableId = null;
    public string $newTarget   = '_self';

    public function mount(): void
    {
        $firstMenu = Menu::first();
        if ($firstMenu) {
            $this->selectedMenuId = $firstMenu->id;
            $this->loadMenuItems();
        }
    }

    public function updatedSelectedMenuId(): void
    {
        $this->loadMenuItems();
    }

    public function loadMenuItems(): void
    {
        if (!$this->selectedMenuId) {
            $this->menuItems = [];
            return;
        }

        $items = MenuItem::where('menu_id', $this->selectedMenuId)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with(['children' => fn($q) => $q->orderBy('sort_order')])
            ->get();

        $this->menuItems = $items->map(fn($item) => [
            'id'       => $item->id,
            'label'    => $item->label,
            'url'      => $item->url,
            'type'     => $item->type,
            'target'   => $item->target,
            'is_active' => $item->is_active,
            'children' => $item->children->map(fn($child) => [
                'id'       => $child->id,
                'label'    => $child->label,
                'url'      => $child->url,
                'type'     => $child->type,
                'target'   => $child->target,
                'is_active' => $child->is_active,
                'children' => [],
            ])->toArray(),
        ])->toArray();
    }

    public function addItem(): void
    {
        if (empty($this->newLabel)) {
            Notification::make()
                ->title('Başlık zorunludur!')
                ->danger()
                ->send();
            return;
        }

        $url = $this->newUrl;

        // Kategori veya sayfa seçildiyse URL'yi otomatik oluşturma
        if ($this->newType === 'category' && $this->newLinkableId) {
            $category = Category::find($this->newLinkableId);
            $url = $category ? route('category', $category->slug) : '#';
        } elseif ($this->newType === 'page' && $this->newLinkableId) {
            $page = Page::find($this->newLinkableId);
            $url = $page ? '/' . $page->slug : '#';
        } elseif ($this->newType === 'blog' && $this->newLinkableId) {
            $blog = BlogPost::find($this->newLinkableId);
            $url = $blog ? route('blog.show', $blog->slug) : route('blog.index');
        }

        $maxOrder = MenuItem::where('menu_id', $this->selectedMenuId)
            ->whereNull('parent_id')
            ->max('sort_order') ?? 0;

        $item = MenuItem::create([
            'menu_id'     => $this->selectedMenuId,
            'parent_id'   => null,
            'label'       => $this->newLabel,
            'url'         => $url,
            'type'        => $this->newType,
            'linkable_id' => $this->newLinkableId,
            'target'      => $this->newTarget,
            'sort_order'  => $maxOrder + 1,
            'is_active'   => true,
        ]);

        // Reset form
        $this->newLabel      = '';
        $this->newType       = 'custom';
        $this->newUrl        = '';
        $this->newLinkableId = null;
        $this->newTarget     = '_self';
        $this->loadMenuItems();
        $this->dispatch('menuUpdated');

        $this->loadMenuItems();

        Notification::make()
            ->title('Öğe eklendi!')
            ->success()
            ->send();
    }

    public function removeItem(int $id): void
    {
        MenuItem::where('id', $id)->orWhere('parent_id', $id)->delete();
        $this->loadMenuItems();
    }

    public function toggleActive(int $id): void
    {
        $item = MenuItem::find($id);
        if ($item) {
            $item->update(['is_active' => !$item->is_active]);
            $this->loadMenuItems();
            $this->dispatch('menuUpdated');
        }
    }

    public function updateOrder(array $order): void
    {
        foreach ($order as $sortIndex => $itemData) {
            MenuItem::where('id', $itemData['id'])->update([
                'sort_order' => $sortIndex,
                'parent_id'  => null,
            ]);

            if (!empty($itemData['children'])) {
                foreach ($itemData['children'] as $childIndex => $childData) {
                    MenuItem::where('id', $childData['id'])->update([
                        'sort_order' => $childIndex,
                        'parent_id'  => $itemData['id'],
                        'menu_id'    => $this->selectedMenuId,
                    ]);
                }
            }
        }

        $this->loadMenuItems();
        $this->dispatch('menuUpdated');
        Notification::make()->title('Sıralama kaydedildi!')->success()->send();
    }

    public function getMenusProperty()
    {
        return Menu::all();
    }

    public function getCategoriesProperty()
    {
        return Category::where('is_active', true)->pluck('name', 'id');
    }
    public function getBlogPostsProperty()
    {
        return BlogPost::where('is_published', true)->pluck('title', 'id');
    }

    public function getPagesProperty()
    {
        return Page::pluck('title', 'id');
    }

    public function getItemsJson(): string
    {
        return json_encode($this->menuItems);
    }
}

<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class CategoriesList extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public array $sortableCategories = [];
    public function getView(): string
    {
        return 'filament.categories.pages.list-categories';
    }

    public function mount(): void
    {
        parent::mount();
        $this->loadSortable();
    }

    public function loadSortable(): void
    {
        $parents = Category::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $this->sortableCategories = $parents->map(function ($parent) {
            return [
                'id'        => $parent->id,
                'name'      => $parent->name,
                'is_active' => $parent->is_active,
                'children'  => Category::where('parent_id', $parent->id)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn($c) => [
                        'id'        => $c->id,
                        'name'      => $c->name,
                        'is_active' => $c->is_active,
                    ])->toArray(),
            ];
        })->toArray();
    }

    public function updateOrder(array $order): void
    {
        foreach ($order as $i => $item) {
            Category::where('id', $item['id'])->update([
                'sort_order' => $i + 1,
                'parent_id'  => null,
            ]);
            foreach ($item['children'] ?? [] as $j => $child) {
                Category::where('id', $child['id'])->update([
                    'sort_order' => $j + 1,
                    'parent_id'  => $item['id'],
                ]);
            }
        }

        $this->loadSortable();

        Notification::make()->title('Sıralama kaydedildi!')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

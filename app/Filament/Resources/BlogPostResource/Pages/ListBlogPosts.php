<?php
namespace App\Filament\Resources\BlogPostResource\Pages;
use App\Filament\Resources\BlogPostResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListBlogPosts extends ListRecords
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

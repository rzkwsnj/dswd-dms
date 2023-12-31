<?php

namespace Konnco\FilamentImport\Tests\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;
use Konnco\FilamentImport\Tests\Resources\Models\Post;
use Konnco\FilamentImport\Tests\Resources\PostResource;

class AlternativeColumnsNamesList extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make('import')
                ->fields([
                    ImportField::make('title')->alternativeColumnNames(['Title']),
                    ImportField::make('slug')->alternativeColumnNames(['Slug']),
                    ImportField::make('body')->alternativeColumnNames(['Body']),
                ])
                ->handleRecordCreation(function ($data) {
                    return Post::create($data);
                }),
        ];
    }
}

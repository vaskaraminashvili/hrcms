<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ManageReports extends Page
{
    protected static ?string $slug = 'reports';

    protected string $view = 'filament.pages.manage-reports';

    public function table(Table $table): Table
    {
        return $table
            ->query()
            ->columns([
                TextColumn::make('created_at')->date(),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                DatePicker::make('created_at'),
            ], layout: FiltersLayout::AboveContentCollapsible);
        // Options: AboveContent, BelowContent, AboveContentCollapsible
    }
}

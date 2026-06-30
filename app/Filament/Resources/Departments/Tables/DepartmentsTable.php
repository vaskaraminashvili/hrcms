<?php

namespace App\Filament\Resources\Departments\Tables;

use App\Enums\DepartmentStatus;
use App\Enums\DepartmentType;
use App\Models\Department;
use App\Services\DepartmentDescendantTypeCountService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('parent'))
            ->defaultSort(function (Builder $query, string $direction, mixed $livewire): Builder {
                if (! $livewire instanceof HasTable || filled($livewire->getTableSortColumn())) {
                    return $query;
                }

                $model = $query->getModel();

                return $query
                    ->orderBy($model->qualifyColumn('parent_id'))
                    ->orderBy($model->qualifyColumn('order'))
                    ->orderBy($model->qualifyColumn('name'));
            })
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('order')
                    ->label(__('filament.resources.departments.order'))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->limit(60)
                    ->url(
                        fn (Department $record): string => route('filament.admin.resources.positions.index', [
                            'filters[department_id][value]' => $record->getKey(),
                            'filters[hide_scheduled_dismissals][isActive]' => true,
                        ])
                    )
                    ->openUrlInNewTab()
                    ->searchable()
                    ->sortable(),
                // show parent name if parent is not null
                TextColumn::make('parent.name')
                    ->label('ზემდგომი დეპარტამენტი')
                    ->formatStateUsing(function (string $state, Department $record): string {
                        return $record->parent?->name ?? 'Unknown';
                    }),

                TextColumn::make('type')
                    ->label(__('filament.type'))
                    ->badge()
                    ->color('info')
                    ->alignment(Alignment::End)
                    ->formatStateUsing(fn (?DepartmentType $state): ?string => $state?->getLabel())
                    ->sortable(),
                TextColumn::make('descendant_type_counts')
                    ->label('')
                    ->html()
                    ->alignment(Alignment::End)
                    ->state(fn (Department $record): HtmlString => self::descendantTypeCountsBadgesHtml($record)),
                IconColumn::make('status')
                    ->label(__('filament.status'))
                    ->alignment(Alignment::End)
                    ->icon(fn (?DepartmentStatus $state): string => $state === DepartmentStatus::ACTIVE
                        ? 'heroicon-o-check-circle'
                        : 'heroicon-o-archive-box')
                    ->color(fn (?DepartmentStatus $state): string => $state === DepartmentStatus::ACTIVE
                        ? 'success'
                        : 'warning')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function descendantTypeCountsBadgesHtml(Department $department): HtmlString
    {
        $badges = app(DepartmentDescendantTypeCountService::class)->getCachedDescendantTypeCountsPayload($department);

        if ($badges === []) {
            return new HtmlString('');
        }

        $html = '<span class="flex gap-1 flex-wrap">';
        foreach ($badges as $badge) {
            $html .= '<span class="fi-badge rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset '.e($badge['classes']).'">'
                .e($badge['label']).': '.e((string) $badge['count'])
                .'</span>';
        }
        $html .= '</span>';

        return new HtmlString($html);
    }
}

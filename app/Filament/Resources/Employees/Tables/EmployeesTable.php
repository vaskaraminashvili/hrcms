<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Enums\EmployeeStatusEnum;
use App\Filament\Resources\Departments\Fields\DepartmentTextField;
use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'appointmentPositions.department',
                'appointmentPositions.place',
            ]))
            ->columns([
                ImageColumn::make('employee_image')
                    ->getStateUsing(fn (Employee $record) => $record->employeeImageUrl())
                    ->circular()
                    ->label(''),
                TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->formatStateUsing(function (string $state, Employee $record): string {
                        return $record->name.' '.$record->surname;
                    })
                    ->searchable(['name', 'surname']),

                TextColumn::make('name_eng')
                    ->label(__('filament.name_eng'))
                    ->formatStateUsing(function (string $state, Employee $record): string {
                        return $record->name_eng.' '.$record->surrname_eng;
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(['name_eng', 'surrname_eng']),

                TextColumn::make('personal_number')
                    ->badge()
                    ->label(__('filament.personal_number_short'))
                    ->formatStateUsing(function (string $state, Employee $record): string {
                        return $record->personal_number;
                    })
                    ->color('success')
                    ->copyable()
                    ->copyMessage(__('filament.copied'))
                    ->copyMessageDuration(1500)
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label(__('filament.birth_date_placeholder'))
                    ->date()
                    ->sortable(),
                TextColumn::make('appointment_positions_department_summary')
                    ->label(__('filament.department_id'))
                    ->width('400px')
                    ->wrap()
                    ->html()
                    ->state(fn (Employee $record): HtmlString => self::appointmentPositionsBadgesHtml($record, 'department', asBadge: false, col_width: 330)),
                TextColumn::make('appointment_positions_type_summary')
                    ->label(__('filament.position_type'))
                    ->html()
                    ->state(fn (Employee $record): HtmlString => self::appointmentPositionsBadgesHtml($record, 'type')),
                TextColumn::make('appointment_positions_place_summary')
                    ->label(__('filament.place_id'))
                    ->html()
                    ->state(fn (Employee $record): HtmlString => self::appointmentPositionsBadgesHtml($record, 'place'))
                    ->description(fn (Employee $record): string => self::appointmentPositionsDateEndDescription($record)),
                TextColumn::make('appointment_positions_status_summary')
                    ->label(__('filament.status'))
                    ->html()
                    ->state(fn (Employee $record): HtmlString => self::appointmentPositionsBadgesHtml($record, 'status')),

                TextColumn::make('positions_count')
                    ->label(__('filament.positions_count'))
                    ->alignCenter()
                    ->icon('heroicon-o-briefcase')
                    ->counts(['appointmentPositions as positions_count'])
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                // TextColumn::make('email')
                //     ->label(__('filament.email'))
                //     ->searchable(),

                TextColumn::make('status')
                    ->label(__('filament.status'))
                    ->badge()
                    ->color(fn (Employee $record): string => $record->status->getColor())
                    ->icon(fn (Employee $record): string => $record->status->getIcon())
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('gender')
                    ->label(__('filament.gender'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('citizenship')
                    ->label(__('filament.citizenship'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('education')
                    ->label(__('filament.education'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('degree')
                    ->label(__('filament.degree'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address_details')
                    ->label(__('filament.address'))
                    ->formatStateUsing(function (?array $state): string {
                        if (! is_array($state)) {
                            return '';
                        }

                        $parts = array_filter([
                            $state['address_physical'] ?? null,
                            $state['address_jurisdiction'] ?? null,
                            $state['en_address_physical'] ?? null,
                            $state['en_address_jurisdiction'] ?? null,
                        ]);

                        return implode(' · ', $parts);
                    })
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->where('address_details', 'like', '%'.$search.'%');
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(__('filament.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('filament.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.status'))
                    ->options(EmployeeStatusEnum::class)
                    ->default(EmployeeStatusEnum::ACTIVE->value),
                SelectFilter::make('employee_image')
                    ->label(__('filament.employee_image'))
                    ->options([
                        'with' => __('filament.with_image'),
                        'without' => __('filament.without_image'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'with' => $query->whereHas('media', function (Builder $mediaQuery): void {
                                $mediaQuery->where('collection_name', 'employee_image');
                            }),
                            'without' => $query->whereDoesntHave('media', function (Builder $mediaQuery): void {
                                $mediaQuery->where('collection_name', 'employee_image');
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(''),
                // when on tab deleted show restore action only
                RestoreAction::make()
                    ->visible(fn (Employee $record): bool => $record->trashed()),
            ], RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->filtersFormColumns(2);
    }

    /**
     * Renders each appointment position as a list item (badge or plain text per column).
     */
    private static function appointmentPositionsBadgesHtml(Employee $record, string $column, bool $asBadge = true, int $col_width = 0): HtmlString
    {
        $positions = $record->appointmentPositions;

        if ($positions->isEmpty()) {
            return new HtmlString('');
        }

        $listLimit = 1;
        $total = $positions->count();
        $hiddenCount = max(0, $total - $listLimit);

        $items = [];
        foreach ($positions->take($listLimit) as $position) {
            $departmentLabel = $position->department?->name ?? '—';
            $placeLabel = $position->place?->name ?? '—';
            $typeLabel = $position->position_type?->getLabel() ?? '';
            $statusLabel = $position->status?->getLabel() ?? '';

            $typeColor = self::filamentBadgeColorKey($position->position_type?->getColor());
            $statusColor = self::filamentBadgeColorKey($position->status?->getColor());
            $contentHtml = match ($column) {
                'department' => $asBadge
                    ? self::filamentBadgeHtml($departmentLabel, 'gray')
                    : self::filamentTextHtml($departmentLabel),
                'type' => $asBadge
                    ? self::filamentBadgeHtml($typeLabel, $typeColor)
                    : self::filamentTextHtml($typeLabel),
                'status' => $asBadge
                    ? self::filamentBadgeHtml($statusLabel, $statusColor)
                    : self::filamentTextHtml($statusLabel),
                'place' => $asBadge
                    ? self::filamentBadgeHtml($placeLabel, 'gray')
                    : self::filamentTextHtml($placeLabel),
                default => '',
            };

            $items[] = '<li class="fi-ta-text-item">'.$contentHtml.'</li>';
        }
        $width = $col_width > 0 ? ' style="width: '.$col_width.'px !important;"' : '';
        $html = '<ul class="list-none space-y-1"'.$width.'>'.implode('', $items).'</ul>';

        if ($hiddenCount > 0) {
            $html .= '<p class="fi-ta-text-description mt-1 text-xs">'
                .e(trans_choice('filament-tables::table.columns.text.more_list_items', $hiddenCount))
                .'</p>';
        }

        return new HtmlString($html);
    }

    private static function appointmentPositionsDateEndDescription(Employee $record): string
    {
        $positions = $record->appointmentPositions;

        if ($positions->isEmpty()) {
            return '';
        }

        return $positions
            ->take(3)
            ->map(fn ($position): string => $position->date_end?->format('d.m.Y') ?? 'N/A')
            ->implode(' · ');
    }

    /**
     * @param  string|array<string, mixed>|null  $color
     */
    private static function filamentBadgeColorKey(mixed $color): string
    {
        if (is_string($color) && array_key_exists($color, DepartmentTextField::BADGE_COLOR_CLASSES)) {
            return $color;
        }

        return 'gray';
    }

    private static function filamentBadgeHtml(string $label, string $colorKey): string
    {
        $classes = DepartmentTextField::BADGE_COLOR_CLASSES[$colorKey]
            ?? DepartmentTextField::BADGE_COLOR_CLASSES['gray'];

        return '<span class="fi-badge rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset '.$classes.'">'.e($label).'</span>';
    }

    private static function filamentTextHtml(string $label): string
    {
        return '<span class="text-xs whitespace-normal break-words">'.e($label).'</span>';
    }
}

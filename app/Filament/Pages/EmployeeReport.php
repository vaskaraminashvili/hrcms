<?php

namespace App\Filament\Pages;

use App\Enums\EmployeeStatusEnum;
use App\Enums\Gender;
use App\Filament\Exports\EmployeeExporter;
use App\Models\Employee;
use BackedEnum;
use App\Support\DepartmentBadgeColors;
use Carbon\Carbon;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use UnitEnum;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\ImageColumn;



class EmployeeReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'reports/employees';

    protected static ?string $navigationLabel = 'Employee Report';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected string $view = 'filament.pages.employee-report';

    public function table(Table $table): Table
    {
        return $table
        ->query(Employee::query()->with([
            'appointmentPositions.department.parent',
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
            TextColumn::make('mobile_number')
                ->label(__('filament.mobile_number_placeholder'))
                ->formatStateUsing(function (string $state, Employee $record): string {
                    return $record->mobile_number;
                })
                ->color('info')
                ->copyable()
                ->copyMessage(__('filament.copied'))
                ->copyMessageDuration(1500)
                ->searchable(),
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

                // ── Add more columns below ─────────────────────────────────────
                //
                // Relationship column via dot notation:
                //     TextColumn::make('user.email'),
                //
                // Aggregate column:
                //     TextColumn::make('positions_count')->counts('positions'),
                // ───────────────────────────────────────────────────────────────
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.status'))
                    ->options(EmployeeStatusEnum::class)
                    ->multiple(),
                SelectFilter::make('gender')
                    ->label(__('filament.gender'))
                    ->options(Gender::class),

                // Relationship filter (dot notation walks nested relationships).
                SelectFilter::make('department')
                    ->label(__('filament.department_id'))
                    ->relationship('positions.department', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                // Custom form filter: date range example.
                Filter::make('birth_date')
                    ->form([
                        DatePicker::make('birth_date_from'),
                        DatePicker::make('birth_date_until'),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['birth_date_from'], fn (Builder $query, $date): Builder => $query->whereDate('birth_date', '>=', $date))
                            ->when($data['birth_date_until'], fn (Builder $query, $date): Builder => $query->whereDate('birth_date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['birth_date_from'] && ! $data['birth_date_until']) {
                            return null;
                        }

                        return collect([
                            $data['birth_date_from'] ? 'From '.Carbon::parse($data['birth_date_from'])->toFormattedDateString() : null,
                            $data['birth_date_until'] ? 'Until '.Carbon::parse($data['birth_date_until'])->toFormattedDateString() : null,
                        ])->filter()->implode(' ');
                    }),

                TrashedFilter::make(),

                // ── Add more filters below ─────────────────────────────────────
                //
                // Custom form filter with a query callback:
                //     Filter::make('has_active_position')
                //         ->query(fn (Builder $query): Builder => $query->whereHas('positions', fn (Builder $q) => $q->activePositions())),
                //
                // Relationship select filter:
                //     SelectFilter::make('place')
                //         ->relationship('positions.place', 'name')
                //         ->searchable()
                //         ->preload(),
                // ───────────────────────────────────────────────────────────────
                ],layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->headerActions([
                // Exports every row matching the current filters + search
                // (the export query is built from the filtered table query).
                ExportAction::make()
                    ->exporter(EmployeeExporter::class)
                    ->formats([ExportFormat::Xlsx])
                    ->after(fn () => $this->downloadLatestXlsxExport()),
            ])
            ->toolbarActions([
                // Exports only the selected rows.
                ExportBulkAction::make()
                    ->exporter(EmployeeExporter::class)
                    ->formats([ExportFormat::Xlsx])
                    ->after(fn () => $this->downloadLatestXlsxExport()),
            ])
            ->defaultSort('id', 'desc');
    }

    /**
     * The exporter runs on the sync queue connection, so by the time the
     * action's "after" hook fires, the export files already exist on disk.
     * Redirecting to the signed download URL triggers the XLSX download
     * immediately, without requiring a click on the notification button.
     */
    protected function downloadLatestXlsxExport(): void
    {
        $export = Export::query()
            ->where('user_id', Auth::id())
            ->whereNotNull('completed_at')
            ->latest('id')
            ->first();

        if (! $export) {
            return;
        }

        $this->redirect(url(URL::signedRoute('filament.exports.download', [
            'authGuard' => Filament::getAuthGuard(),
            'export' => $export,
            'format' => ExportFormat::Xlsx,
        ], absolute: false)));
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
            // if department -> show_parent is 1 then show parent name else show department name
            if ($position->department?->show_parent == 1) {
                $parentName = $position->department?->parent?->name;
                $departmentName = $position->department?->name;
                $departmentLabel = $parentName.' &#8594; '.$departmentName;
                $departmentLabelIsHtml = true;
            } else {
                $departmentLabel = $position->department?->name ?? '—';
                $departmentLabelIsHtml = false;
            }
            $placeLabel = $position->place?->name ?? '—';
            $typeLabel = $position->position_type?->getLabel() ?? '';
            $statusLabel = $position->status?->getLabel() ?? '';

            $typeColor = self::filamentBadgeColorKey($position->position_type?->getColor());
            $statusColor = self::filamentBadgeColorKey($position->status?->getColor());
            $contentHtml = match ($column) {
                'department' => $asBadge
                    ? self::filamentBadgeHtml($departmentLabel, 'gray', raw: $departmentLabelIsHtml)
                    : self::filamentTextHtml($departmentLabel, raw: $departmentLabelIsHtml),
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
        if (is_string($color) && array_key_exists($color, DepartmentBadgeColors::BADGE_COLOR_CLASSES)) {
            return $color;
        }

        return 'gray';
    }

        private static function filamentBadgeHtml(string $label, string $colorKey, bool $raw = false): string
    {
        $classes = DepartmentBadgeColors::BADGE_COLOR_CLASSES[$colorKey]
            ?? DepartmentBadgeColors::BADGE_COLOR_CLASSES['gray'];

        $content = $raw ? $label : e($label);

        return '<span class="fi-badge rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset '.$classes.'">'.$content.'</span>';
    }

    private static function filamentTextHtml(string $label, bool $raw = false): string
    {
        $content = $raw ? $label : e($label);

        return '<span class="text-xs whitespace-normal break-words">'.$content.'</span>';
    }
}

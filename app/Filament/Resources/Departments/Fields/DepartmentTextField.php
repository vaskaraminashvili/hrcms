<?php

namespace App\Filament\Resources\Departments\Fields;

use App\Models\Department;
use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Tables\Columns\Concerns\CanFormatState;
use Filament\Tables\Columns\Concerns\HasTooltip;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Openplain\FilamentTreeView\Fields\TextField;

class DepartmentTextField extends TextField
{
    use EvaluatesClosures;
    use HasTooltip;

    /**
     * CSS hook for per-depth tree styling ({@see resources/css/filament/admin/custom.css}).
     */
    public const TREE_NAME_CLASS = 'fi-tree-dept-name';

    protected Model|array|null $tooltipEvaluationRecord = null;

    protected bool $applyDepartmentColor = false;

    protected bool|Closure $isBadge = false;

    protected string|Closure|null $badgeColor = null;

    /**
     * Character limit (Filament {@see CanFormatState::limit} API).
     * When set, takes precedence over the tree TextField's int-only {@see TextField::$characterLimit}.
     */
    protected int|Closure|null $characterLimitLength = null;

    /**
     * Suffix when the value is truncated (e.g. '...').
     */
    protected string|Closure|null $characterLimitEnd = null;

    /** @var array<string, string> */
    public const BADGE_COLOR_CLASSES = [
        'gray' => 'fi-color fi-color-gray fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'primary' => 'fi-color fi-color-primary fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'success' => 'fi-color fi-color-success fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'warning' => 'fi-color fi-color-warning fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'danger' => 'fi-color fi-color-danger fi-text-color-700 dark:fi-text-color-300 fi-badge',
        'info' => 'fi-color fi-color-info fi-text-color-700 dark:fi-text-color-300 fi-badge',
    ];

    /**
     * Apply department color from the record's color enum.
     * Uses text-{color}-500 Tailwind classes.
     */
    public function withDepartmentColor(bool $apply = true): static
    {
        $this->applyDepartmentColor = $apply;

        return $this;
    }

    /**
     * Render the value as a Filament-style badge pill.
     */
    public function badge(bool|Closure $condition = true): static
    {
        $this->isBadge = $condition;

        return $this;
    }

    /**
     * Set the badge color. Accepts a color name string or a Closure receiving (state, record).
     */
    public function badgeColor(string|Closure $color): static
    {
        $this->badgeColor = $color;

        return $this;
    }

    /**
     * Limit the displayed string length, matching Filament table columns
     * ({@see CanFormatState::limit}).
     *
     * @param  int|Closure|null  $length  Pass null to disable truncation from this API.
     */
    public function limit(int|Closure|null $length = 100, string|Closure|null $end = '...'): static
    {
        $this->characterLimitLength = $length;
        $this->characterLimitEnd = $end;

        if (is_int($length)) {
            parent::limit($length);
        } else {
            $this->characterLimit = null;
        }

        return $this;
    }

    /**
     * Render the text field for the given record.
     * Supports HtmlString from formatStateUsing for raw HTML output.
     * When withDepartmentColor() is used, applies color from Department model.
     * When badge() is used, wraps the value in a Filament badge pill.
     */
    public function render(Model|array $record): string
    {
        $this->evaluationIdentifier = 'field';

        $state = $this->getFieldState($record);
        $displayState = $state;

        if ($this->formatStateUsing) {
            $formatted = ($this->formatStateUsing)($state, $record);

            if ($formatted instanceof HtmlString) {
                return $this->wrapCellTooltip(
                    $this->maybeWrapTreeNameColumn((string) $formatted),
                    $state,
                    $record,
                );
            }

            $displayState = $formatted;
        }

        $isBadge = $this->isBadge instanceof Closure
            ? (bool) ($this->isBadge)($state, $record)
            : $this->isBadge;

        if ($isBadge) {
            return $this->wrapCellTooltip(
                $this->maybeWrapTreeNameColumn($this->renderBadge($displayState, $state, $record)),
                $state,
                $record,
            );
        }

        $color = $this->resolveDepartmentColor($record);

        if ($color !== null) {
            $plain = (string) $displayState;
            $limited = e($this->applyCharacterLimit($plain, $state, $record));

            return $this->wrapCellTooltip(
                $this->maybeWrapTreeNameColumn("<span class='text-sm' style='color: {$color};'>{$limited}</span>"),
                $state,
                $record,
            );
        }

        $savedCharacterLimit = $this->characterLimit;
        $this->characterLimit = null;

        $savedFormatStateUsing = $this->formatStateUsing;

        $this->formatStateUsing = $savedFormatStateUsing !== null
            ? function (mixed $s, Model|array $rec) use ($savedFormatStateUsing): string {
                $base = ($savedFormatStateUsing)($s, $rec);

                return $this->applyCharacterLimit((string) $base, $s, $rec);
            }
        : function (mixed $s, Model|array $rec): string {
            return $this->applyCharacterLimit((string) $s, $s, $rec);
        };

        $html = parent::render($record);

        $this->formatStateUsing = $savedFormatStateUsing;
        $this->characterLimit = $savedCharacterLimit;

        return $this->wrapCellTooltip(
            $this->maybeWrapTreeNameColumn($html),
            $state,
            $record,
        );
    }

    /**
     * @param  mixed  $displayState  Value shown in the badge (respects {@see formatStateUsing}).
     * @param  mixed  $state  Raw attribute state (for limit / color closure arguments).
     */
    protected function renderBadge(mixed $displayState, mixed $state, Model|array $record): string
    {
        $raw = (string) $displayState;
        $label = e($this->applyCharacterLimit($raw, $state, $record));

        $resolvedColor = $this->badgeColor instanceof Closure
            ? ($this->badgeColor)($state, $record)
            : ($this->badgeColor ?? 'gray');

        $colorClasses = self::BADGE_COLOR_CLASSES[$resolvedColor] ?? self::BADGE_COLOR_CLASSES['gray'];

        return '<span class="fi-badge rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset '.$colorClasses.'">'.$label.'</span>';
    }

    /**
     * Wrap the `name` column so depth-based colors can target it without relying on field order.
     */
    protected function maybeWrapTreeNameColumn(string $html): string
    {
        if ($this->getName() !== 'name') {
            return $html;
        }

        return '<span class="'.self::TREE_NAME_CLASS.'">'.$html.'</span>';
    }

    /**
     * Matches Filament table text column tooltips (same Alpine directive as TextColumn).
     *
     * @see TextColumn
     */
    protected function wrapCellTooltip(string $html, mixed $state, Model|array $record): string
    {
        $this->tooltipEvaluationRecord = $record;

        try {
            $tooltip = filled($state)
                ? $this->getTooltip($state)
                : $this->getEmptyTooltip();
        } finally {
            $this->tooltipEvaluationRecord = null;
        }

        if (! filled($tooltip)) {
            return $html;
        }

        $attributes = (new ComponentAttributeBag)
            ->merge([
                'x-tooltip' => '{
                content: '.Js::from($tooltip).',
                theme: $store.theme,
                allowHTML: '.Js::from($tooltip instanceof Htmlable).',
            }',
            ], escape: false);

        return '<span '.$attributes->toHtml().'>'.$html.'</span>';
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'record' => $this->tooltipEvaluationRecord !== null ? [$this->tooltipEvaluationRecord] : [],
            default => [],
        };
    }

    protected function resolveDepartmentColor(Model|array $record): ?string
    {
        if (! $this->applyDepartmentColor || ! $record instanceof Department) {
            return null;
        }

        return $record->color?->color() ?? 'gray';
    }

    protected function applyCharacterLimit(string $value, mixed $state, Model|array $record): string
    {
        $limit = $this->resolveCharacterLimit($state, $record);

        if ($limit === null) {
            return $value;
        }

        return Str::limit($value, $limit, $this->resolveCharacterLimitEnd($state, $record));
    }

    protected function resolveCharacterLimit(mixed $state, Model|array $record): ?int
    {
        if ($this->characterLimitLength !== null) {
            $length = $this->characterLimitLength instanceof Closure
                ? ($this->characterLimitLength)($state, $record)
                : $this->characterLimitLength;

            return $length !== null ? (int) $length : null;
        }

        return $this->characterLimit;
    }

    protected function resolveCharacterLimitEnd(mixed $state, Model|array $record): string
    {
        $end = $this->characterLimitEnd ?? '...';

        if ($end instanceof Closure) {
            $end = ($end)($state, $record);
        }

        return (string) ($end ?? '...');
    }
}

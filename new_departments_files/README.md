# Department Tree – Custom Filament View

A modernized tree UI for the `DepartmentResource` built with plain Blade
components + Alpine.js, replacing the `openplain/filament-tree-view` widget.

---

## File map

```
filament-tree/
├── views/
│   └── filament/resources/departments/
│       ├── pages/
│       │   └── tree-departments.blade.php   ← main page view
│       └── components/
│           └── tree-node.blade.php          ← recursive node component
│
├── scss/
│   ├── department-tree.scss                 ← SCSS entry point (import this)
│   ├── _tokens.scss                         ← design tokens / variables
│   ├── _toolbar.scss                        ← expand/collapse toolbar
│   ├── _tree.scss                           ← tree wrapper
│   ├── _node.scss                           ← row, chevron, actions
│   ├── _badges.scss                         ← type badge + count badges
│   └── _dark.scss                           ← dark-mode overrides
│
├── php/
│   ├── Pages/
│   │   └── TreeDepartments.php              ← Filament page class
│   └── Fields/
│       ├── DepartmentTextField.php          ← base text field
│       ├── DepartmentDescendantTypeCountField.php
│       └── DepartmentStatusIconField.php
│
└── lang/
    ├── en/filament.php                      ← English strings
    └── ka/filament.php                      ← Georgian strings
```

---

## Installation

### 1 – Copy views

Copy the `views/filament/` tree into your `resources/views/` directory:

```
resources/views/filament/resources/departments/
```

Filament will automatically discover Blade components under
`resources/views/components/`.

### 2 – Register the Blade component

In `app/Providers/AppServiceProvider.php` (or a dedicated `ViewServiceProvider`):

```php
use Illuminate\Support\Facades\Blade;

Blade::component(
    'filament.resources.departments.components.tree-node',
    \App\View\Components\Departments\TreeNode::class   // or use anonymous component
);
```

If you use anonymous Blade components (no backing class needed), just ensure
the file lives at:

```
resources/views/components/filament/resources/departments/components/tree-node.blade.php
```

### 3 – Compile SCSS

Import the entry file in your `resources/css/filament/admin/theme.css`
(or equivalent Filament theme file):

```scss
@use '../../scss/department-tree';
```

Then rebuild assets:

```bash
npm run build
# or
php artisan filament:assets
```

### 4 – Register the page

`DepartmentResource::getPages()` already maps `'index'` to `TreeDepartments`.
Ensure the class namespace matches your app:

```php
'index' => Pages\TreeDepartments::route('/'),
```

### 5 – Language strings

Merge `lang/en/filament.php` and `lang/ka/filament.php` into your existing
`lang/{locale}/filament.php` files, or publish and merge via:

```bash
php artisan vendor:publish --tag=filament-panels-translations
```

---

## Customisation

### Depth accent colours

Edit `$depth-colors` in `_tokens.scss`:

```scss
$depth-colors: (
    0: #534AB7,   // root level
    1: #AFA9EC,   // level 1
    2: #CECBF6,   // level 2
    3: #5DCAA5,   // level 3+
);
```

### Badge colour palette

`DepartmentDescendantTypeCountService::getCachedDescendantTypeCountsPayload()`
must return items shaped like:

```php
[
    ['label' => 'ცენტრი',       'count' => 33, 'color' => 'teal'],
    ['label' => 'კლინიკა',      'count' => 16, 'color' => 'coral'],
    ['label' => 'დეპარტამენტი', 'count' => 77, 'color' => 'blue'],
    // …
]
```

Colour keys must match the `$badge-palettes` map in `_tokens.scss`.

### Max depth

The tree renders all descendants. To enforce a visual max depth you can
add a `@if ($depth < 6)` guard in `tree-node.blade.php` around the
`<x-filament.resources.departments.components.tree-node>` recursive call.

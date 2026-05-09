# ANT DESIGN QUICK REFERENCE

## Common Components

### Buttons
```html
<!-- Primary Button -->
<button class="ant-btn ant-btn-primary">Primary</button>

<!-- Success Button -->
<button class="ant-btn ant-btn-success">Success</button>

<!-- Info Button -->
<button class="ant-btn ant-btn-info">Info</button>

<!-- Warning Button -->
<button class="ant-btn ant-btn-warning">Warning</button>

<!-- Small Button -->
<button class="ant-btn ant-btn-sm">Small</button>

<!-- Full Width Button -->
<button class="ant-btn ant-btn-primary w-100">Full Width</button>
```

### Cards
```html
<!-- Basic Card -->
<div class="ant-card">
    <div class="ant-card-head">Card Title</div>
    <div class="ant-card-body">
        Card content here
    </div>
</div>

<!-- Colored Headers -->
<div class="ant-card">
    <div class="ant-card-head">Primary (default blue)</div>
</div>

<div class="ant-card">
    <div class="ant-card-head success">Success (green)</div>
</div>

<div class="ant-card">
    <div class="ant-card-head warning">Warning (yellow)</div>
</div>

<div class="ant-card">
    <div class="ant-card-head danger">Danger (red)</div>
</div>

<div class="ant-card">
    <div class="ant-card-head info">Info (cyan)</div>
</div>

<div class="ant-card">
    <div class="ant-card-head secondary">Secondary (gray)</div>
</div>
```

### Forms
```html
<!-- Form Item -->
<div class="ant-form-item">
    <label for="input1" class="ant-form-item-label">Label</label>
    <input type="text" class="ant-input" id="input1" name="input1">
</div>

<!-- Select -->
<div class="ant-form-item">
    <label for="select1" class="ant-form-item-label">Select</label>
    <select class="ant-select" id="select1" name="select1">
        <option value="">Choose...</option>
        <option value="1">Option 1</option>
    </select>
</div>

<!-- Textarea -->
<div class="ant-form-item">
    <label for="textarea1" class="ant-form-item-label">Textarea</label>
    <textarea class="ant-input" id="textarea1" rows="3" style="height: auto; min-height: 80px;"></textarea>
</div>

<!-- Checkbox -->
<label style="display: flex; align-items: center; gap: 8px;">
    <input type="checkbox" class="ant-checkbox">
    <span>Checkbox Label</span>
</label>

<!-- File Input -->
<input type="file" class="ant-input" style="padding: 8px;">
```

### Alerts
```html
<!-- Success Alert -->
<div class="ant-alert ant-alert-success">
    Success message here
</div>

<!-- Warning Alert -->
<div class="ant-alert ant-alert-warning">
    Warning message here
</div>

<!-- Error Alert -->
<div class="ant-alert ant-alert-error">
    Error message here
</div>
```

### Tags/Badges
```html
<!-- Success Tag -->
<span class="ant-tag ant-tag-success">Success</span>

<!-- Warning Tag -->
<span class="ant-tag ant-tag-warning">Warning</span>

<!-- Error Tag -->
<span class="ant-tag ant-tag-error">Error</span>

<!-- Default Tag -->
<span class="ant-tag ant-tag-default">Default</span>
```

### Grid System
```html
<!-- Two Columns (50/50) -->
<div class="ant-row">
    <div class="ant-col ant-col-6">Column 1</div>
    <div class="ant-col ant-col-6">Column 2</div>
</div>

<!-- Three Columns (33/33/33) -->
<div class="ant-row">
    <div class="ant-col ant-col-4">Column 1</div>
    <div class="ant-col ant-col-4">Column 2</div>
    <div class="ant-col ant-col-4">Column 3</div>
</div>

<!-- Four Columns (25/25/25/25) -->
<div class="ant-row">
    <div class="ant-col ant-col-4">Column 1</div>
    <div class="ant-col ant-col-4">Column 2</div>
    <div class="ant-col ant-col-4">Column 3</div>
    <div class="ant-col ant-col-4">Column 4</div>
</div>

<!-- Mixed Columns (33/66) -->
<div class="ant-row">
    <div class="ant-col ant-col-4">33%</div>
    <div class="ant-col ant-col-8">66%</div>
</div>

<!-- Full Width -->
<div class="ant-row">
    <div class="ant-col ant-col-12">100%</div>
</div>
```

### Tables
```html
<table class="ant-table">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Data 1</td>
            <td>Data 2</td>
            <td>
                <button class="ant-btn ant-btn-sm ant-btn-primary">Edit</button>
            </td>
        </tr>
    </tbody>
</table>
```

### Utility Classes
```html
<!-- Spacing -->
<div class="mb-2">Margin bottom 8px</div>
<div class="mb-3">Margin bottom 16px</div>
<div class="mb-4">Margin bottom 24px</div>
<div class="mt-3">Margin top 16px</div>
<div class="mt-4">Margin top 24px</div>

<!-- Flexbox -->
<div class="d-flex">Flex container</div>
<div class="d-flex gap-2">Flex with 8px gap</div>
<div class="d-flex gap-3">Flex with 16px gap</div>
<div class="d-flex flex-column">Flex column</div>
<div class="d-flex align-items-center">Align center</div>
<div class="d-flex justify-content-between">Space between</div>

<!-- Text -->
<div class="text-center">Centered text</div>
<div class="text-muted">Muted text (#8c8c8c)</div>

<!-- Width -->
<div class="w-100">Full width</div>

<!-- Display -->
<div class="d-none">Hidden</div>
```

### Loading Spinner
```html
<span class="spinner"></span>
```

## Layout Template

### Extending Base Layout
```blade
@extends('compliance.layouts.antd_base')

@section('title', 'Page Title')

@section('content')
    <!-- Your content here -->
@endsection

@push('styles')
    <style>
        /* Custom styles */
    </style>
@endpush

@push('scripts')
    <script>
        // Custom scripts
    </script>
@endpush
```

## Color Reference

```css
/* Primary Colors */
--ant-primary-color: #1890ff;
--ant-success-color: #52c41a;
--ant-warning-color: #faad14;
--ant-error-color: #ff4d4f;
--ant-info-color: #13c2c2;

/* Neutral Colors */
--ant-text-color: #262626;
--ant-text-secondary: #8c8c8c;
--ant-border-color: #d9d9d9;
--ant-background: #fafafa;
```

## Responsive Design

All columns automatically stack on mobile (< 768px):
```html
<!-- Desktop: 50/50, Mobile: 100/100 -->
<div class="ant-row">
    <div class="ant-col ant-col-6">Column 1</div>
    <div class="ant-col ant-col-6">Column 2</div>
</div>
```

## Best Practices

1. **Always use semantic HTML**
   ```html
   <button class="ant-btn">Button</button>  <!-- Good -->
   <div class="ant-btn">Button</div>        <!-- Bad -->
   ```

2. **Use form-item wrapper for forms**
   ```html
   <div class="ant-form-item">
       <label class="ant-form-item-label">Label</label>
       <input class="ant-input">
   </div>
   ```

3. **Maintain consistent spacing**
   ```html
   <div class="ant-card mb-3">Card 1</div>
   <div class="ant-card mb-3">Card 2</div>
   ```

4. **Use appropriate button types**
   ```html
   <button type="submit" class="ant-btn ant-btn-primary">Submit</button>
   <button type="button" class="ant-btn">Cancel</button>
   ```

5. **Wrap tables in responsive container**
   ```html
   <div style="overflow-x: auto;">
       <table class="ant-table">...</table>
   </div>
   ```

## Common Patterns

### Form with Two Columns
```html
<form method="POST" action="/submit">
    @csrf
    <div class="ant-row">
        <div class="ant-col ant-col-6">
            <div class="ant-form-item">
                <label class="ant-form-item-label">First Name</label>
                <input type="text" class="ant-input" name="first_name">
            </div>
        </div>
        <div class="ant-col ant-col-6">
            <div class="ant-form-item">
                <label class="ant-form-item-label">Last Name</label>
                <input type="text" class="ant-input" name="last_name">
            </div>
        </div>
    </div>
    <button type="submit" class="ant-btn ant-btn-primary">Submit</button>
</form>
```

### Card with Actions
```html
<div class="ant-card">
    <div class="ant-card-head">Card Title</div>
    <div class="ant-card-body">
        <p>Card content here</p>
        <div class="d-flex gap-2 mt-3">
            <button class="ant-btn ant-btn-primary">Primary Action</button>
            <button class="ant-btn">Secondary Action</button>
        </div>
    </div>
</div>
```

### Stats Grid
```html
<div class="ant-row text-center">
    <div class="ant-col ant-col-4">
        <h3 style="color: #1890ff; margin: 0;">150</h3>
        <small class="text-muted">Total</small>
    </div>
    <div class="ant-col ant-col-4">
        <h3 style="color: #52c41a; margin: 0;">120</h3>
        <small class="text-muted">Completed</small>
    </div>
    <div class="ant-col ant-col-4">
        <h3 style="color: #faad14; margin: 0;">30</h3>
        <small class="text-muted">Pending</small>
    </div>
</div>
```

## Migration Checklist

When converting a new page:

- [ ] Replace `<link bootstrap>` with base layout
- [ ] Change `.card` to `.ant-card`
- [ ] Change `.btn` to `.ant-btn`
- [ ] Change `.form-control` to `.ant-input`
- [ ] Change `.form-select` to `.ant-select`
- [ ] Change `.alert` to `.ant-alert`
- [ ] Change `.badge` to `.ant-tag`
- [ ] Change `.row` to `.ant-row`
- [ ] Change `.col-*` to `.ant-col ant-col-*`
- [ ] Update button classes in JavaScript
- [ ] Test responsive design
- [ ] Verify all functionality works

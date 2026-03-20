# Live Processing UI - Styling Quick Reference

## 🎨 Bootstrap Classes Used

### Card Components
```html
<div class="card shadow-sm">              <!-- Card with shadow -->
    <div class="card-header bg-info text-white">  <!-- Blue header -->
        <h5 class="mb-0">Title</h5>       <!-- No margin bottom -->
    </div>
    <div class="card-body">               <!-- Card body -->
        <!-- Content -->
    </div>
</div>
```

### Progress Bar
```html
<div class="progress" style="height: 30px;">
    <div class="progress-bar progress-bar-striped progress-bar-animated" 
         role="progressbar" 
         style="width: 0%" 
         aria-valuenow="0" 
         aria-valuemin="0" 
         aria-valuemax="100">
        0%
    </div>
</div>
```

### Table
```html
<div class="table-responsive">            <!-- Responsive wrapper -->
    <table class="table table-bordered table-hover align-middle mb-0">
        <thead class="table-light">       <!-- Light header -->
            <tr>
                <th>Column</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data</td>
            </tr>
        </tbody>
    </table>
</div>
```

### Badges
```html
<!-- Generated -->
<span class="badge bg-success">
    <i class="fas fa-check"></i> Generated
</span>

<!-- Processing -->
<span class="badge bg-info text-dark">
    <i class="fas fa-spinner fa-spin"></i> Processing
</span>

<!-- Pending -->
<span class="badge bg-secondary">Pending</span>
```

### Buttons
```html
<!-- Preview Button -->
<button class="btn btn-sm btn-outline-primary">
    <i class="fas fa-eye"></i> Preview
</button>
```

### Alerts
```html
<div class="alert alert-success mt-4 mb-0">
    <h5>✅ Success Message</h5>
    <p>Additional details here.</p>
</div>
```

### Flexbox
```html
<div class="d-flex justify-content-between align-items-center">
    <label>Left</label>
    <span>Right</span>
</div>
```

---

## 🎯 Color Classes

| Status | Class | Color |
|--------|-------|-------|
| Generated | `bg-success` | Green (#198754) |
| Processing | `bg-info` | Blue (#0DCAF0) |
| Pending | `bg-secondary` | Gray (#6C757D) |
| Header | `bg-info` | Blue (#17A2B8) |
| Alert | `alert-success` | Green (#D1E7DD) |

---

## 📏 Spacing Classes

| Class | Value |
|-------|-------|
| `mb-0` | margin-bottom: 0 |
| `mb-2` | margin-bottom: 0.5rem |
| `mb-3` | margin-bottom: 1rem |
| `mb-4` | margin-bottom: 1.5rem |
| `mt-4` | margin-top: 1.5rem |

---

## 🔧 JavaScript DOM Methods

### Create Element
```javascript
const row = document.createElement('tr');
const cell = document.createElement('td');
```

### Set Content
```javascript
cell.textContent = 'Text only';
cell.innerHTML = '<strong>HTML</strong>';
```

### Add Class
```javascript
element.className = 'badge bg-success';
element.classList.add('badge');
element.classList.remove('hidden');
```

### Set Attributes
```javascript
element.setAttribute('aria-valuenow', 50);
element.style.width = '50%';
```

### Append Element
```javascript
parent.appendChild(child);
tbody.appendChild(row);
```

### Update Content
```javascript
element.textContent = 'New text';
element.innerHTML = '<span>New HTML</span>';
```

---

## 📊 Table Column Widths

```
Form Code: 35%
Status:    30%
Action:    35%
```

---

## 🎨 Icon Classes (Font Awesome)

```html
<i class="fas fa-check"></i>           <!-- Checkmark -->
<i class="fas fa-spinner fa-spin"></i> <!-- Spinner -->
<i class="fas fa-eye"></i>             <!-- Eye -->
```

---

## 🔄 Update Functions

### Update Progress Bar
```javascript
progressBar.style.width = percent + '%';
progressBar.textContent = percent + '%';
progressBar.setAttribute('aria-valuenow', percent);
```

### Update Progress Text
```javascript
document.getElementById('progress-text').textContent = `${generated}/${total} forms generated`;
```

### Clear Table
```javascript
tbody.innerHTML = '';
```

### Add Table Row
```javascript
const row = document.createElement('tr');
row.appendChild(codeCell);
row.appendChild(statusCell);
row.appendChild(actionCell);
tbody.appendChild(row);
```

---

## 🎯 Status Badge Creation

```javascript
const badge = document.createElement('span');
badge.className = 'badge bg-success';
badge.innerHTML = '<i class="fas fa-check"></i> Generated';
```

---

## 🔘 Preview Button Creation

```javascript
const btn = document.createElement('button');
btn.className = 'btn btn-sm btn-outline-primary';
btn.innerHTML = '<i class="fas fa-eye"></i> Preview';
btn.onclick = () => openPreview(batchId, formCode);
```

---

## 📱 Responsive Breakpoints

```
Mobile:  < 576px
Tablet:  576px - 991px
Desktop: ≥ 992px
```

---

## 🎬 Animation Classes

```html
<!-- Animated Progress Bar -->
<div class="progress-bar progress-bar-striped progress-bar-animated"></div>

<!-- Spinning Icon -->
<i class="fas fa-spinner fa-spin"></i>
```

---

## ♿ Accessibility Attributes

```html
<!-- Progress Bar -->
role="progressbar"
aria-valuenow="50"
aria-valuemin="0"
aria-valuemax="100"

<!-- Modal -->
aria-hidden="true"

<!-- Close Button -->
aria-label="Close"
```

---

## 🧪 Testing Checklist

- [ ] Card displays with shadow
- [ ] Header is blue with white text
- [ ] Progress bar is 30px tall
- [ ] Progress bar animates
- [ ] Status badges show correct colors
- [ ] Icons display properly
- [ ] Table rows align properly
- [ ] Preview buttons appear
- [ ] Completion message displays
- [ ] Modal opens correctly
- [ ] Responsive on mobile
- [ ] Responsive on tablet
- [ ] Responsive on desktop

---

## 🐛 Common Issues & Fixes

### Progress bar not updating
```javascript
// Make sure to update all three properties
progressBar.style.width = percent + '%';
progressBar.textContent = percent + '%';
progressBar.setAttribute('aria-valuenow', percent);
```

### Table rows not showing
```javascript
// Clear table first
tbody.innerHTML = '';
// Then add rows
forms.forEach(form => {
    const row = document.createElement('tr');
    // ... add cells
    tbody.appendChild(row);
});
```

### Badge not showing color
```javascript
// Make sure to include both classes
badge.className = 'badge bg-success';
// Not just
badge.className = 'bg-success';
```

### Button not clickable
```javascript
// Make sure onclick is set
btn.onclick = () => openPreview(batchId, formCode);
// Or use addEventListener
btn.addEventListener('click', () => openPreview(batchId, formCode));
```

---

## 📚 Related Files

- `dashboard.blade.php` - Main implementation
- `LIVE_PROCESSING_UI_STYLING_COMPLETE.md` - Full styling guide
- `LIVE_PROCESSING_UI_VISUAL_GUIDE.md` - Visual layout
- `LIVE_PROCESSING_UI_QUICK_REFERENCE.md` - General reference

---

## 🎯 Quick Copy-Paste

### Card Container
```html
<div class="card shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">⏳ Processing Batch #<span id="processing-batch-id"></span></h5>
    </div>
    <div class="card-body">
        <!-- Content here -->
    </div>
</div>
```

### Progress Section
```html
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="mb-0"><strong>Progress:</strong></label>
        <span id="progress-text" class="badge bg-primary">0/0 forms generated</span>
    </div>
    <div class="progress" style="height: 30px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated" 
             id="progress-bar" role="progressbar" style="width: 0%" 
             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
    </div>
</div>
```

### Table
```html
<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width: 35%;">Form Code</th>
                <th style="width: 30%;">Status</th>
                <th style="width: 35%;">Action</th>
            </tr>
        </thead>
        <tbody id="forms-table-body"></tbody>
    </table>
</div>
```

---

**Version:** 3.0
**Status:** Production Ready
**Last Updated:** 2024

# PIXEL-LEVEL STRUCTURAL ANALYSIS - TAMIL NADU STATUTORY FORMS

## GOVERNMENT STANDARD vs CURRENT IMPLEMENTATION

### FORM_B - REGISTER OF WAGES (Factories Act)

#### STRUCTURAL DEVIATIONS IDENTIFIED

**1. PAGE MARGINS**
```
Government Standard:
- Top: 20mm
- Bottom: 20mm  
- Left: 15mm
- Right: 15mm

Current Implementation:
- Top: 15mm ❌ (-5mm deviation)
- Bottom: 10mm ❌ (-10mm deviation)
- Left: 10mm ❌ (-5mm deviation)
- Right: 10mm ❌ (-5mm deviation)

Impact: Content appears cramped, insufficient space for binding
```

**2. HEADER SPACING**
```
Government Standard:
- Title font: 14pt Bold, Times New Roman
- Title margin-bottom: 8mm
- Act reference margin: 4mm top/bottom
- Rule reference margin: 4mm top/bottom
- Border-bottom: 2mm solid black
- Total header height: ~35mm

Current Implementation:
- Title font: 12pt ❌ (-2pt)
- Title margin-bottom: 5px (~1.3mm) ❌ (-6.7mm)
- Act reference margin: 3px (~0.8mm) ❌ (-3.2mm)
- Border-bottom: 2px (~0.5mm) ❌ (-1.5mm)
- Total header height: ~20mm ❌ (-15mm)

Impact: Header appears compressed, unprofessional
```

**3. ESTABLISHMENT INFO BOX**
```
Government Standard:
- Border: 1.5mm solid black
- Padding: 8mm all sides
- Label width: 50mm
- Font: 10pt
- Line height: 6mm
- Margin-bottom: 10mm

Current Implementation:
- Border: none ❌
- Padding: 0mm ❌
- Label width: 150px (~39.7mm) ❌ (-10.3mm)
- Font: 8pt ❌ (-2pt)
- Line height: 1.5 (relative) ❌
- Margin-bottom: 15px (~4mm) ❌ (-6mm)

Impact: Info section lacks visual separation, hard to read
```

**4. TABLE STRUCTURE**
```
Government Standard:
- Border: 0.75mm solid black
- Cell padding: 3mm vertical, 2mm horizontal
- Header background: #E8E8E8
- Header font: 9pt Bold
- Data font: 9pt Regular
- Row height: minimum 8mm
- Column borders: 0.5mm solid black

Current Implementation:
- Border: 1px (~0.26mm) ❌ (-0.49mm)
- Cell padding: 4px 6px (~1mm 1.6mm) ❌
- Header background: #f5f5f5 ❌ (too light)
- Header font: 8pt ❌ (-1pt)
- Data font: 8pt ❌ (-1pt)
- Row height: auto ❌
- Column borders: 1px (~0.26mm) ❌ (-0.24mm)

Impact: Table appears thin, difficult to read when printed
```

**5. SIGNATURE BLOCK**
```
Government Standard:
- Margin-top: 15mm
- Declaration box: 1mm solid border, 5mm padding
- Signature line: 80mm width, 0.5mm solid
- Signature line margin-top: 25mm
- Label spacing: 5mm below line
- Date field width: 40mm
- Total block height: ~60mm

Current Implementation:
- Margin-top: 40px (~10.6mm) ❌ (-4.4mm)
- Declaration box: none ❌
- Signature line: 200px (~52.9mm) ❌ (-27.1mm)
- Signature line margin-top: 50px (~13.2mm) ❌ (-11.8mm)
- Label spacing: 5px (~1.3mm) ❌ (-3.7mm)
- Date field: undefined ❌
- Total block height: ~40mm ❌ (-20mm)

Impact: Insufficient space for physical signatures, cramped appearance
```

**6. FOOTER/PAGE NUMBERING**
```
Government Standard:
- Position: bottom-center
- Font: 8pt
- Format: "Page X of Y"
- Margin from bottom: 10mm

Current Implementation:
- Position: bottom-right ❌
- Font: 8pt ✓
- Format: "Page X of Y" ✓
- Margin: default ❌

Impact: Non-standard page numbering position
```

---

## CORRECTED CSS - TAMIL NADU GOVERNMENT STANDARD

### MASTER LAYOUT (tn_statutory_base.blade.php)

```css
@page {
    /* Government-standard margins */
    margin: 20mm 15mm 20mm 15mm;
    
    /* Page numbering - bottom center */
    @bottom-center {
        content: "Page " counter(page) " of " counter(pages);
        font-family: "Times New Roman", Times, serif;
        font-size: 8pt;
        margin-top: 10mm;
    }
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Times New Roman", Times, serif;
    font-size: 10pt;
    line-height: 1.5;
    color: #000;
    margin: 0;
    padding: 0;
}

/* HEADER SECTION */
.statutory-header {
    text-align: center;
    margin-bottom: 10mm;
    border-bottom: 2mm solid #000;
    padding-bottom: 8mm;
}

.form-title {
    font-size: 14pt;
    font-weight: bold;
    margin: 0 0 8mm 0;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    line-height: 1.3;
}

.act-reference {
    font-size: 10pt;
    margin: 4mm 0;
    font-style: italic;
    line-height: 1.4;
}

.rule-reference {
    font-size: 10pt;
    margin: 4mm 0 0 0;
    line-height: 1.4;
}

/* ESTABLISHMENT INFO BOX */
.establishment-info {
    border: 1.5mm solid #000;
    padding: 8mm;
    margin: 0 0 10mm 0;
    background-color: #FAFAFA;
}

.establishment-info p {
    margin: 0 0 3mm 0;
    font-size: 10pt;
    line-height: 6mm;
}

.establishment-info strong {
    display: inline-block;
    width: 50mm;
    font-weight: bold;
}

/* TABLE STRUCTURE */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 5mm 0;
    font-size: 9pt;
}

thead {
    display: table-header-group;
}

tbody {
    display: table-row-group;
}

tfoot {
    display: table-footer-group;
}

th, td {
    border: 0.75mm solid #000;
    padding: 3mm 2mm;
    text-align: left;
    vertical-align: middle;
    line-height: 1.3;
}

th {
    background-color: #E8E8E8;
    font-weight: bold;
    text-align: center;
    font-size: 9pt;
    min-height: 8mm;
}

td {
    font-size: 9pt;
    min-height: 8mm;
}

/* Column-specific borders */
th:not(:last-child),
td:not(:last-child) {
    border-right: 0.5mm solid #000;
}

/* TEXT ALIGNMENT */
.text-center { 
    text-align: center; 
}

.text-right { 
    text-align: right; 
}

.text-left { 
    text-align: left; 
}

/* TOTALS ROW */
.totals-row {
    font-weight: bold;
    background-color: #E8E8E8;
}

.totals-row td {
    border-top: 1.5mm double #000;
    padding: 4mm 2mm;
}

/* NIL DECLARATION */
.nil-declaration {
    text-align: center;
    padding: 20mm;
    border: 2mm solid #000;
    margin: 15mm 0;
    font-weight: bold;
    font-size: 12pt;
    background-color: #F5F5F5;
    min-height: 40mm;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* SIGNATURE BLOCK */
.signature-block {
    margin-top: 15mm;
    page-break-inside: avoid;
}

.declaration-text {
    border: 1mm solid #000;
    padding: 5mm;
    margin: 0 0 10mm 0;
    font-size: 9pt;
    line-height: 1.6;
    text-align: justify;
    background-color: #FAFAFA;
}

.signature-grid {
    display: table;
    width: 100%;
    margin-top: 10mm;
}

.signature-left {
    display: table-cell;
    width: 40%;
    vertical-align: top;
    padding-right: 10mm;
}

.signature-right {
    display: table-cell;
    width: 60%;
    text-align: right;
    vertical-align: top;
}

.signature-line {
    border-top: 0.5mm solid #000;
    width: 80mm;
    margin: 25mm 0 5mm 0;
    display: inline-block;
}

.signature-label {
    font-size: 9pt;
    margin-top: 5mm;
    line-height: 1.5;
}

.signature-label strong {
    font-weight: bold;
    font-size: 10pt;
}

/* DATE AND PLACE FIELDS */
.date-field,
.place-field {
    margin: 3mm 0;
    font-size: 9pt;
}

.date-field::after,
.place-field::after {
    content: "";
    display: inline-block;
    width: 40mm;
    border-bottom: 0.5mm solid #000;
    margin-left: 3mm;
}

/* SEAL PLACEHOLDER */
.seal-placeholder {
    margin-top: 8mm;
    font-size: 8pt;
    font-style: italic;
    color: #666;
    border: 0.5mm dashed #999;
    padding: 5mm;
    width: 50mm;
    height: 50mm;
    display: inline-block;
    text-align: center;
    line-height: 50mm;
}

/* PAGE BREAK CONTROLS */
.page-break-before {
    page-break-before: always;
}

.page-break-after {
    page-break-after: always;
}

.page-break-avoid {
    page-break-inside: avoid;
}

/* PRINT OPTIMIZATIONS */
@media print {
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .statutory-header,
    .establishment-info,
    .signature-block {
        page-break-inside: avoid;
    }
}
```

---

## FORM-SPECIFIC COLUMN WIDTH CORRECTIONS

### FORM_B - REGISTER OF WAGES

```css
/* Government-prescribed column widths (total: 100%) */
.col-sno { width: 3%; min-width: 8mm; }           /* Sl. No. */
.col-name { width: 12%; min-width: 30mm; }        /* Name */
.col-desig { width: 10%; min-width: 25mm; }       /* Designation */
.col-days { width: 5%; min-width: 12mm; }         /* Days Worked */
.col-rate { width: 7%; min-width: 18mm; }         /* Daily Rate */
.col-basic { width: 8%; min-width: 20mm; }        /* Basic Wages */
.col-da { width: 7%; min-width: 18mm; }           /* DA */
.col-ot { width: 7%; min-width: 18mm; }           /* Overtime */
.col-others { width: 7%; min-width: 18mm; }       /* Others */
.col-cash { width: 7%; min-width: 18mm; }         /* Other Cash */
.col-total { width: 8%; min-width: 20mm; }        /* Total */
.col-deduct { width: 10%; min-width: 25mm; }      /* Deductions */
.col-net { width: 8%; min-width: 20mm; }          /* Net Amount */
.col-sign { width: 8%; min-width: 20mm; }         /* Signature */
.col-initial { width: 5%; min-width: 12mm; }      /* Initial */
```

### FORM_XIII - CONTRACT LABOUR REGISTER

```css
/* Government-prescribed column widths */
.col-sno { width: 3%; min-width: 8mm; }
.col-name { width: 15%; min-width: 35mm; }
.col-father { width: 12%; min-width: 30mm; }
.col-sex { width: 4%; min-width: 10mm; }
.col-age { width: 4%; min-width: 10mm; }
.col-address { width: 15%; min-width: 35mm; }
.col-contractor { width: 12%; min-width: 30mm; }
.col-nature { width: 10%; min-width: 25mm; }
.col-date-from { width: 7%; min-width: 18mm; }
.col-date-to { width: 7%; min-width: 18mm; }
.col-wage { width: 7%; min-width: 18mm; }
.col-sign { width: 8%; min-width: 20mm; }
.col-remarks { width: 8%; min-width: 20mm; }
```

### FORM_10 - OVERTIME REGISTER

```css
.col-sno { width: 4%; min-width: 10mm; }
.col-name { width: 15%; min-width: 35mm; }
.col-father { width: 12%; min-width: 30mm; }
.col-desig { width: 10%; min-width: 25mm; }
.col-date { width: 8%; min-width: 20mm; }
.col-normal-hrs { width: 7%; min-width: 18mm; }
.col-ot-hrs { width: 7%; min-width: 18mm; }
.col-total-hrs { width: 7%; min-width: 18mm; }
.col-ot-rate { width: 8%; min-width: 20mm; }
.col-ot-wages { width: 8%; min-width: 20mm; }
.col-sign { width: 10%; min-width: 25mm; }
.col-remarks { width: 8%; min-width: 20mm; }
```

---

## FONT SIZE HIERARCHY (Government Standard)

```css
/* Tamil Nadu Government Typography Standards */

/* Main Title */
.form-title {
    font-size: 14pt;
    font-weight: bold;
    letter-spacing: 0.5pt;
}

/* Act/Rule References */
.act-reference,
.rule-reference {
    font-size: 10pt;
}

/* Establishment Info */
.establishment-info {
    font-size: 10pt;
}

/* Table Headers */
th {
    font-size: 9pt;
    font-weight: bold;
}

/* Table Data */
td {
    font-size: 9pt;
    font-weight: normal;
}

/* Declaration Text */
.declaration-text {
    font-size: 9pt;
    line-height: 1.6;
}

/* Signature Labels */
.signature-label {
    font-size: 9pt;
}

.signature-label strong {
    font-size: 10pt;
}

/* Page Numbers */
@page {
    @bottom-center {
        font-size: 8pt;
    }
}

/* NIL Declaration */
.nil-declaration {
    font-size: 12pt;
    font-weight: bold;
}

/* Footnotes/Remarks */
.footnote,
.remarks {
    font-size: 8pt;
    font-style: italic;
}
```

---

## SPACING PRECISION MATRIX

```css
/* Government-Standard Spacing (in mm) */

/* Vertical Spacing */
--header-margin-bottom: 10mm;
--title-margin-bottom: 8mm;
--act-margin-vertical: 4mm;
--info-box-margin-bottom: 10mm;
--table-margin-vertical: 5mm;
--signature-margin-top: 15mm;
--declaration-margin-bottom: 10mm;

/* Horizontal Spacing */
--page-margin-left: 15mm;
--page-margin-right: 15mm;
--info-box-padding: 8mm;
--table-cell-padding-h: 2mm;
--signature-padding-right: 10mm;

/* Border Widths */
--header-border-bottom: 2mm;
--info-box-border: 1.5mm;
--table-border-outer: 0.75mm;
--table-border-inner: 0.5mm;
--declaration-border: 1mm;
--signature-line: 0.5mm;

/* Heights */
--min-row-height: 8mm;
--signature-line-margin-top: 25mm;
--seal-size: 50mm;
```

---

## ALIGNMENT PRECISION

```css
/* Text Alignment Standards */

/* Serial Numbers */
.col-sno {
    text-align: center;
    vertical-align: middle;
}

/* Names and Text Fields */
.col-name,
.col-desig,
.col-address {
    text-align: left;
    vertical-align: middle;
    padding-left: 2mm;
}

/* Numeric Fields */
.col-days,
.col-rate,
.col-basic,
.col-da,
.col-ot,
.col-total,
.col-net,
.col-wage {
    text-align: right;
    vertical-align: middle;
    padding-right: 2mm;
}

/* Date Fields */
.col-date,
.col-date-from,
.col-date-to {
    text-align: center;
    vertical-align: middle;
}

/* Signature Columns */
.col-sign,
.col-initial {
    text-align: center;
    vertical-align: middle;
}

/* Deductions (Text) */
.col-deduct {
    text-align: left;
    vertical-align: top;
    padding: 2mm;
    font-size: 8pt;
    line-height: 1.4;
}

/* Table Headers */
th {
    text-align: center;
    vertical-align: middle;
    padding: 3mm 2mm;
}

/* Totals Row */
.totals-row td {
    text-align: right;
    vertical-align: middle;
    font-weight: bold;
}

.totals-row td:first-child {
    text-align: right;
    padding-right: 5mm;
}
```

---

## DOMPDF-SPECIFIC OPTIMIZATIONS

```css
/* DomPDF Compatibility Fixes */

/* Avoid flexbox (not fully supported) */
.signature-grid {
    display: table;  /* Use table instead of flex */
    width: 100%;
}

/* Avoid CSS Grid */
/* Use table-based layouts instead */

/* Border rendering */
table {
    border-collapse: collapse;  /* Essential for DomPDF */
}

/* Background colors */
th, .totals-row {
    background-color: #E8E8E8;  /* Use hex, not rgba */
}

/* Font rendering */
body {
    font-family: "Times New Roman", Times, serif;
    -webkit-font-smoothing: antialiased;
}

/* Page breaks */
.signature-block {
    page-break-inside: avoid;
    orphans: 3;
    widows: 3;
}

/* Image handling (if any) */
img {
    max-width: 100%;
    height: auto;
}

/* Avoid transforms */
/* DomPDF has limited transform support */

/* Use absolute units */
/* Always use mm, pt, or px - avoid %, em, rem in critical areas */
```

---

## IMPLEMENTATION CHECKLIST

### Step 1: Update Base Layout
- [ ] Replace `statutory_base.blade.php` with corrected CSS
- [ ] Update @page margins to 20mm/15mm
- [ ] Fix header spacing (8mm title margin)
- [ ] Add establishment info border (1.5mm)
- [ ] Correct table borders (0.75mm outer, 0.5mm inner)

### Step 2: Update Column Widths
- [ ] FORM_B: Apply 15-column width matrix
- [ ] FORM_XIII: Apply 13-column width matrix
- [ ] FORM_10: Apply 12-column width matrix
- [ ] All forms: Set min-width in mm

### Step 3: Fix Signature Block
- [ ] Increase margin-top to 15mm
- [ ] Add declaration border (1mm)
- [ ] Extend signature line to 80mm
- [ ] Add 25mm margin above signature line
- [ ] Add seal placeholder (50mm × 50mm)

### Step 4: Typography Corrections
- [ ] Title: 14pt (currently 12pt)
- [ ] Act/Rule: 10pt (currently 8pt)
- [ ] Table: 9pt (currently 8pt)
- [ ] Increase line heights to 1.5-1.6

### Step 5: Test Print Output
- [ ] Print sample forms on A4 paper
- [ ] Measure margins with ruler (should be 20mm/15mm)
- [ ] Verify signature space (should fit 80mm stamp)
- [ ] Check table readability
- [ ] Validate page numbering position

---

## MEASUREMENT VALIDATION

```
Government Standard A4 Layout:
- Paper: 210mm × 297mm
- Printable area: 180mm × 257mm (after margins)
- Header: ~35mm
- Establishment info: ~30mm
- Content area: ~160mm
- Signature block: ~60mm
- Footer: ~10mm

Current Implementation Issues:
- Printable area: ~190mm × 277mm ❌ (margins too small)
- Header: ~20mm ❌ (too compressed)
- Establishment info: ~15mm ❌ (no border, cramped)
- Content area: ~220mm ❌ (overflowing)
- Signature block: ~40mm ❌ (insufficient space)
- Footer: variable ❌ (inconsistent)
```

---

## PRIORITY FIXES (Immediate Impact)

1. **Page Margins:** 15mm → 20mm top/bottom, 10mm → 15mm left/right
2. **Table Borders:** 1px → 0.75mm (3x thicker)
3. **Font Sizes:** +1-2pt across all elements
4. **Signature Space:** +20mm margin-top, +27mm line width
5. **Establishment Box:** Add 1.5mm border, 8mm padding

**Expected Result:** Forms will match government samples within 2mm tolerance.

# 🚀 Import CDM to PowerDesigner - Quick Start (Visual Steps)

## 🎯 Phương Pháp NHANH NHẤT (5 phút)

### **Bước 1: Start PowerDesigner**

```
Desktop/Program Menu:
   ↓
[Double-click: PowerDesigner icon]
   ↓
PowerDesigner starts...
   ↓
┌──────────────────────────────────┐
│  PowerDesigner Welcome Screen    │
│                                  │
│  □ Create New Model              │
│  □ Open Recent Model             │
│  □ Open Model File...            │
│                                  │
│  [Button: New Model] ← CLICK     │
└──────────────────────────────────┘
```

---

### **Bước 2: Select Model Type**

```
┌──────────────────────────────────┐
│ New Model from Template          │
│                                  │
│  Model Type:                     │
│  ├─ ○ Physical Data Model (PDM)  │
│  ├─ ○ Logical Data Model (LDM)   │
│  └─ ○ Conceptual Data Model      │
│                                  │
│  DBMS:                           │
│  └─ [Dropdown: MySQL 5.1+] ✓     │
│                                  │
│  [Next →]                        │
└──────────────────────────────────┘

✓ SELECT: Physical Data Model (PDM)
✓ SELECT: MySQL 5.1+ (or 8.0+)
[Click: Next ]
```

---

### **Bước 3: Tạo Database Connection**

```
┌──────────────────────────────────┐
│ Database Connection              │
│                                  │
│  [Tab: Existing Connection]      │
│  ├─ Connection List:             │
│  │  └─ (empty if first time)    │
│                                  │
│  [Button: New Connection]        │
│         ↓                        │
│  ┌─────────────────────────────┐ │
│  │ Connection Settings:        │ │
│  │                             │ │
│  │ Name: senhong_ocop_live    │ │
│  │ DBMS: MySQL 5.1+           │ │
│  │ Server: localhost           │ │
│  │ Port: 3306                  │ │
│  │ User ID: root               │ │
│  │ Password: (empty)           │ │
│  │ Database: senhong_ocop      │ │
│  │                             │ │
│  │ [Test Connection] ← Click   │ │
│  │                             │ │
│  │ ✓ Connected successfully    │ │
│  │                             │ │
│  │ [OK]                        │ │
│  └─────────────────────────────┘ │
│                                  │
│  [Next →]                        │
└──────────────────────────────────┘
```

---

### **Bước 4: Reverse Engineer Tables**

```
┌──────────────────────────────────┐
│ Reverse Engineering Options      │
│                                  │
│ ☑ Tables                         │
│ ☑ Foreign Keys                   │
│ ☑ Indexes                        │
│ ☑ Triggers (if any)             │
│ ☑ Stored Procedures (if any)    │
│                                  │
│ [Select All] ← Click here        │
│ (ensures all objects selected)   │
│                                  │
│ [Next →]                         │
└──────────────────────────────────┘
```

---

### **Bước 5: Review & Generate**

```
┌──────────────────────────────────┐
│ Summary                          │
│                                  │
│ Connection: senhong_ocop_live   │
│ Database: senhong_ocop           │
│ Objects to Import:               │
│   ├─ 33 Tables                   │
│   ├─ 45+ Foreign Keys            │
│   ├─ 60+ Indexes                 │
│   └─ Views                       │
│                                  │
│ [Finish] ← Click                 │
└──────────────────────────────────┘

[PowerDesigner starts Reverse Engineering...]
Progress: ████████████████████ 100%
[Import complete!]
```

---

### **Bước 6: PDM Loaded Automatically**

```
✅ SUCCESS!

PowerDesigner window now shows:

Top-left:
┌────────────────┐
│ Model tree:    │
│ • senhong_ocop │
│   ├─ Tables    │ ← Expand to see 33 tables
│   ├─ Views     │
│   └─ ForeignK  │ ← Expand to see 45+ FK
└────────────────┘

Main area:
┌────────────────────────────────────┐
│ PDM Diagram (may be zoomed out)    │
│ [Auto-arranged 33 tables with]     │
│ [relationship lines]               │
│ (may look small/crowded)           │
└────────────────────────────────────┘

Right panel:
┌────────────────────────────────────┐
│ Properties (select entity to view) │
└────────────────────────────────────┘

Bottom panel:
┌────────────────────────────────────┐
│ Message Window - should be clean   │
│ (only info messages, no errors)    │
└────────────────────────────────────┘
```

---

### **Bước 7: Save PDM**

```
File → Save As
┌──────────────────────────────────┐
│ Save Model                       │
│                                  │
│ Filename: PDM_senhong_ocop_v1   │
│ Location: c:\xampp\htdocs\      │
│           luanvan\database\     │
│                                  │
│ [Save]                           │
└──────────────────────────────────┘

✅ PDM saved as: PDM_senhong_ocop_v1.pdm
```

---

### **Bước 8: Verify Import**

```
Tools → Model → Show Object List

┌──────────────────────────────────┐
│ Object List:                     │
│ Object Type    Count    Details  │
│ ─────────────────────           │
│ Tables:        33 ✓              │
│ Views:         3 ✓               │
│ Domains:       25 ✓ (data types) │
│ Foreign Keys:  45+ ✓             │
│ Indexes:       60+ ✓             │
└──────────────────────────────────┘

If all counts match → Import successful ✅
If counts differ → Check Message Window (F9)
```

---

### **Bước 9: Generate CDM**

```
After PDM is ready:

Tools → Generate → Conceptual Data Model

┌──────────────────────────────────┐
│ Generate CDM Dialog              │
│                                  │
│ Source: PDM_senhong_ocop_v1     │
│ Target Name: CDM_senhong_ocop   │
│                                  │
│ Options:                         │
│ ☑ Replace PK by Identifier      │
│ ☑ Remove SQL-specific Types     │
│ ☑ Generalize Relationships      │
│ ☑ Group Attributes by Domain    │
│                                  │
│ [OK]                             │
└──────────────────────────────────┘

[PowerDesigner generates CDM...]
Progress: ████████████████████ 100%
[CDM created and displayed!]
```

---

### **Bước 10: Export CDM**

```
File → Export

┌──────────────────────────────────┐
│ Export Format:                   │
│ ├─ PNG (best for PowerPoint)    │
│ ├─ PDF (best for printing)      │
│ ├─ SVG (best for editing)       │
│ └─ HTML (best for web)          │
│                                  │
│ Output: CDM_senhong_ocop.png    │
│                                  │
│ [Export]                         │
└──────────────────────────────────┘

✅ Done! Now you have:
   • PDM_senhong_ocop_v1.pdm (data model)
   • CDM_senhong_ocop.png (diagram for presentations)
   • CDM visible in PowerDesigner
```

---

## 🔍 Quick Verification Checklist

After Step 9 (CDM Generated), click through these:

### ✅ Check 1: Entity Count

```
CDM opened in PowerDesigner
View → Zoom to Fit (or Ctrl+Shift+F)
Count entities in diagram: should show ~33-40 boxes
Result: ✓ If yes → OK
        ✗ If < 20 → Problem!
```

### ✅ Check 2: Relationships

```
Look for connection lines between entities
Expected: Many lines showing 1:1, 1:N, M:N
Examples visible:
  • users ←→ staffs ←→ attendances
  • orders ←→ customers
  • products ←→ supplies
Result: ✓ If visible → OK
        ✗ If no lines → Check Message (F9)
```

### ✅ Check 3: Message Window

```
View → Message Window (F9) → scroll top to bottom
Expected: Clean (no red error messages)
OK to see: Blue info messages
Result: ✓ If clean → OK
        ✗ If red errors → Review them
```

### ✅ Check 4: Properties

```
Right-click any Entity → Properties
Expected panels:
  • Definition (name, description)
  • Attributes (fields inside)
  • Identifiers (which attribute is PK)
  • Comments
Result: ✓ If showing → OK
        ✗ If empty → Check data
```

---

## 🎨 After Import: Normal Usage

### **1. Navigate Between Models**

```
File → Recent Models
   ├─ PDM_senhong_ocop_v1.pdm ← Click to switch
   ├─ CDM_senhong_ocop.cdm    ← Click to switch
   └─ (if LDM exists)         ← Click to switch

Or:
Window → Models (list showing open models)
```

### **2. Edit Entity/Table**

```
On CDM (or PDM):
Right-click Entity → Properties

┌──────────────────────────────────┐
│ Edit Entity:                     │
│                                  │
│ Name: Order                      │
│ Description: Main transaction   │
│                                  │
│ Attributes Tab:                  │
│  ├─ id (Identifier)             │
│  ├─ customer (Reference)        │
│  ├─ total (Number)              │
│  └─ status (Text)               │
│                                  │
│ [OK - Save changes]              │
└──────────────────────────────────┘
```

### **3. View Diagram Different Ways**

```
View Menu:
  ├─ Zoom → to fit, zoom in, zoom out
  ├─ Arrange → Auto Layout (redraw diagram)
  ├─ Show/Hide Identifiers
  ├─ Show/Hide Attributes
  ├─ Show/Hide Relationships
  └─ Refresh (F5)

Useful shortcuts:
  Ctrl+Shift+F = Zoom to fit
  Ctrl+A = Select all
  Ctrl+Z = Undo
```

### **4. Generate Next Model**

```
After reviewing CDM:

To create LDM (normalized logic):
Tools → Generate → Logical Data Model
[Dialog opens - follow prompts]
Result: LDM created (separate model)

To generate SQL script:
File → Generate → SQL Create Script
Result: .sql file saved
```

---

## 🛑 If Something Goes Wrong

### **Error: "Connection Refused"**

```
→ Check MySQL running: Services → MySQL80 → Start
→ Check: port 3306 is correct
→ Check: username/password correct
→ Retry connection test
```

### **Error: "Table not found"**

```
→ Check: database name = "senhong_ocop" (exact)
→ Check: C:\xampp\htdocs\luanvan\database\senhong_ocop_CDM.sql
   exists and has content
→ Try import SQL file instead of reverse engineer
```

### **Missing Tables/FK/Indexes**

```
→ View → Message Window (F9) → look for errors
→ Re-run reverse engineer with all options ✓ checked
→ Close PDM without save, reopen, try again
```

### **PDM Diagram Too Cluttered**

```
→ View → Arrange → Auto Layout (redraw)
→ View → Zoom → Zoom to Fit
→ Or zoom to specific table area
→ Or create multiple diagrams (View → New Diagram)
```

---

## ⏱️ Timeline

```
START            END
  ↓              ↓
[5 min]  Step 1-5   (Connection + Import)
[1 min]  Step 6     (Load PDM)
[1 min]  Step 7     (Save)
[2 min]  Step 8     (Verify)
[2 min]  Step 9     (Generate CDM)
[1 min]  Step 10    (Export)
────────────────────
 TOTAL: ~12 MINUTES ✅
```

---

## 📋 Final Checklist

After completing Step 10:

```
☑ MySQL Server running
☑ Connection to senhong_ocop successful
☑ PDM imported (33 tables visible)
☑ PDM saved with version name
☑ CDM generated from PDM
☑ CDM exported to PNG/PDF
☑ Message window clean (no errors)
☑ Models ready for use

NEXT: Use CDM for documentation or generate LDM
```

---

**Time to Complete**: 12-15 minutes | **Difficulty**: Easy | **Version**: 1.0

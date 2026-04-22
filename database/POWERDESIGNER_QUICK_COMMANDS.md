# ⚡ PowerDesigner: Quick Commands Reference CDM ↔ LDM ↔ PDM

## 🎮 PowerDesigner Menu Shortcuts

### **Core Path: Tools → Generate**

```
PowerDesigner Main Menu
└─ Tools
   └─ Generate
      ├─ Conceptual Data Model (CDM)
      ├─ Logical Data Model (LDM)
      ├─ Physical Data Model (PDM)
      ├─ SQL Create Script
      ├─ DDL Scripts
      └─ Documentation

Alternative: Repository → Generate (if using workspace)
```

---

## 📝 Chi Tiết Các Lệnh Menu

### **1️⃣ Tools → Generate → Conceptual Data Model**

**Khi nào?** Có PDM → cần CDM (business view)

**Menu Path:**

```
Menu: Tools
  ↓ (Click "Generate")
    ↓ "Conceptual Data Model"
```

**Dialog Options:**

```
┌─────────────────────────────────────────────┐
│ Generate CDM Dialog:                        │
│                                             │
│ Source: [Current PDM]                       │
│ Target: [New CDM]                           │
│ Name: CDM_senhong_ocop                      │
│                                             │
│ Options:                                    │
│ ☑ Replace PK by Identifier                 │
│ ☑ Group Attributes                         │
│ ☑ Generate New Properties..                │
│                                             │
│ Transform Rules:                            │
│ ☑ Convert Technical Columns                │
│ ☑ Simplify Names                           │
│ ☑ Remove SQL-specific Types                │
│                                             │
│ [OK] [Cancel] [Help]                       │
└─────────────────────────────────────────────┘
```

**Kết quả:**

- ✓ Tables → Entities
- ✓ Columns → Attributes
- ✓ VARCHAR(255) → String
- ✓ BIGINT → Number
- ✓ Foreign Keys → Relationships
- ✓ Bỏ Indexes, Constraints

---

### **2️⃣ Tools → Generate → Logical Data Model**

**Khi nào?** Có CDM → cần LDM (normalized, DB-independent)

**Menu Path:**

```
Menu: Tools
  ↓ (Click "Generate")
    ↓ "Logical Data Model"
```

**Dialog Options:**

```
┌─────────────────────────────────────────────┐
│ Generate LDM Dialog:                        │
│                                             │
│ Source: [Current CDM]                       │
│ Target: [Generic/Target DBMS]               │
│ Name: LDM_senhong_ocop                      │
│                                             │
│ Normalization:                              │
│ ☑ 1st Normal Form (1NF)                    │
│ ☑ 2nd Normal Form (2NF)                    │
│ ☑ 3rd Normal Form (3NF) [DEFAULT]          │
│ ☑ Boyce-Codd Normal Form (BCNF)            │
│                                             │
│ Resolution:                                 │
│ ☑ Create junction Tables for M:M           │
│ ☑ Create Primary Keys                      │
│ ☑ Create Foreign Keys                      │
│ ☑ Remove Redundancy                        │
│                                             │
│ [OK] [Cancel] [Help]                       │
└─────────────────────────────────────────────┘
```

**Kết quả:**

- ✓ Chuẩn hóa 3NF
- ✓ Giải quyết M:N → junction table
- ✓ Thêm PK, FK rõ ràng
- ✓ Bỏ redundant data
- ✓ Vẫn generic (DB-independent)

---

### **3️⃣ Tools → Generate → Physical Data Model**

**Khi nào?** Có LDM → cần PDM (MySQL-specific, deploy)

**Menu Path:**

```
Menu: Tools
  ↓ (Click "Generate")
    ↓ "Physical Data Model"
```

**Dialog Options:**

```
┌─────────────────────────────────────────────┐
│ Generate PDM Dialog:                        │
│                                             │
│ Source: [Current LDM]                       │
│ Target DBMS: MySQL 5.1+ [IMPORTANT!]        │
│ Name: PDM_senhong_ocop_MySQL                │
│                                             │
│ Generation Options:                         │
│ ☑ Create Tables                            │
│ ☑ Create Domains                           │
│ ☑ Create Primary Keys                      │
│ ☑ Create Foreign Keys                      │
│ ☑ Create Indexes                           │
│ ☑ Create Unique Constraints                │
│ ☑ Create Check Constraints                 │
│                                             │
│ MySQL-Specific:                             │
│ ☑ Use BIGINT for IDs                       │
│ ☑ Use ENUM for enumerations                │
│ ☑ Add AUTO_INCREMENT                       │
│ ☑ Add TIMESTAMP defaults                   │
│ ☑ Add Storage Engine: InnoDB               │
│                                             │
│ [OK] [Cancel] [Help]                       │
└─────────────────────────────────────────────┘
```

**Kết quả:**

- ✓ Tables với MySQL-specific types
- ✓ Data types: BIGINT, VARCHAR, DECIMAL, TIMESTAMP
- ✓ Constraints: PK, FK, UNIQUE, CHECK
- ✓ Indexes trên FK + key columns
- ✓ Ready for deployment

---

## 🔄 Workflow Commands

### **Scenario 1: PDM → CDM (tạo business view)**

```bash
# Step 1: Open existing PDM
File → Open → PDM_senhong_ocop_MySQL.pdm

# Step 2: Navigate to generation
Tools → Generate → Conceptual Data Model

# Step 3: Configure options (see dialog above)
Name: CDM_senhong_ocop
Options: ☑ all default options

# Step 4: Click OK (auto-generate)
[Automatically opens new CDM diagram]

# Step 5: Save
File → Save As → CDM_senhong_ocop.cdm

# Step 6: Export for presentation
File → Export → Select Format:
  ├─ PNG (for slides)
  ├─ PDF (for documentation)
  ├─ HTML (for web)
  └─ SVG (for scalable graphics)
```

---

### **Scenario 2: CDM → LDM (normalize & validate)**

```bash
# Step 1: Open existing CDM
File → Open → CDM_senhong_ocop.cdm

# Step 2: Navigate to generation
Tools → Generate → Logical Data Model

# Step 3: Configure normalization
Options:
  • Select: 3rd Normal Form (3NF)
  • ☑ Create junction tables for M:M
  • ☑ Create Primary Keys
  • ☑ Create Foreign Keys

# Step 4: Click OK
[Automatically opens new LDM diagram]

# Step 5: Validate relationships
View → Zoom to fit entire diagram
Verify:
  ✓ No M:M without junction table
  ✓ All PK present
  ✓ All FK present
  ✓ No duplicate data

# Step 6: Save
File → Save As → LDM_senhong_ocop.ldm

# Step 7: Export for team review
File → Export → PDF (for analysts/BA)
```

---

### **Scenario 3: LDM → PDM (implement MySQL)**

```bash
# Step 1: Open existing LDM
File → Open → LDM_senhong_ocop.ldm

# Step 2: Navigate to generation
Tools → Generate → Physical Data Model

# Step 3: Select Target DBMS CRITICALLY IMPORTANT!
DBMS Selection: MySQL 5.1+ (or MySQL 8.0+)

# Step 4: Configure MySQL-specific options
Options:
  ☑ Create Tables
  ☑ Create Primary Keys
  ☑ Create Foreign Keys
  ☑ Create Indexes
  ☑ Create Unique Constraints
  ☑ Auto-increment for bigint IDs
  ☑ TIMESTAMP defaults
  ☑ InnoDB Storage Engine

# Step 5: Name PDM
Name: PDM_senhong_ocop_MySQL_v1

# Step 6: Click OK
[Automatically opens new PDM diagram]

# Step 7: Add optimization (optional)
Edit individual tables:
  • Add INDEX on FK columns
  • Add INDEX on frequently queried columns
  • Add composite indexes for joins
  • Add UNIQUE constraints

# Step 8: Save
File → Save As → PDM_senhong_ocop_MySQL_v1.pdm

# Step 9: Generate SQL
File → Generate → SQL Create Script
Name: senhong_ocop_MySQL_CREATE.sql
Options:
  ☑ Include DROP TABLE IF EXISTS
  ☑ Include Create Table
  ☑ Include Index Definitions
  ☑ Include Foreign Key Constraints

# Step 10: Save SQL file
File → Save → senhong_ocop_MySQL_CREATE.sql

# Step 11: Deploy to database
# Terminal:
mysql -h 127.0.0.1 -u root senhong_ocop < senhong_ocop_MySQL_CREATE.sql
```

---

## 🎨 Advanced Menu Options

### **Alternative: Reverse Path (PDM → LDM → CDM from scratch)**

```
Scenario: Bạn có actual MySQL database (tạo PDM từ reverse engineer)
→ Tạo LDM từ PDM
→ Tạo CDM từ LDM
```

**Commands:**

```bash
# 1. Reverse engineer dari actual database
Database → Configure Connections
Database → New Connection → [MySQL Connection Details]
Database → Reverse Engineer → From Database Connection
[Select: all tables]
[Generate PDM]

# 2. From PDM to LDM
Tools → Generate → Logical Data Model

# 3. From LDM to CDM
File → Switch to CDM
Tools → Generate → Conceptual Data Model
```

---

## 📊 View/Navigation Commands

### **View Current Model**

```
View Menu → Content
├─ Model → (show current model type)
│  ├─ Conceptual Data Model (CDM)
│  ├─ Logical Data Model (LDM)
│  └─ Physical Data Model (PDM)
├─ Diagram
│  ├─ Entities (show/hide)
│  ├─ Attributes (show/hide)
│  ├─ Relationships (show/hide)
│  ├─ Constraints (show/hide)
│  └─ Domains (show/hide)
├─ Appearance
│  ├─ Auto Layout → arrange diagram
│  ├─ zoom to fit
│  ├─ select all
│  └─ Properties
└─ Refresh
```

### **Switch Between Models**

```
Window → Model List (if multiple models open)
File → Open Recent → switch between .cdm, .ldm, .pdm

Or use keyboard shortcut (if set):
Ctrl+Tab → cycle through open models
```

---

## 🔧 Configuration Before Generation

### **Set Default DBMS**

```
Tools → General Options
  ├─ Diagram
  ├─ Model
  │  ├─ Default DBMS: MySQL 5.1+ [IMPORTANT]
  │  └─ Repository Options
  ├─ SQL
  ├─ Versioning
  └─ Naming Conventions
```

### **Naming Convention**

```
Tools → General Options → Naming Conventions
├─ Table: [Database].[%NAME]
├─ Column: %NAME
├─ Constraint: PK_%TABLE%, FK_%TABLE%_%REF%
├─ Index: idx_%TABLE%_%COLUMN%
└─ Unique: uk_%TABLE%_%COLUMN%
```

---

## ⌨️ Keyboard Shortcuts (Common)

```
Ctrl+N          → New Model
Ctrl+O          → Open
Ctrl+S          → Save
Ctrl+Shift+S    → Save As
Ctrl+P          → Print / Export
Ctrl+E          → Edit selected
Ctrl+Z          → Undo
Ctrl+Y          → Redo
Ctrl+A          → Select All
F5              → Refresh
F12             → Properties
Delete          → Delete selected
Insert          → Add new entity/table
```

---

## 📋 Checklist: Trước & Sau Generation

### **Trước Generate CDM:**

- [ ] PDM file đã save
- [ ] PDM có tất cả tables (33+ tables)
- [ ] Foreign keys được định nghĩa
- [ ] Relationships rõ ràng

### **Sau Generate CDM:**

- [ ] Entities hiển thị (không còn Tables)
- [ ] Relationships (không encore Foreign Keys)
- [ ] Không có SQL data types
- [ ] Không có Indexes/Constraints
- [ ] Business readable names

### **Trước Generate LDM:**

- [ ] CDM file đã save
- [ ] CDM logic đúng
- [ ] Tất cả relationships rõ ràng

### **Sau Generate LDM:**

- [ ] Tables normalized (3NF)
- [ ] M:N có junction tables
- [ ] PK, FK rõ ràng
- [ ] Vẫn DB-independent

### **Trước Generate PDM:**

- [ ] LDM file đã save
- [ ] LDM normalized
- [ ] Target DBMS selected: MYSQL 5.1+

### **Sau Generate PDM:**

- [ ] Data types MySQL-specific
- [ ] Indexes visible
- [ ] Constraints FK visible
- [ ] Ready for SQL generation

---

## 🎯 Quick Decision Tree

```
"Tôi có gì?"
   ↓
   ├─ "Có PDF requirement từ product team"
   │  └─ DÙNG CDM
   │     └─ File → Generate CDM
   │
   ├─ "Có tables hiệu nhưng cần optimize"
   │  └─ DÙNG LDM
   │     └─ Validate normalization
   │        Review relationships
   │
   ├─ "Cần deploy vào MySQL database"
   │  └─ DÙNG PDM
   │     └─ Tools → Generate → PDM
   │        Select DBMS: MySQL 8.0
   │        File → Generate → SQL Script
   │
   └─ "Có actual MySQL database hiện tại"
      └─ REVERSE ENGINEER
         Database → Reverse Engineer
         → From Database Connection
         [Auto-generates PDM]
```

---

## 📱 Common Workflow (Recommended)

```
Day 1 - Requirement Analysis
├─ Create CDM (business entities)
├─ Stakeholder review CDM
└─ Export CDM → PDF (presentation)

Day 2 - Logical Design
├─ Generate LDM from CDM
├─ Normalize to 3NF
├─ BA/Analyst review
└─ Export LDM → PDF (technical doc)

Day 3 - Physical Implementation
├─ Generate PDM for MySQL 8.0
├─ Add indexes & optimization
├─ DBA/Developer review
├─ Generate SQL Create script
└─ Export PDM → PNG (architecture diagram)

Day 4 - Deployment
├─ Review SQL script
├─ Run: mysql < script.sql
├─ Verify tables in actual DB
└─ Create migration file
```

---

## 🐛 Debugging Unknown Issues

### **If Generation Fails:**

```
1. Check PowerDesigner log:
   View → Message Window (or F9)

2. Verify source model integrity:
   → File → Save & Close
   → File → Reopen
   → Try Generate again

3. Clear repository cache:
   Tools → Clear Selected caches

4. Check DBMS compatibility:
   (if PDM: Tools → Model → Verify)

5. Reset to defaults:
   Tools → General Options → Reset
```

### **If Diagram Doesn't Render:**

```
1. View → Refresh (F5)
2. View → Arrange → Auto Layout
3. View → Zoom → Zoom to fit
4. Try export → reimport
5. File → Close without save → reopen
```

---

## 📞 PowerDesigner Help Resources

```
Menu: Help
├─ Help Contents (F1)
├─ OLE DB Connection Help
├─ DBMS-Specific Help
│  └─ MySQL 5.1+
├─ Sample Models
│  ├─ CDM Example
│  ├─ LDM Example
│  └─ PDM Example (sakila DB)
├─ Online Documentation
└─ Sybase Official site
```

---

**Version**: 2.0 | **Last Updated**: 2026-04-16 | **DBMS**: MySQL 8.0

# Evy Cost FIFO – WooCommerce Inventory Costing & AP Plugin

**Contributors:** Evy
**Tags:** woocommerce, inventory, fifo, cost, cogs, accounts payable, ap, financial reports, google sheets, api, bookings, subscriptions, bundles, composite products
**Requires at least:** 6.0
**Tested up to:** 6.5
**Requires PHP:** 7.4
**Stable tag:** 1.0.0
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

---

## 🇹🇭 Evy Cost FIFO – ปลั๊กอินจัดการต้นทุนสินค้าคงคลังและเจ้าหนี้การค้าสำหรับ WooCommerce

### 🔍 ภาพรวม

Evy Cost FIFO คือปลั๊กอินสำหรับ WordPress ที่ออกแบบมาเพื่อเสริมความสามารถของ WooCommerce ในการจัดการต้นทุนสินค้าคงคลังด้วยวิธี **FIFO (First In First Out)** และการบริหารจัดการเจ้าหนี้การค้า (Accounts Payable) อย่างแม่นยำ โดยมีจุดประสงค์หลักเพื่อช่วยให้ธุรกิจสามารถวิเคราะห์ต้นทุนสินค้า, ควบคุมการซื้อขาย, และวางแผนการเงินได้อย่างมีประสิทธิภาพ

**จุดเน้นสำคัญ:** ปลั๊กอินนี้ถูกพัฒนาขึ้นโดยยึดหลักการ **ไม่เข้าไปแก้ไขหรือซ้ำซ้อนกับฟีเจอร์พื้นฐานของ WooCommerce** โดยเฉพาะระบบจัดการสต็อกของ WooCommerce เอง Evy Cost FIFO จะเน้นไปที่การบันทึกและคำนวณข้อมูลด้านต้นทุนและเจ้าหนี้โดยเฉพาะ และจะส่งออกข้อมูลเพื่อการวิเคราะห์ภายนอก (ผ่าน Google Sheets) เพื่อลดภาระการประมวลผลบน WordPress และรักษาความสะอาดของระบบ

ปลั๊กอินนี้ถูกออกแบบมาเพื่อรองรับ **Product Types ที่หลากหลายของ WooCommerce** รวมถึงที่มาจาก Extensions เช่น Bookable Products (WooCommerce Bookings), Subscription Products (WooCommerce Subscriptions), Bundled Products (Product Bundles), และ Composite Products (Composite Products for WooCommerce) โดยจะปรับ Logic การจัดการต้นทุนให้เหมาะสมกับแต่ละประเภท แต่จะไม่รบกวนการจัดการสต็อกพื้นฐานของ WooCommerce

### 🔧 ฟีเจอร์หลัก

1.  **Inventory In (FIFO Basis):**
    * บันทึกรายการซื้อสินค้าเข้าสต็อกพร้อมรายละเอียด**ต้นทุนต่อหน่วย**, จำนวน, ผู้จำหน่าย (Supplier), เทอมเครดิต, ค่าขนส่ง, และข้อมูลการชำระเงิน
    * รองรับการบันทึกข้อมูลแบบ **เฉพาะรายการ (Per Entry)** แม้จะเป็นผู้จำหน่ายรายเดิม เพื่อรองรับความแตกต่างของเทอมเครดิตหรือต้นทุนในแต่ละครั้ง
    * ระบบจะคำนวณ **Due Date** อัตโนมัติจาก Credit Term ที่ระบุ
    * รองรับการบันทึกต้นทุนสำหรับ **Product Types ที่หลากหลาย** เช่น Simple, Variable, Bookable, Subscription (สำหรับส่วนประกอบที่ซื้อเข้ามา) โดยจะบันทึก `product_type` ที่ถูกต้องจาก WooCommerce เพื่อใช้ในการประมวลผล COGS
    * สำหรับสินค้าบริการหรือสินค้าที่ไม่ต้องสต็อกจริง (เช่น ทัวร์, บริการ) จะยังคงบันทึกต้นทุนและจัดการเจ้าหนี้ได้ แต่จะไม่ทำการลด "ปริมาณสต็อก" ในระบบ Evy Cost FIFO เนื่องจากไม่มีสต็อกกายภาพ

2.  **Inventory Out (อิงจากคำสั่งซื้อ WooCommerce):**
    * ระบบจะบันทึกการตัดสต็อกสินค้าและคำนวณ **ต้นทุนขาย (COGS)** โดยอัตโนมัติเมื่อสถานะคำสั่งซื้อใน WooCommerce เปลี่ยนเป็น **"Completed"**
    * ใช้หลักการ **FIFO Logic** ที่แม่นยำในการเลือกต้นทุนจากล็อตสินค้าที่เข้ามาก่อน สำหรับสินค้าแต่ละประเภท (Simple, Variable, Bookable, Subscription Components, Bundled Components, Composite Components)
    * สำหรับ Product Types ที่ไม่มีสต็อกจริง (เช่น `virtual`, `bookable`, `subscription`, `bundle`, `composite`), ปลั๊กอินจะคำนวณ COGS แต่**จะไม่ลด `quantity` ที่เหลืออยู่ในล็อตการซื้อ** เพื่อสะท้อนว่าสินค้าเหล่านั้นไม่มีสต็อกกายภาพที่ต้องตัด

3.  **Google Sheets Sync:**
    * **Manual Trigger:** มีปุ่มในหน้า Admin ของปลั๊กอินเพื่อให้ผู้ใช้สามารถสั่ง Sync ข้อมูลทั้งหมด (Inventory In, COGS, Accounts Payable) ไปยัง Google Sheet ที่กำหนดได้ทันที
    * **Automated Trigger:** ระบบจะมีการตั้งค่าให้ Sync ข้อมูลไปยัง Google Sheet โดยอัตโนมัติเป็นประจำ (เช่น รายวัน ผ่าน WordPress Cron Job)
    * **จุดประสงค์:** เพื่อให้ผู้ใช้สามารถนำข้อมูลไปวิเคราะห์และสร้างรายงานทางการเงินที่ซับซ้อน (เช่น Sources of Funds, Account Payable Analysis, Inventory Analysis) ได้อย่างยืดหยุ่นบน Google Sheets โดยใช้ประโยชน์จากฟีเจอร์ของ Sheets และ Gemini AI

### 🔄 การเชื่อมต่อกับ WooCommerce

* ดึงรายชื่อสินค้า (product\_id), Product Type และรายละเอียดที่จำเป็นจาก WooCommerce โดยตรงเพื่อใช้ในฟอร์มการบันทึก
* การบันทึกสินค้าออกจากสต็อกและคำนวณ COGS จะอิงจาก **เหตุการณ์ "Order Completed" ใน WooCommerce เท่านั้น**
* **ปลั๊กอินนี้จะไม่แก้ไขหรือรบกวนระบบการจัดการสต็อกของ WooCommerce โดยตรง** ระบบสต็อกของ WooCommerce จะยังคงทำงานตามปกติ เพื่อคงความเข้ากันได้และลดความเสี่ยง
* การคำนวณ COGS (ต้นทุนขาย) และข้อมูลเจ้าหนี้ทั้งหมดจะใช้เฉพาะข้อมูลที่บันทึกผ่าน Evy Cost FIFO เท่านั้น
* รองรับการทำงานร่วมกับ WooCommerce Extensions ที่เพิ่ม Product Type เช่น WooCommerce Bookings, WooCommerce Subscriptions, Product Bundles for WooCommerce, และ Composite Products for WooCommerce โดยเน้นการบันทึกต้นทุนของ "ส่วนประกอบ" หรือ "หน่วยบริการ" ที่แท้จริง

### 📁 โครงสร้างไฟล์

evy-cost-fifo/
├── evy-cost-fifo.php                   # ไฟล์หลักของปลั๊กอิน: จัดการการเปิด/ปิดใช้งาน, โหลด autoloader, เริ่มต้นคลาสต่างๆ
├── admin/                              # หน้าสำหรับผู้ดูแลระบบและส่วนประกอบ UI
│   ├── settings.php                    # หน้าตั้งค่าปลั๊กอิน: การกำหนดค่า Google Sync, ปุ่มซิงค์ด้วยตนเอง
│   ├── inventory-in.php                # ฟอร์มสำหรับบันทึกข้อมูล Inventory In, แสดงรายการสินค้า
│   └── partials/                       # ส่วน HTML/PHP ที่สามารถนำกลับมาใช้ใหม่ได้สำหรับหน้าผู้ดูแลระบบ
│       └── form-fields.php             # ฟิลด์ฟอร์มทั่วไปสำหรับ Inventory In
├── includes/                           # ฟังก์ชันหลัก, คลาส และฟังก์ชันตัวช่วย
│   ├── class-evy-fifo-database-manager.php # จัดการตารางฐานข้อมูลที่กำหนดเองของปลั๊กอิน (การสร้าง, การอัปเดต)
│   ├── class-evy-fifo-inventory-manager.php # ประกอบด้วย Logic FIFO หลักสำหรับ Inventory In/Out และการคำนวณ COGS
│   ├── class-evy-fifo-google-sync.php     # จัดการการเชื่อมต่อ Google Sheets API ทั้งหมด, การจัดรูปแบบข้อมูลสำหรับการซิงค์
│   ├── class-evy-fifo-admin-menu.php      # จัดการเมนูผู้ดูแลระบบและเมนูย่อยของปลั๊กอิน
│   ├── class-evy-fifo-woocommerce-integration.php # จัดการ Hook และการดึงข้อมูลเฉพาะของ WooCommerce
│   └── helpers.php                     # ฟังก์ชันยูทิลิตี้และฟังก์ชันตัวช่วยทั่วไป
├── assets/
│   ├── css/                            # ไฟล์ CSS สำหรับ UI ผู้ดูแลระบบของปลั๊กอิน
│   │   └── admin.css
│   └── js/                             # ไฟล์ JavaScript สำหรับการโต้ตอบของ UI ผู้ดูแลระบบ (เช่น การแนะนำอัตโนมัติ)
│       └── admin.js
├── vendor/                             # ไดเรกทอรีสำหรับ Dependencies ของ Composer (เช่น Google API Client Library)
│   └── autoload.php                    # Composer autoloader
├── credentials.json                    # สำคัญ: ข้อมูลรับรองบัญชีบริการของ Google (ไฟล์ JSON) ไฟล์นี้ต้องได้รับการรักษาความปลอดภัย!
├── README.md                           # ไฟล์นี้: ภาพรวมปลั๊กอินที่ครอบคลุมสำหรับนักพัฒนาและผู้ใช้
└── CHANGELOG.md                        # บันทึกการเปลี่ยนแปลงที่สำคัญทั้งหมดในแต่ละเวอร์ชันของปลั๊กอิน

### 🧩 วิธีติดตั้ง

1.  ดาวน์โหลดปลั๊กอิน Evy Cost FIFO
2.  อัปโหลดโฟลเดอร์ `evy-cost-fifo/` ไปยังไดเรกทอรี `wp-content/plugins/` บนเซิร์ฟเวอร์ WordPress ของคุณ
3.  เข้าสู่ระบบหลังบ้าน WordPress ไปที่ **Plugins** และกด **เปิดใช้งาน (Activate)** ปลั๊กอิน Evy Cost FIFO
4.  เมนูสำหรับปลั๊กอินจะปรากฏขึ้นในหลังบ้าน WordPress (เช่น ภายใต้ WooCommerce หรือในเมนูหลัก)
5.  เข้าสู่หน้า **Evy Cost FIFO Settings** เพื่อตั้งค่า **Google Spreadsheet ID** และวางไฟล์ `credentials.json` จาก Google Cloud Project ของคุณลงในโฟลเดอร์หลักของปลั๊กอิน (`evy-cost-fifo/`)
6.  เริ่มใช้งานโดยการบันทึกข้อมูลสินค้าเข้า (Inventory In) และปล่อยให้ระบบทำงานร่วมกับ WooCommerce Order Status

### ⚠️ หมายเหตุสำคัญสำหรับนักพัฒนา

* **ไม่รบกวน WooCommerce Core:** ปลั๊กอินนี้ออกแบบมาเพื่อเพิ่มฟีเจอร์ด้านต้นทุนโดยไม่เข้าไปยุ่งเกี่ยวกับระบบสต็อกพื้นฐานของ WooCommerce หรือระบบการทำงานของ Extension อื่นๆ เช่น Bookings หรือ Subscriptions. การอัปเดตสต็อกจริงยังคงจัดการโดย WooCommerce
* **การจัดการข้อมูลภายนอก (Google Sheets):** การรายงานและการวิเคราะห์ข้อมูลเชิงลึกทั้งหมดจะทำบน Google Sheets เพื่อความยืดหยุ่นและลดภาระเซิร์ฟเวอร์
* **Service Account Credentials (`credentials.json`):** ไฟล์นี้มีความสำคัญอย่างยิ่งและควรได้รับการปกป้องอย่างสูงสุด ห้ามเผยแพร่สู่สาธารณะโดยเด็ดขาด

---

## 🇬🇧 Evy Cost FIFO – WooCommerce Inventory Costing & AP Plugin

### 🔍 Overview

Evy Cost FIFO is a WordPress plugin crafted to extend WooCommerce's capabilities by providing precise **FIFO (First In First Out)** inventory costing and robust Accounts Payable (AP) management. Its core objective is to empower businesses with accurate cost analysis, enhanced purchasing control, and effective financial planning.

**Key Principle:** This plugin is developed with a strict focus on **not modifying or duplicating core WooCommerce functionalities**, especially its native stock management system. Evy Cost FIFO concentrates solely on recording and calculating cost and AP-related data, then offloads this data for external analysis (via Google Sheets) to minimize WordPress server load and preserve system integrity.

The plugin is designed to accommodate **various WooCommerce Product Types**, including those introduced by extensions such as Bookable Products (WooCommerce Bookings), Subscription Products (WooCommerce Subscriptions), Bundled Products (Product Bundles), and Composite Products (Composite Products for WooCommerce). It will adapt its costing logic appropriately for each type without interfering with WooCommerce's fundamental stock handling.

### 🔧 Key Features

1.  **Inventory In (FIFO Basis):**
    * Record inventory purchases with details such as **unit cost**, quantity, supplier, credit term, shipping cost, and payment information.
    * Supports **per-entry data recording**, even for the same supplier, to accommodate varying credit terms or costs for different batches.
    * Automatically calculates the **Due Date** based on the specified Credit Term.
    * Supports recording costs for **diverse Product Types** (e.g., Simple, Variable, Bookable, Subscription components) by accurately fetching and storing the `product_type` from WooCommerce for COGS processing.
    * For service products or non-stockable items (e.g., tours, consultations), costs and AP are still recorded and managed, but no "stock quantity" deductions will occur within Evy Cost FIFO as there's no physical inventory.

2.  **Inventory Out (WooCommerce Order Driven):**
    * The system automatically records stock deductions and calculates **Cost of Goods Sold (COGS)** when a WooCommerce order status changes to **"Completed."**
    * Applies precise **FIFO Logic** to select the cost from the earliest purchased inventory lots, adapting for each product type (Simple, Variable, Bookable, Subscription Components, Bundled Components, Composite Components).
    * For Product Types without physical stock (e.g., `virtual`, `bookable`, `subscription`, `bundle`, `composite`), the plugin will calculate COGS but **will not reduce the `quantity` remaining in the purchase lots**, reflecting their non-physical nature.

3.  **Google Sheets Sync:**
    * **Manual Trigger:** Includes an admin button allowing users to initiate an immediate sync of all data (Inventory In, COGS, Accounts Payable) to a configured Google Sheet.
    * **Automated Trigger:** The plugin is set up to automatically sync data to Google Sheets on a recurring basis (e.g., daily, via WordPress Cron Job).
    * **Purpose:** To enable users to flexibly analyze data and generate complex financial reports (e.g., Sources of Funds, Account Payable Analysis, Inventory Analysis) on Google Sheets, leveraging Sheet's features and Gemini AI.

### 🔄 WooCommerce Integration

* Directly retrieves product IDs, Product Types, and necessary details from WooCommerce for use in input forms.
* Inventory outflow recording and COGS calculation are solely triggered by **WooCommerce "Order Completed" status events.**
* **This plugin does NOT modify or interfere with WooCommerce's built-in stock management system.** WooCommerce's stock quantities will continue to function independently, ensuring compatibility and reducing risks.
* All COGS and Accounts Payable calculations and data are based exclusively on information recorded through Evy Cost FIFO.
* Supports integration with WooCommerce Extensions that introduce new Product Types (e.g., WooCommerce Bookings, WooCommerce Subscriptions, Product Bundles for WooCommerce, and Composite Products for WooCommerce) by focusing on costing the underlying "components" or "service units."

### 📁 File Structure

evy-cost-fifo/
├── evy-cost-fifo.php                   # Main plugin file: handles activation/deactivation, loads autoloader, initializes classes.
├── admin/                              # Admin pages and UI components.
│   ├── settings.php                    # Plugin settings page: Google Sync configuration, manual sync button.
│   ├── inventory-in.php                # Form for recording Inventory In data, displays product list.
│   └── partials/                       # Reusable HTML/PHP parts for admin pages.
│       └── form-fields.php             # Common form fields for Inventory In.
├── includes/                           # Core functionalities, classes, and helper functions.
│   ├── class-evy-fifo-database-manager.php # Manages plugin's custom database tables (creation, updates).
│   ├── class-evy-fifo-inventory-manager.php # Contains core FIFO logic for Inventory In/Out and COGS calculation.
│   ├── class-evy-fifo-google-sync.php     # Handles all Google Sheets API integration, data formatting for sync.
│   ├── class-evy-fifo-admin-menu.php      # Manages plugin's admin menu and submenus.
│   ├── class-evy-fifo-woocommerce-integration.php # Handles WooCommerce specific hooks and data retrieval.
│   └── helpers.php                     # General utility and helper functions.
├── assets/
│   ├── css/                            # CSS files for plugin's admin UI.
│   │   └── admin.css
│   └── js/                             # JavaScript files for admin UI interactivity (e.g., auto-suggestion).
│       └── admin.js
├── vendor/                             # Directory for Composer dependencies (e.g., Google API Client Library).
│   └── autoload.php                    # Composer autoloader.
├── credentials.json                    # IMPORTANT: Google Service Account credentials (JSON file). This file MUST be secured!
├── README.md                           # This file: comprehensive plugin overview for developers and users.
└── CHANGELOG.md                        # Records all significant changes across plugin versions.

### 🧩 Installation

1.  Download the Evy Cost FIFO plugin.
2.  Upload the `evy-cost-fifo/` folder to your `wp-content/plugins/` directory on your WordPress server.
3.  Log into your WordPress admin dashboard, navigate to **Plugins**, and **Activate** the Evy Cost FIFO plugin.
4.  The plugin's menu items will appear in your WordPress admin (e.g., under WooCommerce or in the main menu).
5.  Visit the **Evy Cost FIFO Settings** page to configure your **Google Spreadsheet ID** and place the `credentials.json` file (obtained from your Google Cloud Project Service Account) directly into the main plugin folder (`evy-cost-fifo/`).
6.  Begin by entering your Inventory In data and let the system integrate with WooCommerce order statuses.

### ⚠️ Important Notes for Developers

* **Non-intrusive to WooCommerce Core:** This plugin is designed to extend costing capabilities without interfering with WooCommerce's fundamental stock management or the operational logic of other Extensions like Bookings or Subscriptions. Actual stock updates remain handled by WooCommerce.
* **External Data Handling (Google Sheets):** All advanced reporting and detailed data analysis are performed externally on Google Sheets for flexibility and reduced server load.
* **Service Account Credentials (`credentials.json`):** This file is highly sensitive and must be kept absolutely secure. Under no circumstances should it be publicly exposed.

---

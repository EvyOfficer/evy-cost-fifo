## 📜 CHANGELOG.md (Thai & English - Revised)

```markdown
# Evy Cost FIFO - Change Log

---

## 🇹🇭 บันทึกการเปลี่ยนแปลง

### 1.0.0 - 2025-06-07 (Stable Release)

* **Initial Release:**
    * ปลั๊กอิน Evy Cost FIFO เวอร์ชั่นเริ่มต้นเพื่อเสริมการจัดการต้นทุน FIFO และเจ้าหนี้การค้าให้กับ WooCommerce
    * **Inventory In (FIFO Basis):**
        * เพิ่มหน้า Admin สำหรับบันทึกรายการซื้อสินค้าเข้าสต็อกพร้อมต้นทุน, จำนวน, ผู้จำหน่าย, เทอมเครดิต, และข้อมูลการชำระเงิน
        * รองรับการคำนวณ Due Date อัตโนมัติ
        * รองรับการบันทึกต้นทุนสำหรับ Product Types ที่หลากหลายของ WooCommerce (Simple, Variable, Bookable, Subscription, Bundle Component, Composite Component) โดยการดึง `product_type` จาก WooCommerce
    * **Inventory Out (WooCommerce Order Driven):**
        * ระบบอัตโนมัติในการบันทึกการตัดสต็อกและคำนวณ COGS เมื่อสถานะคำสั่งซื้อ WooCommerce เปลี่ยนเป็น "Completed" โดยใช้หลักการ FIFO
        * ปรับ Logic การลด `quantity` ในล็อตการซื้อให้เหมาะสมกับ Product Types (ลด `quantity` สำหรับสินค้าที่มีสต็อกกายภาพเท่านั้น)
    * **Google Sheets Sync:**
        * เพิ่มฟังก์ชันการ Sync ข้อมูล (Inventory In, COGS, Accounts Payable) ไปยัง Google Sheet ที่กำหนด
        * รองรับทั้งการ Sync แบบ Manual (ผ่านปุ่มในหน้า Admin) และแบบ Automated (ผ่าน WordPress Cron Job)
        * เพิ่มหน้า Settings ใน Admin เพื่อให้ผู้ใช้กำหนด Google Spreadsheet ID และตรวจสอบสถานะไฟล์ credentials.json
    * **โครงสร้างปลั๊กอิน:**
        * จัดโครงสร้างไฟล์ปลั๊กอินใหม่ให้เป็นระเบียบตาม Best Practices ของ WordPress เพื่อความเข้าใจและบำรุงรักษาในระยะยาว

---

## 🇬🇧 Change Log

### 1.0.0 - 2025-06-07 (Stable Release)

* **Initial Release:**
    * Initial version of Evy Cost FIFO plugin, designed to augment WooCommerce with FIFO costing and Accounts Payable management.
    * **Inventory In (FIFO Basis):**
        * Introduced an Admin page for recording inventory purchases, including cost, quantity, supplier, credit terms, and payment details.
        * Supports automatic Due Date calculation.
        * Supports cost recording for various WooCommerce Product Types (Simple, Variable, Bookable, Subscription, Bundle Component, Composite Component) by fetching `product_type` from WooCommerce.
    * **Inventory Out (WooCommerce Order Driven):**
        * Implemented automated stock deduction and COGS calculation (using FIFO logic) when a WooCommerce order status is "Completed."
        * Adjusted `quantity` reduction logic in purchase lots to be appropriate for each Product Type (reducing `quantity` only for physically stockable items).
    * **Google Sheets Sync:**
        * Added functionality to sync data (Inventory In, COGS, Accounts Payable) to a specified Google Sheet.
        * Supports both Manual Sync (via an admin button) and Automated Sync (via WordPress Cron Job).
        * Introduced an Admin Settings page for users to configure Google Spreadsheet ID and verify `credentials.json` file status.
    * **Plugin Structure:**
        * Organized plugin file structure according to WordPress Best Practices for better understanding and long-term maintainability.

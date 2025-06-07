# วิธีเชื่อมต่อ Google Sheets API กับปลั๊กอิน Evy Cost FIFO

คู่มือฉบับนี้จะช่วยคุณเชื่อมต่อปลั๊กอิน Evy Cost FIFO กับ Google Sheets API โดยใช้ Service Account เพื่อให้สามารถบันทึกข้อมูลลง Google Sheet ได้อย่างปลอดภัยและอัตโนมัติ

---

## 🔑 สิ่งที่คุณต้องมี
- บัญชี Google
- สิทธิ์เข้าถึง Google Cloud Console
- Google Sheet ที่สร้างไว้แล้ว
- ปลั๊กอิน Evy Cost FIFO เวอร์ชันล่าสุด

---

## ✨ คำสำคัญที่ควรรู้

### 1. **Google Spreadsheet ID**
ได้จาก URL ของ Google Sheet ของคุณโดยตรง เช่น: https://docs.google.com/spreadsheets/d/1BltD-F_Q-x9c_zD9wXkG2a0L6p-5Y7VfR3hJ_m8N0C/edit#gid=0
**Google Spreadsheet ID คือ:**  
`1BltD-F_Q-x9c_zD9wXkG2a0L6p-5Y7VfR3hJ_m8N0C`

---

### 2. **Sheet Name**
เป็นชื่อ **Tab** ภายใน Google Sheet เช่น:
- `Purchase Lots`
- `COGS Entries`

> คุณสามารถตั้งชื่ออะไรก็ได้ และควรสร้างล่วงหน้าไว้ใน Google Sheet เพื่อความชัวร์

---

## 🛠 ขั้นตอนการสร้างไฟล์ credentials.json (Service Account Key)

### ✅ ขั้นตอนที่ 1: เข้าสู่ Google Cloud Console
- เปิด: https://console.cloud.google.com/
- ล็อกอินด้วยบัญชี Google

---

### ✅ ขั้นตอนที่ 2: สร้าง Project ใหม่ (หรือเลือก Project เดิม)
- คลิกชื่อโปรเจกต์ที่มุมซ้ายบน > `NEW PROJECT`
- ตั้งชื่อโปรเจกต์ เช่น `Evy FIFO Plugin Integration`
- กด `CREATE`

---

### ✅ ขั้นตอนที่ 3: เปิดใช้งาน Google Sheets API
- เมนูซ้าย: `APIs & Services` > `Enabled APIs & services`
- คลิก `+ ENABLE APIS AND SERVICES`
- ค้นหา `Google Sheets API` > คลิก `ENABLE`

---

### ✅ ขั้นตอนที่ 4: สร้าง Service Account
- เมนูซ้าย: `APIs & Services` > `Credentials`
- คลิก `+ CREATE CREDENTIALS` > `Service Account`
- ตั้งชื่อ เช่น: `evy-fifo-sync-service-account`
- คัดลอกอีเมลบัญชีบริการนี้เก็บไว้
- คลิก `CREATE AND CONTINUE`

---

### ✅ ขั้นตอนที่ 5: มอบสิทธิ์การเข้าถึง
- ในช่อง `Select a role` ให้เลือก:  
  `Project` > `Viewer` *(หรือ Editor หากจำเป็น)*
- กด `CONTINUE` > `DONE`

---

### ✅ ขั้นตอนที่ 6: สร้าง Key และดาวน์โหลด credentials.json
- ที่หน้า `Credentials` คลิกชื่อ Service Account
- ไปที่แท็บ `KEYS` > `ADD KEY` > `Create new key`
- เลือก `JSON` > กด `CREATE`
- ไฟล์จะถูกดาวน์โหลดโดยอัตโนมัติ
- **เปลี่ยนชื่อไฟล์เป็น** `credentials.json`

---

### ✅ ขั้นตอนที่ 7: แชร์ Google Sheet กับ Service Account
- เปิด Google Sheet ที่จะเชื่อมต่อ
- คลิก `Share` > วางอีเมลจากขั้นตอนที่ 4
- เลือกสิทธิ์เป็น `Editor` > กด `Done`

---

## 📥 ขั้นตอนสุดท้าย: วางไฟล์ credentials.json ในปลั๊กอิน

ให้นำไฟล์ `credentials.json` ไปไว้ใน: /wp-content/plugins/evy-cost-fifo/credentials.json


---

## ✅ พร้อมใช้งาน
เมื่อตั้งค่าทุกอย่างเรียบร้อยแล้ว ปลั๊กอินจะสามารถเขียนข้อมูลไปยัง Google Sheet ได้ทันที โดยไม่ต้องเข้าสู่ระบบ Google ทุกครั้งอีกต่อไป

หากมีคำถามหรือพบปัญหา สามารถสอบถามทีมผู้พัฒนาได้ครับ

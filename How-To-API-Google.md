🇹🇭 วิธีการติดตั้ง Google Client Library สำหรับปลั๊กอิน Evy Cost FIFO
เอกสารนี้อธิบายขั้นตอนการติดตั้ง Google Client Library สำหรับ PHP ซึ่งจำเป็นสำหรับปลั๊กอิน Evy Cost FIFO ในการสื่อสารกับ Google Sheets API

วิธีที่ 1: การใช้ Composer (แนะนำ)
Composer เป็นเครื่องมือจัดการ Dependencies สำหรับ PHP ที่ช่วยให้การติดตั้งและจัดการ Libraries เป็นเรื่องง่าย

ข้อกำหนดเบื้องต้น:

ต้องติดตั้ง Composer บนเครื่องมือพัฒนาของคุณ และสามารถเรียกใช้งานได้ผ่าน Command Line หากจะ Deploy ไปยัง Server ก็ควรมี Composer บน Server ด้วย หรือจะต้องรวม Folder vendor/ ที่สร้างจาก Composer ในเครื่องของคุณไปพร้อมกับการ Deploy
ขั้นตอน:

ไปที่ไดเรกทอรีหลักของปลั๊กอิน:
เปิด Terminal หรือ Command Prompt ของคุณ แล้วไปยังไดเรกทอรี evy-cost-fifo/ (ซึ่งเป็น Folder หลักของปลั๊กอินที่ไฟล์ evy-cost-fifo.php อยู่)

Bash

cd /path/to/your/wordpress/wp-content/plugins/evy-cost-fifo/
เรียกใช้ Google API Client Libraries ด้วย Composer:
รันคำสั่ง Composer ต่อไปนี้เพื่อติดตั้ง Libraries ของ Google API ที่จำเป็น:

Bash

composer require google/apiclient google/apiclient-services
คำสั่งนี้จะทำการ:

ดาวน์โหลด Libraries และ Dependencies ที่เกี่ยวข้อง
สร้างไดเรกทอรีชื่อ vendor/ ภายในไดเรกทอรีหลักของปลั๊กอินของคุณ
สร้างไฟล์ composer.json และ composer.lock ซึ่งใช้สำหรับติดตาม Dependencies ของโปรเจกต์ของคุณ
รวมไฟล์ Autoload:
ตรวจสอบให้แน่ใจว่าได้มีการรวมไฟล์ autoload.php จากไดเรกทอรี vendor/ ไว้ในไฟล์หลักของปลั๊กอิน (evy-cost-fifo.php) หรือในจุดเริ่มต้นที่คลาส Google Client จะถูกเรียกใช้งาน เพื่อให้ปลั๊กอินของคุณสามารถโหลดคลาส Library ที่จำเป็นได้โดยอัตโนมัติ

ตัวอย่าง: เพิ่มบรรทัดต่อไปนี้ไว้ใกล้ส่วนบนสุดของไฟล์ evy-cost-fifo.php (หลังจากการตรวจสอบ ABSPATH):

PHP

require_once __DIR__ . '/vendor/autoload.php';
ข้อควรทราบที่สำคัญสำหรับการ Deploy: เมื่อ Deploy ปลั๊กอินของคุณไปยัง Server จริง ตรวจสอบให้แน่ใจว่าได้รวมไดเรกทอรี vendor/ ทั้งหมดไปพร้อมกับไฟล์ปลั๊กอินของคุณด้วย โดยปกติ Dependencies ของ Composer จะถูกจัดการแยกตามโปรเจกต์

วิธีที่ 2: การติดตั้งด้วยตนเอง (ทางเลือกหากไม่สามารถใช้ Composer ได้)
วิธีนี้เกี่ยวข้องกับการดาวน์โหลดและจัดวางไฟล์ Library ด้วยตนเอง ซึ่งไม่แนะนำเท่าไหร่เพราะทำให้การอัปเดตยุ่งยากกว่า

ข้อกำหนดเบื้องต้น:

สามารถเข้าถึงอินเทอร์เน็ตเพื่อดาวน์โหลด Library
ขั้นตอน:

ดาวน์โหลด Google API Client Library สำหรับ PHP:
ไปที่ GitHub Repository อย่างเป็นทางการสำหรับ Google API Client Library สำหรับ PHP:
https://github.com/google/google-api-php-client/releases
ดาวน์โหลดเวอร์ชัน Stable ล่าสุด (โดยปกติจะเป็นไฟล์ .zip หรือ .tar.gz)

แตกไฟล์ที่ดาวน์โหลดมา:
แตกไฟล์ Archive ที่ดาวน์โหลดมา โดยปกติคุณจะพบไดเรกทอรี vendor/ (หรือโครงสร้างคล้ายกัน) อยู่ภายใน

สร้างไดเรกทอรี Vendor ของปลั๊กอิน:
ภายในไดเรกทอรีปลั๊กอิน evy-cost-fifo/ ของคุณ ให้สร้าง Folder ใหม่ชื่อ vendor/

wp-content/plugins/evy-cost-fifo/vendor/
คัดลอกไฟล์ Library:
คัดลอก เนื้อหา ของไดเรกทอรี vendor/ ที่แตกไฟล์ออกมา (หรือ Folder หลักของ Library หากไม่มี vendor ซ้อนอยู่) ไปยังไดเรกทอรี evy-cost-fifo/vendor/ ของคุณ เป้าหมายคือการให้ไฟล์ autoload.php อยู่ในตำแหน่ง evy-cost-fifo/vendor/autoload.php โดยตรง

ตัวอย่างโครงสร้างหลังจากคัดลอก:

wp-content/plugins/evy-cost-fifo/
├── evy-cost-fifo.php
└── vendor/
    ├── autoload.php
    ├── composer/
    └── google/
รวมไฟล์ Autoload:
ตรวจสอบให้แน่ใจว่าได้มีการรวมไฟล์ autoload.php จากไดเรกทอรี vendor/ ที่คุณสร้างด้วยตนเองนี้ ไว้ในไฟล์หลักของปลั๊กอิน (evy-cost-fifo.php) หรือในจุดเริ่มต้นที่คลาส Google Client จะถูกเรียกใช้งาน

ตัวอย่าง: เพิ่มบรรทัดต่อไปนี้ไว้ใกล้ส่วนบนสุดของไฟล์ evy-cost-fifo.php (หลังจากการตรวจสอบ ABSPATH):

PHP

require_once __DIR__ . '/vendor/autoload.php';
ข้อควรทราบที่สำคัญ: ในการติดตั้งด้วยตนเองนี้ คุณจะต้องทำซ้ำขั้นตอนเหล่านี้หากต้องการอัปเดต Google Client Library ในอนาคต

How-To-API-Google.md
🇬🇧 How to Install Google Client Library for Evy Cost FIFO Plugin
This document outlines the steps to install the Google Client Library for PHP, which is essential for the Evy Cost FIFO plugin to communicate with Google Sheets API.

Method 1: Using Composer (Recommended)
Composer is a dependency manager for PHP that makes it easy to install and manage libraries.

Prerequisites:

Composer must be installed on your development machine and accessible via the command line. If deploying to a server, Composer should also be available there, or the vendor/ directory generated locally must be included in your deployment.
Steps:

Navigate to the Plugin's Root Directory:
Open your terminal or command prompt and navigate to the evy-cost-fifo/ directory (the main folder of your plugin where evy-cost-fifo.php is located).

Bash

cd /path/to/your/wordpress/wp-content/plugins/evy-cost-fifo/
Require Google API Client Libraries:
Run the following Composer command to install the necessary Google API client libraries:

Bash

composer require google/apiclient google/apiclient-services
This command will:

Download the libraries and their dependencies.
Create a vendor/ directory within your plugin's root directory.
Generate composer.json and composer.lock files, which track your project's dependencies.
Include the Autoload File:
Ensure that the autoload.php file from the vendor/ directory is included in your plugin's main file (evy-cost-fifo.php) or at an early point where Google Client classes are used. This allows your plugin to load the necessary library classes automatically.

Example: Add the following line near the top of your evy-cost-fifo.php file (after ABSPATH check):

PHP

require_once __DIR__ . '/vendor/autoload.php';
Important Note for Deployment: When deploying your plugin to a live server, make sure to include the entire vendor/ directory along with your plugin files. Composer dependencies are typically managed per project.

Method 2: Manual Installation (Alternative if Composer is not feasible)
This method involves manually downloading and placing the library files. It's less recommended as it makes updates more cumbersome.

Prerequisites:

Internet access to download the library.
Steps:

Download the Google API Client Library for PHP:
Go to the official GitHub repository for the Google API Client Library for PHP:
https://github.com/google/google-api-php-client/releases
Download the latest stable release (usually a .zip or .tar.gz file).

Extract the Downloaded Files:
Unzip the downloaded archive. You will typically find a vendor/ directory (or similar structure) inside.

Create Plugin Vendor Directory:
Inside your evy-cost-fifo/ plugin directory, create a new folder named vendor/.

wp-content/plugins/evy-cost-fifo/vendor/
Copy Library Files:
Copy the contents of the extracted library's vendor/ directory (or the main library folder if it doesn't have a vendor sub-folder) into your evy-cost-fifo/vendor/ directory. The goal is to have the autoload.php file directly inside evy-cost-fifo/vendor/autoload.php.

Example structure after copying:

wp-content/plugins/evy-cost-fifo/
├── evy-cost-fifo.php
└── vendor/
    ├── autoload.php
    ├── composer/
    └── google/
Include the Autoload File:
Ensure that the autoload.php file from this manual vendor/ directory is included in your plugin's main file (evy-cost-fifo.php) or at an early point where Google Client classes are used.

Example: Add the following line near the top of your evy-cost-fifo.php file (after ABSPATH check):

PHP

require_once __DIR__ . '/vendor/autoload.php';
Important Note: With manual installation, you will need to repeat this process if you want to update the Google Client Library in the future.

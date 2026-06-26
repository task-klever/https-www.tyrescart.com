# Changelog

## 1.1.0 – 2026-06-23

### Added
- **Notify Invoice admin popup** – modal in Order Actions tab with Save & Send Mail, Update, and Download PDF buttons
- **Admin controllers** – `SendMail`, `Update`, `DownloadPdf` under `Controller/Adminhtml/Invoice/`
- **Admin route** – `klever_orderactions` frontend name registered in `etc/adminhtml/routes.xml`
- **Invoice PDF header** – "Tax Invoice" title centered, Invoice # and Invoice Date on right side, white background (no gray)
- **Invoice PDF table header** – custom `_drawHeader()` with Description, Price, Qty, VAT (5%), Subtotal columns
- **Invoice PDF item renderer** – `DefaultInvoice.php` without SKU column, registered via `etc/pdf.xml`
- **50-50 column layout** – shifted column divider from 275→298 and text from 285→308 in PDF
- **Notify Invoice email template** – `notify_invoice.html` with clean styling (no green/colored backgrounds)
- **Invoice email items template** – `items.phtml` with `#333333` text headers, `1px solid #dddddd` borders
- **Email template registration** – `etc/email_templates.xml`
- **Email layout** – `klever_orderactions_email_invoice_items.xml`

## 1.0.0

### Added
- Initial release: Order Actions tab with Purchase Orders, Installer Actions, and Notifications sections
- Custom Invoice PDF with modified labels and installer address support

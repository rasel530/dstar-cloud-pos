<?php

namespace App\Services\Printing;

class ReceiptBuilder
{
    public function build(array $document, array $company, array $settings): string
    {
        $items = $document['items'] ?? [];
        $payments = $document['payments'] ?? [];

        $itemRows = '';
        $sno = 0;
        foreach ($items as $item) {
            $sno++;
            $qty = number_format($item['quantity'] ?? 0, 2);
            $price = number_format($item['price'] ?? 0, 2);
            $lineTotal = number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2);
            $productName = $item['product_name'] ?? $item['name'] ?? 'Item';
            $itemRows .= <<<ROW
            <tr>
                <td class="name">{$sno}. {$productName}</td>
                <td class="qty">{$qty}</td>
                <td class="price">{$price}</td>
                <td class="total">{$lineTotal}</td>
            </tr>
            ROW;
        }

        $companyName = htmlspecialchars($company['name'] ?? $settings['company_name'] ?? 'Company Name');
        $companyAddress = htmlspecialchars($company['address'] ?? $settings['company_address'] ?? '');
        $companyPhone = htmlspecialchars($company['phone'] ?? $settings['company_phone'] ?? '');
        $companyEmail = htmlspecialchars($company['email'] ?? $settings['company_email'] ?? '');

        $documentNumber = htmlspecialchars($document['number'] ?? '');
        $documentDate = htmlspecialchars($document['date'] ?? date('Y-m-d H:i:s'));
        $documentType = htmlspecialchars($document['document_type']['name'] ?? $document['type'] ?? 'Receipt');
        $orderStatus = $document['order_status'] ?? null;

        $subtotal = number_format($document['subtotal'] ?? 0, 2);
        $taxAmount = number_format($document['tax_amount'] ?? 0, 2);
        $discountAmount = number_format($document['discount'] ?? 0, 2);
        $grandTotal = number_format($document['grand_total'] ?? $document['total'] ?? 0, 2);
        $paidAmount = number_format($document['paid_amount'] ?? 0, 2);
        $dueAmount = number_format($document['due_amount'] ?? 0, 2);
        $changeAmount = number_format($document['change_amount'] ?? 0, 2);

        $cashierName = htmlspecialchars($document['cashier'] ?? '');
        $customerName = htmlspecialchars($document['customer'] ?? '');
        $customerPhone = htmlspecialchars($document['customer_phone'] ?? '');

        $branchName = '';
        $branchCode = '';
        $branchAddress = '';
        $branchPhone = '';
        $branch = $document['branch'] ?? null;
        if ($branch) {
            $branchName = htmlspecialchars($branch['name'] ?? '');
            $branchCode = htmlspecialchars($branch['branch_code'] ?? '');
            $branchAddress = htmlspecialchars($branch['address'] ?? '');
            $branchPhone = htmlspecialchars($branch['phone'] ?? '');
        }

        $paymentMethod = '';
        if (!empty($payments)) {
            $methodNames = [];
            foreach ($payments as $payment) {
                $methodNames[] = htmlspecialchars($payment['payment_type']['name'] ?? $payment['method'] ?? $document['payment_method'] ?? 'Unknown');
            }
            $paymentMethod = implode(', ', array_unique($methodNames));
        }
        if (empty($paymentMethod)) {
            $paymentMethod = htmlspecialchars($document['payment_method'] ?? '');
        }

        $headerText = htmlspecialchars($settings['receipt_header'] ?? '');
        $footerText = htmlspecialchars($settings['receipt_footer'] ?? 'Thank you for your purchase!');
        $extraFooter = $settings['receipt_extra_footer'] ?? '';
        $receiptTitle = htmlspecialchars($settings['receipt_title'] ?? 'RECEIPT');
        $logoUrl = !empty($settings['receipt_logo'] ?? '') ? $settings['receipt_logo'] : ($settings['logo'] ?? '');
        $currencySymbol = htmlspecialchars(
            $settings['currency_symbol']
            ?? config('business.currency_symbols.' . ($settings['currency'] ?? 'USD'))
            ?? '$'
        );
        $paperWidth = (int)($settings['paper_width'] ?? 80);
        $paperWidthPx = ($paperWidth == 58) ? '58mm' : '80mm';
        $bodyFontSize = ($paperWidth == 58) ? '10px' : '11px';
        $h2FontSize = ($paperWidth == 58) ? '12px' : '14px';

        $branchSection = '';
        if ($branchName) {
            $branchSection .= '<p><strong>Branch:</strong> ' . $branchName . '</p>';
            if ($branchCode) $branchSection .= '<p><strong>Branch Code:</strong> ' . $branchCode . '</p>';
            if ($branchAddress) $branchSection .= '<p>' . $branchAddress . '</p>';
            if ($branchPhone) $branchSection .= '<p>' . $branchPhone . '</p>';
            $branchSection .= '<hr style="border:0;border-top:1px dashed #000;margin:4px 0;">';
        }

        $cashierLine = $cashierName ? '<p><strong>Cashier:</strong> ' . $cashierName . '</p>' : '';
        $customerLine = '';
        if ($customerName) {
            $customerLine .= '<p><strong>Customer:</strong> ' . $customerName . '</p>';
            if ($customerPhone) $customerLine .= '<p><strong>Phone:</strong> ' . $customerPhone . '</p>';
        }

        $serviceType = (int)($document['service_type'] ?? 0);
        $tableNumber = htmlspecialchars($document['table_number'] ?? '');
        $dineInEnabled = ($settings['dine_in_enabled'] ?? 'true') !== 'false';
        $takeawayEnabled = ($settings['takeaway_enabled'] ?? 'true') !== 'false';
        $tableMgmtEnabled = ($settings['table_management_enabled'] ?? 'true') !== 'false';

        $serviceLine = '';
        if ($serviceType === 0 && $dineInEnabled) {
            $serviceLine = '<p><strong>Type:</strong> Dine-in</p>';
            if ($tableNumber && $tableMgmtEnabled) {
                $serviceLine .= '<p><strong>Table:</strong> ' . $tableNumber . '</p>';
            }
        } elseif ($serviceType === 1 && $takeawayEnabled) {
            $serviceLine = '<p><strong>Type:</strong> Takeaway</p>';
        }

        $paidChange = '';
        if ((float) $paidAmount > 0) {
            $paidChange .= '<p><span>Paid</span><span>' . $currencySymbol . $paidAmount . '</span></p>';
            if ((float) $changeAmount > 0) {
                $paidChange .= '<p><span>Change</span><span>' . $currencySymbol . $changeAmount . '</span></p>';
            }
        }
        if ((float) $dueAmount > 0) {
            $paidChange .= '<p><span>Due</span><span>' . $currencySymbol . $dueAmount . '</span></p>';
        }

        $qrCode = $this->qrCodeHtml($documentNumber, $documentDate, $grandTotal, $customerName, $paymentMethod, $settings);

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$documentNumber}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Courier New', Courier, monospace;
                    font-size: {$bodyFontSize};
                    width: {$paperWidthPx};
                    margin: 0 auto;
                    padding: 8px;
                    color: #000;
                    line-height: 1.4;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                }
                .header { text-align: center; margin-bottom: 8px; border-bottom: 1px dashed #000; padding-bottom: 6px; }
                .header h2 { font-size: {$h2FontSize}; margin-bottom: 2px; word-break: break-word; }
                .header p { font-size: {$bodyFontSize}; margin-bottom: 1px; word-break: break-word; }
                .branch-info { margin-bottom: 4px; }
                .branch-info p { font-size: {$bodyFontSize}; margin-bottom: 1px; word-break: break-word; }
                .document-info { margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px dashed #000; }
                .document-info p { font-size: {$bodyFontSize}; word-break: break-word; }
                .document-info strong { display: inline-block; width: 40%; }
                table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 6px; }
                table th { font-size: {$bodyFontSize}; text-align: left; border-bottom: 1px solid #000; padding: 2px 3px; }
                table td { font-size: {$bodyFontSize}; padding: 2px 3px; vertical-align: top; text-align: left; overflow: hidden; word-break: break-word; }
                table tbody td { font-weight: bold; }
                th.item, td.name { width: 44%; padding-right: 6px; }
                th.qty, td.qty { width: 18%; }
                th.price, td.price { width: 18%; }
                th.total, td.total { width: 20%; }
                .totals { border-top: 1px dashed #000; padding-top: 4px; margin-bottom: 8px; }
                .totals p { font-size: {$bodyFontSize}; display: flex; align-items: baseline; justify-content: space-between; }
                .totals p span:first-child { text-align: left; }
                .totals p span:last-child { text-align: right; white-space: nowrap; }
                .totals .grand-total { font-size: {$h2FontSize}; font-weight: bold; border-top: 2px solid #000; padding-top: 4px; margin-top: 4px; }
                .totals .grand-total span:last-child { white-space: nowrap; }
                .payment-line { text-align: center; margin-top: 4px; font-weight: bold; }
                .footer { text-align: center; margin-top: 12px; padding-top: 6px; border-top: 1px dashed #000; }
                .footer p { font-size: {$bodyFontSize}; margin-bottom: 2px; }
                .qr-code { text-align: center; margin-top: 8px; }
                .qr-code img { max-width: 100px; max-height: 100px; }
                @page { margin: 0; }
                @media print { body { width: 100%; margin: 0 auto; padding: 4px; } }
            </style>
        </head>
        <body>
            {$this->optionalLogo($logoUrl, $paperWidthPx)}
            <div class="header">
                {$this->optionalTextLine($headerText)}
                <h2>{$companyName}</h2>
                {$this->optionalLine($companyAddress)}
                {$this->optionalLine($companyPhone)}
                {$this->optionalLine($companyEmail)}
            </div>

            <div class="branch-info">
                {$branchSection}
            </div>

            <div class="document-info">
                <p><strong>{$documentType}:</strong> {$documentNumber}</p>
                <p><strong>Date:</strong> {$documentDate}</p>
                {$cashierLine}
                {$customerLine}
                {$serviceLine}
            </div>

            {$this->optionalRefundedBanner($orderStatus)}

            <table>
                <thead>
                    <tr>
                        <th class="item">Item</th>
                        <th class="qty">Qty</th>
                        <th class="price">Price</th>
                        <th class="total">Total</th>
                    </tr>
                </thead>
                <tbody>{$itemRows}</tbody>
            </table>

            <div class="totals">
                <p><span>Subtotal</span><span>{$currencySymbol}{$subtotal}</span></p>
                {$this->optionalTotalRow("Discount", $currencySymbol, $discountAmount)}
                {$this->optionalTotalRow("Tax", $currencySymbol, $taxAmount)}
                <p class="grand-total"><span>TOTAL</span><span>{$currencySymbol}{$grandTotal}</span></p>
                {$paidChange}
            </div>

            <div class="payment-line">
                <p>Payment: {$paymentMethod}</p>
            </div>

            <div class="footer">
                <p>{$footerText}</p>
                <p>{$extraFooter}</p>
                <p>Powered by {{ $companyName }}</p>
            </div>

            {$qrCode}
        </body>
        </html>
        HTML;
    }

    public function buildFromOrder(array $order, array $company, array $settings): string
    {
        $document = [
            'number' => $order['number'] ?? $order['id'] ?? '',
            'date' => $order['created_at'] ?? $order['date'] ?? date('Y-m-d H:i:s'),
            'items' => $order['items'] ?? [],
            'subtotal' => $order['subtotal'] ?? 0,
            'tax_amount' => $order['tax_amount'] ?? 0,
            'discount' => $order['discount'] ?? 0,
            'grand_total' => $order['total'] ?? $order['grand_total'] ?? 0,
            'paid_amount' => $order['paid_amount'] ?? 0,
            'change_amount' => $order['change_amount'] ?? 0,
            'payment_method' => $order['payment_method'] ?? '',
            'payments' => $order['payments'] ?? [],
            'document_type' => $order['document_type'] ?? ['name' => 'Sale'],
            'cashier' => $order['cashier'] ?? '',
            'customer' => $order['customer'] ?? '',
            'branch' => $order['branch'] ?? null,
        ];

        return $this->build($document, $company, $settings);
    }

    /**
     * Build a world-standard A4 invoice/receipt HTML for PDF download/print.
     * Separate design from the thermal receipt: S.No column, "Unit Price" label,
     * bordered bold item table, right-aligned totals, centered header.
     */
    public function buildPdf(array $document, array $company, array $settings): string
    {
        $items = $document['items'] ?? [];
        $payments = $document['payments'] ?? [];

        $itemRows = '';
        $sno = 0;
        foreach ($items as $item) {
            $sno++;
            $qty = number_format($item['quantity'] ?? 0, 2);
            $unitPrice = number_format($item['price'] ?? 0, 2);
            $lineTotal = number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2);
            $productName = htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Item');
            $itemRows .= <<<ROW
            <tr>
                <td class="sno">{$sno}</td>
                <td class="item">{$productName}</td>
                <td class="num">{$qty}</td>
                <td class="num">{$unitPrice}</td>
                <td class="num">{$lineTotal}</td>
            </tr>
            ROW;
        }

        $companyName = htmlspecialchars($company['name'] ?? $settings['company_name'] ?? 'Company Name');
        $companyAddress = htmlspecialchars($company['address'] ?? $settings['company_address'] ?? '');
        $companyPhone = htmlspecialchars($company['phone'] ?? $settings['company_phone'] ?? '');
        $companyEmail = htmlspecialchars($company['email'] ?? $settings['company_email'] ?? '');
        $companyInfo = trim(implode(' · ', array_filter([$companyAddress, $companyPhone, $companyEmail])));

        $documentNumber = htmlspecialchars($document['number'] ?? '');
        $documentDate = htmlspecialchars($document['date'] ?? date('Y-m-d H:i:s'));
        $documentType = htmlspecialchars($document['document_type']['name'] ?? $document['type'] ?? 'Receipt');
        $orderStatus = $document['order_status'] ?? null;

        $subtotal = number_format($document['subtotal'] ?? 0, 2);
        $taxAmount = number_format($document['tax_amount'] ?? 0, 2);
        $discountAmount = number_format($document['discount'] ?? 0, 2);
        $grandTotal = number_format($document['grand_total'] ?? $document['total'] ?? 0, 2);
        $paidAmount = number_format($document['paid_amount'] ?? 0, 2);
        $dueAmount = number_format($document['due_amount'] ?? 0, 2);
        $changeAmount = number_format($document['change_amount'] ?? 0, 2);

        $customerName = htmlspecialchars($document['customer'] ?? 'Walk-in Customer');
        $customerPhone = htmlspecialchars($document['customer_phone'] ?? '');
        $cashierName = htmlspecialchars($document['cashier'] ?? '');

        $paymentMethod = '';
        if (!empty($payments)) {
            $methodNames = [];
            foreach ($payments as $payment) {
                $methodNames[] = htmlspecialchars($payment['payment_type']['name'] ?? $payment['method'] ?? $document['payment_method'] ?? 'Unknown');
            }
            $paymentMethod = implode(', ', array_unique($methodNames));
        }
        if (empty($paymentMethod)) {
            $paymentMethod = htmlspecialchars($document['payment_method'] ?? '');
        }

        $currencySymbol = htmlspecialchars(
            $settings['currency_symbol']
            ?? config('business.currency_symbols.' . ($settings['currency'] ?? 'USD'))
            ?? '$'
        );
        $headerText = htmlspecialchars($settings['receipt_header'] ?? '');
        $footerText = htmlspecialchars($settings['receipt_footer'] ?? 'Thank you for your purchase!');
        $extraFooter = $settings['receipt_extra_footer'] ?? '';
        $logoUrl = !empty($settings['receipt_logo'] ?? '') ? $settings['receipt_logo'] : ($settings['logo'] ?? '');

        $serviceType = (int) ($document['service_type'] ?? 0);
        $tableNumber = htmlspecialchars($document['table_number'] ?? '');
        $dineInEnabled = ($settings['dine_in_enabled'] ?? 'true') !== 'false';
        $takeawayEnabled = ($settings['takeaway_enabled'] ?? 'true') !== 'false';
        $tableMgmtEnabled = ($settings['table_management_enabled'] ?? 'true') !== 'false';

        $serviceLine = '';
        if ($serviceType === 0 && $dineInEnabled) {
            $serviceLine = 'Dine-in' . ($tableNumber && $tableMgmtEnabled ? ' · Table ' . $tableNumber : '');
        } elseif ($serviceType === 1 && $takeawayEnabled) {
            $serviceLine = 'Takeaway';
        }

        $statusText = 'PAID';
        if ($orderStatus === 'refunded') {
            $statusText = 'REFUNDED';
        } elseif (!empty($orderStatus)) {
            $statusText = strtoupper(htmlspecialchars($orderStatus));
        }

        $totals = '';
        $totals .= $this->pdfTotalRow('Subtotal', $currencySymbol . $subtotal);
        if ((float) $discountAmount > 0) {
            $totals .= $this->pdfTotalRow('Discount', $currencySymbol . $discountAmount);
        }
        if ((float) $taxAmount > 0) {
            $totals .= $this->pdfTotalRow('Tax', $currencySymbol . $taxAmount);
        }
        $totals .= $this->pdfTotalRow('GRAND TOTAL', $currencySymbol . $grandTotal, true);
        if ((float) $paidAmount > 0) {
            $totals .= $this->pdfTotalRow('Paid', $currencySymbol . $paidAmount);
            if ((float) $changeAmount > 0) {
                $totals .= $this->pdfTotalRow('Change', $currencySymbol . $changeAmount);
            }
        }
        if ((float) $dueAmount > 0) {
            $totals .= $this->pdfTotalRow('Amount Due', $currencySymbol . $dueAmount);
        }

        $logoHtml = '';
        if (!empty($logoUrl)) {
            $logoHtml = '<div class="logo"><img src="' . htmlspecialchars($logoUrl) . '" alt="Logo"></div>';
        }
        $companyInfoHtml = $companyInfo !== '' ? '<div class="company-info">' . $companyInfo . '</div>' : '';
        $refundedBadge = $orderStatus === 'refunded' ? '<div class="refunded">REFUNDED</div>' : '';
        $cashierHtml = $cashierName !== '' ? '<div><strong>Cashier:</strong> ' . $cashierName . '</div>' : '';
        $serviceHtml = $serviceLine !== '' ? '<div><strong>Service:</strong> ' . $serviceLine . '</div>' : '';
        $paymentHtml = $paymentMethod !== '' ? '<div><strong>Payment:</strong> ' . $paymentMethod . '</div>' : '';
        $qrCode = $this->qrCodeHtml($documentNumber, $documentDate, $grandTotal, $customerName, $paymentMethod, $settings);

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$documentNumber}</title>
            <style>
                @page { size: A4; margin: 12mm; }
                * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                body {
                    font-family: 'Helvetica Neue', Arial, sans-serif;
                    font-size: 13px;
                    color: #111;
                    max-width: 794px;
                    margin: 0 auto;
                    padding: 28px;
                    line-height: 1.5;
                }
                .top { text-align: center; margin-bottom: 16px; }
                .top .logo img { max-width: 150px; max-height: 80px; object-fit: contain; display: block; margin: 0 auto 8px; }
                .top .company-name { font-size: 20px; font-weight: 800; letter-spacing: 0.5px; }
                .top .company-info { font-size: 12px; color: #444; margin-top: 3px; }
                .title {
                    text-align: center; font-size: 18px; font-weight: 800;
                    border-top: 3px solid #111; border-bottom: 3px solid #111;
                    padding: 6px 0; margin-bottom: 16px; letter-spacing: 3px;
                }
                .meta { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
                .meta .label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-bottom: 2px; }
                .meta .bill-to { font-weight: 700; }
                .meta .right { text-align: right; font-weight: 600; }
                .meta .right div { margin-bottom: 2px; }
                .refunded {
                    text-align: center; border: 2px dashed #c00; color: #c00;
                    font-weight: 800; letter-spacing: 4px; padding: 6px; margin-bottom: 14px;
                }
                table.items { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 0; margin-bottom: 18px; }
                table.items th, table.items td {
                    padding: 7px 8px;
                    text-align: left;
                    font-weight: 700;
                    word-break: break-word;
                    border-right: 1px solid #333;
                    border-bottom: 1px solid #333;
                }
                table.items th { border-top: 1px solid #333; background: #eee; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
                table.items th:first-child, table.items td:first-child { border-left: 1px solid #333; }
                table.items th.sno, table.items td.sno { width: 7%; text-align: center; }
                table.items th.item, table.items td.item { width: 45%; }
                table.items th.num, table.items td.num { text-align: right; white-space: nowrap; }
                .totals { max-width: 420px; margin-left: auto; }
                .totals .row { display: flex; justify-content: space-between; padding: 3px 0; font-weight: 700; }
                .totals .row.grand {
                    border-top: 2px solid #111; border-bottom: 2px solid #111;
                    margin-top: 4px; padding: 7px 0; font-size: 15px;
                }
                .payment-line { text-align: center; margin-top: 14px; font-weight: 700; }
                .footer { text-align: center; margin-top: 22px; padding-top: 10px; border-top: 2px solid #111; font-size: 12px; color: #333; }
                .footer p { margin-bottom: 2px; }
                .qr-code { text-align: center; margin-top: 12px; }
                .qr-code img { max-width: 110px; max-height: 110px; }
                @media print { body { max-width: 100%; padding: 0; } }
                @media (max-width: 560px) {
                    body { padding: 14px; }
                    .meta { flex-direction: column; }
                    .meta .right { text-align: left; }
                    table.items th, table.items td { padding: 5px 4px; font-size: 12px; }
                }
            </style>
        </head>
        <body>
            <div class="top">
                {$logoHtml}
                <div class="company-name">{$companyName}</div>
                {$companyInfoHtml}
            </div>

            <div class="title">{$documentType}</div>

            {$refundedBadge}

            <div class="meta">
                <div class="bill-to">
                    <div class="label">Bill To</div>
                    <div>{$customerName}</div>
                    {$this->optionalPdfLine($customerPhone)}
                </div>
                <div class="right">
                    <div><strong>{$documentType} No:</strong> {$documentNumber}</div>
                    <div><strong>Date:</strong> {$documentDate}</div>
                    {$cashierHtml}
                    {$serviceHtml}
                    {$paymentHtml}
                    <div><strong>Status:</strong> {$statusText}</div>
                </div>
            </div>

            <table class="items">
                <thead>
                    <tr>
                        <th class="sno">S. No</th>
                        <th class="item">Item</th>
                        <th class="num">Quantity</th>
                        <th class="num">Unit Price</th>
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>{$itemRows}</tbody>
            </table>

            <div class="totals">
                {$totals}
            </div>

            <div class="payment-line">Payment Method: {$paymentMethod}</div>

            <div class="footer">
                <p>{$footerText}</p>
                <p>{$extraFooter}</p>
                <p>Powered by {$companyName}</p>
            </div>

            {$qrCode}
        </body>
        </html>
        HTML;
    }

    private function pdfTotalRow(string $label, string $value, bool $grand = false): string
    {
        $class = $grand ? 'row grand' : 'row';
        return '<div class="' . $class . '"><span>' . $label . '</span><span>' . $value . '</span></div>';
    }

    private function optionalPdfLine(string $content): string
    {
        $content = trim($content);
        if ($content === '') return '';
        return '<div>' . $content . '</div>';
    }

    private function qrCodeHtml(string $receiptNumber, string $date, string $total, string $customer, string $method, array $settings): string
    {
        $qrEnabled = trim($settings['receipt_qr_enabled'] ?? '', '"\'');
        if ($qrEnabled !== 'true') {
            return '';
        }

        $data = [
            'r' => $receiptNumber,
            'd' => $date,
            't' => $total,
            'c' => $customer ?: 'Walk-in',
            'p' => $method ?: 'cash',
        ];

        $qrText = json_encode($data, JSON_UNESCAPED_SLASHES);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrText);

        return '<div class="qr-code"><img src="' . $qrUrl . '" alt="QR Code"><p style="font-size:9px;">Scan to verify</p></div>';
    }

    private function optionalLine(string $content, string $tag = 'p'): string
    {
        $content = trim($content);
        if ($content === '' || $content === ':') return '';
        return "<{$tag}>{$content}</{$tag}>";
    }

    private function optionalTotalRow(string $label, string $symbol, string $amount): string
    {
        if ((float) $amount === 0.0) return '';
        return "<p><span>{$label}</span><span>{$symbol}{$amount}</span></p>";
    }

    private function optionalLogo(string $logoUrl, string $width): string
    {
        if (empty($logoUrl)) return '';
        return '<div class="header"><img src="' . htmlspecialchars($logoUrl) . '" style="max-width:' . $width . ';max-height:20mm;display:block;margin:0 auto 4px;"></div>';
    }

    private function optionalTextLine(string $text): string
    {
        if (empty(trim($text))) return '';
        return '<p style="margin-bottom:4px;">' . htmlspecialchars($text) . '</p>';
    }

    private function optionalRefundedBanner(?string $orderStatus): string
    {
        if ($orderStatus !== 'refunded') return '';
        return '<div style="text-align:center;padding:8px;margin:8px 0;border:2px dashed #000;font-size:16px;font-weight:bold;text-transform:uppercase;letter-spacing:3px;">REFUNDED</div>';
    }

    /**
     * Build a plain-text receipt suitable for ESC/POS thermal printers.
     * Uses the same data format as build().
     */
    public function buildText(array $document, array $company, array $settings): string
    {
        $items = $document['items'] ?? [];
        $w = ($settings['paper_width'] ?? '80') === '58' ? 32 : 42;
        $d = str_repeat('-', $w);

        $companyName = $company['name'] ?? $settings['company_name'] ?? 'Company';
        $address = $company['address'] ?? $settings['company_address'] ?? '';
        $phone = $company['phone'] ?? $settings['company_phone'] ?? '';

        $orderNum = $document['number'] ?? '';
        $orderDate = $document['date'] ?? date('Y-m-d H:i:s');
        $cashier = $document['cashier'] ?? '';
        $customer = $document['customer'] ?? 'Walk-in Customer';
        $paymentMethod = $document['payment_method'] ?? 'cash';
        $orderStatus = $document['order_status'] ?? null;

        $subtotal = number_format($document['subtotal'] ?? 0, 2);
        $tax = number_format($document['tax_amount'] ?? 0, 2);
        $discount = number_format($document['discount'] ?? 0, 2);
        $total = number_format($document['grand_total'] ?? $document['total'] ?? 0, 2);
        $paid = number_format($document['paid_amount'] ?? 0, 2);
        $change = number_format($document['change_amount'] ?? 0, 2);
        $due = number_format($document['due_amount'] ?? max(0, (float) ($document['grand_total'] ?? $document['total'] ?? 0) - (float) ($document['paid_amount'] ?? 0)), 2);
        $currency = $settings['currency_symbol'] ?? '$';
        $header = $settings['receipt_header'] ?? '';
        $footer = $settings['receipt_footer'] ?? 'Thank you!';
        $serviceType = (int)($document['service_type'] ?? 0);
        $tableNumber = $document['table_number'] ?? '';

        $out = '';
        $out .= str_repeat('=', $w) . "\n";
        $out .= $this->center($companyName, $w) . "\n";
        if ($address) $out .= $this->center($address, $w) . "\n";
        if ($phone) $out .= $this->center($phone, $w) . "\n";
        $out .= str_repeat('=', $w) . "\n";
        if ($header) $out .= $this->center($header, $w) . "\n";

        $out .= sprintf("Date: %s\n", $orderDate);
        $out .= sprintf("Order: %s\n", $orderNum);
        if ($cashier) $out .= sprintf("Cashier: %s\n", $cashier);
        if ($customer) $out .= sprintf("Customer: %s\n", $customer);
        if ($orderStatus === 'refunded') $out .= "*** REFUNDED ***\n";

        if ($serviceType === 0 && ($settings['dine_in_enabled'] ?? 'true') !== 'false') {
            $out .= "Type: Dine-in\n";
            if ($tableNumber) $out .= "Table: {$tableNumber}\n";
        } elseif ($serviceType === 1 && ($settings['takeaway_enabled'] ?? 'true') !== 'false') {
            $out .= "Type: Takeaway\n";
        }

        $out .= $d . "\n";
        $out .= sprintf("%-*s %4s %7s %8s\n", $w - 22, 'Item', 'Qty', 'Price', 'Total');
        $out .= $d . "\n";

        foreach ($items as $item) {
            $name = $item['product_name'] ?? $item['name'] ?? 'Item';
            $qty = number_format($item['quantity'] ?? 0, 2);
            $price = number_format($item['price'] ?? 0, 2);
            $lineTotal = number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2);
            $nameLen = $w - 22;
            if (mb_strlen($name) > $nameLen) $name = mb_substr($name, 0, $nameLen - 2) . '..';
            $out .= sprintf("%-{$nameLen}s %4s %7s %8s\n", $name, $qty, $price, $lineTotal);
        }

        $out .= $d . "\n";
        $valW = $w >= 40 ? 15 : 13;
        $labW = $w - $valW - 1;
        $out .= sprintf("%-{$labW}s %{$valW}s\n", 'SUBTOTAL:', $currency . $subtotal);
        if ((float)$discount > 0) $out .= sprintf("%-{$labW}s %{$valW}s\n", 'DISCOUNT:', '-' . $currency . $discount);
        if ((float)$tax > 0) $out .= sprintf("%-{$labW}s %{$valW}s\n", 'TAX:', $currency . $tax);
        $out .= sprintf("%-{$labW}s %{$valW}s\n", 'GRAND TOTAL:', $currency . $total);
        $out .= $d . "\n";
        $out .= sprintf("%-{$labW}s %{$valW}s\n", 'PAID:', $currency . $paid);
        if ((float)$change > 0) $out .= sprintf("%-{$labW}s %{$valW}s\n", 'CHANGE:', $currency . $change);
        if ((float)$due > 0) $out .= sprintf("%-{$labW}s %{$valW}s\n", 'DUE:', $currency . $due);
        $out .= $d . "\n";
        $out .= sprintf("Payment: %s\n", strtoupper($paymentMethod));
        $out .= str_repeat('=', $w) . "\n";
        $out .= $this->center($footer, $w) . "\n";
        $out .= str_repeat('=', $w) . "\n\n\n\n";

        return $out;
    }

    private function center(string $text, int $width): string
    {
        $len = mb_strwidth($text);
        if ($len >= $width) return $text;
        $left = intdiv($width - $len, 2);
        $right = $width - $len - $left;
        return str_repeat(' ', $left) . $text . str_repeat(' ', $right);
    }
}

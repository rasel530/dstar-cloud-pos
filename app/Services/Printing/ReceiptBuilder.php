<?php

namespace App\Services\Printing;

class ReceiptBuilder
{
    public function build(array $document, array $company, array $settings): string
    {
        $items = $document['items'] ?? [];
        $payments = $document['payments'] ?? [];

        $itemRows = '';
        foreach ($items as $item) {
            $qty = number_format($item['quantity'] ?? 0, 2);
            $price = number_format($item['price'] ?? 0, 2);
            $lineTotal = number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2);
            $productName = $item['product_name'] ?? $item['name'] ?? 'Item';
            $itemRows .= <<<ROW
            <tr>
                <td class="name">{$productName}</td>
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

        $subtotal = number_format($document['subtotal'] ?? 0, 2);
        $taxAmount = number_format($document['tax_amount'] ?? 0, 2);
        $discountAmount = number_format($document['discount'] ?? 0, 2);
        $grandTotal = number_format($document['grand_total'] ?? $document['total'] ?? 0, 2);
        $paidAmount = number_format($document['paid_amount'] ?? 0, 2);
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
        $logoUrl = $settings['logo'] ?? '';
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
        $serviceLine = '';
        if ($serviceType === 0) {
            $serviceLine = '<p><strong>Type:</strong> Dine-in</p>';
            if ($tableNumber) $serviceLine .= '<p><strong>Table:</strong> ' . $tableNumber . '</p>';
        } elseif ($serviceType === 1) {
            $serviceLine = '<p><strong>Type:</strong> Takeaway</p>';
        }

        $paidChange = '';
        if ((float) $paidAmount > 0) {
            $paidChange .= '<p><span>Paid</span><span>' . $currencySymbol . $paidAmount . '</span></p>';
            if ((float) $changeAmount > 0) {
                $paidChange .= '<p><span>Change</span><span>' . $currencySymbol . $changeAmount . '</span></p>';
            }
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
                table th { font-size: {$bodyFontSize}; text-align: left; border-bottom: 1px solid #000; padding: 2px 0; }
                table td { font-size: {$bodyFontSize}; padding: 2px 0; vertical-align: top; text-align: left; overflow: hidden; word-break: break-word; }
                th.item, td.name { width: 44%; }
                th.qty, td.qty { width: 18%; }
                th.price, td.price { width: 18%; }
                th.total, td.total { width: 20%; }
                .totals { border-top: 1px dashed #000; padding-top: 4px; margin-bottom: 8px; }
                .totals p { font-size: {$bodyFontSize}; display: flex; }
                .totals p span:first-child { width: 80%; text-align: right; padding-right: 4px; }
                .totals p span:last-child { width: 20%; text-align: left; }
                .totals .grand-total { font-size: 13px; font-weight: bold; border-top: 2px solid #000; padding-top: 4px; margin-top: 4px; }
                .payment-line { text-align: center; margin-top: 4px; font-weight: bold; }
                .footer { text-align: center; margin-top: 12px; padding-top: 6px; border-top: 1px dashed #000; }
                .footer p { font-size: {$bodyFontSize}; margin-bottom: 2px; }
                .qr-code { text-align: center; margin-top: 8px; }
                .qr-code img { max-width: 100px; max-height: 100px; }
                @media print { body { margin: 0; padding: 4px; width: 100%; } }
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
                {$this->optionalTotalRow("Tax", $currencySymbol, $taxAmount)}
                {$this->optionalTotalRow("Discount", $currencySymbol, $discountAmount)}
                <p class="grand-total"><span>TOTAL</span><span>{$currencySymbol}{$grandTotal}</span></p>
                {$paidChange}
            </div>

            <div class="payment-line">
                <p>Payment: {$paymentMethod}</p>
            </div>

            <div class="footer">
                <p>{$footerText}</p>
                <p>{$extraFooter}</p>
                <p>Powered by DStar POS</p>
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
}

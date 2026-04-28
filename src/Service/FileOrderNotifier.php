<?php

declare(strict_types=1);

namespace App\Service;

final class FileOrderNotifier implements OrderNotifierInterface
{
    public function __construct(
        private readonly string $businessEmail,
        private readonly string $logPath
    ) {
    }

    public function notify(int $purchaseNo, string $buyerEmail, string $deliveryAddress, array $items, float $total): void
    {
        $lines = [
            'Purchase order #' . $purchaseNo,
            'Buyer: ' . $buyerEmail,
            'Delivery: ' . $deliveryAddress,
            'Items:',
        ];

        foreach ($items as $item) {
            $lines[] = sprintf(
                '- %s x %d @ %s',
                $item['product']['description'],
                $item['quantity'],
                money($item['product']['price'])
            );
        }

        $lines[] = 'Total: ' . money($total);
        $entry = sprintf(
            "[%s]\nTo buyer: %s\nTo business: %s\n%s\n\n",
            date('c'),
            $buyerEmail,
            $this->businessEmail,
            implode(PHP_EOL, $lines)
        );

        file_put_contents($this->logPath, $entry, FILE_APPEND);
    }
}

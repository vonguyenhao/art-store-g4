<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\OrderRepository;
use RuntimeException;

final class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderRepository $orders,
        private readonly OrderNotifierInterface $notifier
    ) {
    }

    public function submit(array $post): int
    {
        [$items, $total] = $this->cart->items();
        if (!$items) {
            throw new RuntimeException('Your cart is empty.');
        }

        $customer = $this->validatedCustomer($post);
        $deliveryAddress = implode(', ', [
            $customer['address'],
            $customer['city'],
            $customer['state'],
            $customer['postcode'],
            $customer['country'],
        ]);

        $purchaseNo = $this->orders->create($customer, $deliveryAddress, $items, $total);
        $this->notifier->notify($purchaseNo, $customer['email'], $deliveryAddress, $items, $total);
        $this->cart->clear();

        return $purchaseNo;
    }

    private function validatedCustomer(array $post): array
    {
        $required = ['email', 'first_name', 'last_name', 'address', 'city', 'state', 'postcode', 'country', 'phone'];
        foreach ($required as $field) {
            if (trim((string) ($post[$field] ?? '')) === '') {
                throw new RuntimeException('Please complete all required checkout fields.');
            }
        }

        $email = filter_var($post['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new RuntimeException('Please enter a valid email address.');
        }

        return [
            'email' => $email,
            'title' => trim($post['title'] ?? ''),
            'first_name' => trim($post['first_name']),
            'last_name' => trim($post['last_name']),
            'address' => trim($post['address']),
            'city' => trim($post['city']),
            'state' => trim($post['state']),
            'postcode' => trim($post['postcode']),
            'country' => trim($post['country']),
            'phone' => trim($post['phone']),
        ];
    }
}

<?php

namespace Netflex\Commerce\Contracts;

interface CartItem
{
    public function getCartItemLineNumber(): int;

    public function getCartItemProductId(): int;
    public function setCartItemProductId(int $productId): void;

    public function getCartItemProductName(): string;
    public function setCartItemProductName(string $productName): void;

    public function getCartItemVariantId(): int;
    public function setCartItemVariantId(int $variantId): void;

    public function getCartItemVariantName(): ?string;
    public function setCartItemVariantName(string $variantName): void;

    public function getCartItemQuantity(): int;
    public function setCartItemQuantity(int $quantity): void;

    public function getCartItemPrice(): float;
    public function setCartItemPrice(float $price): void;

    public function getCartItemSubtotal(): float;
    public function getCartItemTax(): float;
    public function getCartItemTotal(): float;

    public function getCartItemTaxRate(): float;
    public function setCartItemTaxRate(float $taxRate): void;

    public function saveCartItem(): void;
    public function deleteCartItem(): void;

    public function getCartItemProperty(string $key);
    public function setCartItemProperty(string $key, $value): void;

    public function getCartItemProperties(): array;

    public function addCartItemDiscount(Discount $discount);
}

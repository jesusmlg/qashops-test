<?php

class Product
{
    public function getOrdersQuantity(){
        return OrderLine::find()->select('SUM(quantity) as quantity')
                                ->joinWith('order')
                                ->where("(order.status = '" . Order::STATUS_PENDING . "' OR order.status = '" . Order::STATUS_PROCESSING . "' OR order.status = '" . Order::STATUS_WAITING_ACCEPTANCE . "') AND order_line.product_id = $productId")
                                ->scalar();
    }

    public function getBlockedStock(){
        BlockedStock::find()->select('SUM(quantity) as quantity')
                            ->joinWith('shoppingCart')
                            ->where("blocked_stock.product_id = $productId AND blocked_stock_date > '" . date('Y-m-d H:i:s') . "' AND (shopping_cart_id IS NULL OR shopping_cart.status = '" . ShoppingCart::STATUS_PENDING . "')")
                            ->scalar();
    }
    
    public function applySecurity($quantity)
    {
        return ShopChannel::applySecurityStockConfig($quantity, @$securityStockConfig->mode, @$securityStockConfig->quantity);
    }

    public function getBlockedStock()

    public static function stock(
        $productId,
        $quantityAvailable,
        $cache = false,
        $cacheDuration = 60,
        $securityStockConfig = null
    ) {
        if ($cache) {
            // Obtenemos el stock bloqueado por pedidos en curso
            $ordersQuantity = OrderLine::getDb()->cache(function ($db) use ($productId) {
                return getOrdersQuantity();
            }, $cacheDuration);

            // Obtenemos el stock bloqueado
            $blockedStockQuantity = BlockedStock::getDb()->cache(function ($db) use ($productId) {
                return getBlockedStock();
            }, $cacheDuration);
        } else {
            // Obtenemos el stock bloqueado por pedidos en curso
            $ordersQuantity = getOrdersQuantity();

            // Obtenemos el stock bloqueado
            $blockedStockQuantity = getBlockedStock();
        }

        if($quantityAvailable >= 0)
        {
            $ordersQuantity = (isset($ordersQuantity)) ? $ordersQuantity : 0;
            $blockedStockQuantity = (isset($blockedStockQuantity)) ? $blockedStockQuantity : 0;

            $quantity = $quantityAvailable - @$ordersQuantity - @$blockedStockQuantity;
            $quantity = (!empty($securityStockConfig)) ? applySecurity($quantity) : $quantity;
            
            $quantity = ($quantity > 0) ? $quantity : 0;
        }
        else
        {
            $quantity  = $quantityAvailable;
        }

        return $quantity;


        // Calculamos las unidades disponibles
        if (isset($ordersQuantity) || isset($blockedStockQuantity)) {
            if ($quantityAvailable >= 0) {
                $quantity = $quantityAvailable - @$ordersQuantity - @$blockedStockQuantity;                
                $quantity = (!empty($securityStockConfig)) ? applySecurity($quantity) : $quantity;

                return $quantity > 0 ? $quantity : 0;

            } elseif ($quantityAvailable < 0) {
                return $quantityAvailable;
            }
        } else {
            if ($quantityAvailable >= 0) {
                if (!empty($securityStockConfig)) {
                    $quantityAvailable = applySecurity($quantityAvailable);
                }
                $quantityAvailable = $quantityAvailable > 0 ? $quantityAvailable : 0;
            }
            return $quantityAvailable;
        }
        return 0;
    }
}


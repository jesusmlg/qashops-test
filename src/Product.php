<?php

class Product
{
	public static function stock(
		$productId,
		$quantityAvailable,
		$cache = false,
		$cacheDuration = 60,
		$securityStockConfig = null
	) {

		$ordersQuantity = getOrdersQuantity($productId, $cache, $cacheDuration);
		$blockedStockQuantity = getBlockedStockCache($productId, $cache, $cacheDuration);

		//si la cantidad disponible es mayor obtenamos los datos y le restamos los no disponibles
		if($quantityAvailable >= 0)
		{
			$quantityAvailable = $quantityAvailable - @$ordersQuantity - @$blockedStockQuantity;
			//apliamos los parametros de seguridad si corresponde
            if ((!empty($securityStockConfig))) {
                $quantityAvailable = applySecurity($quantityAvailable, $securityStockConfig);
            } else {
                $quantityAvailable = $quantityAvailable;
            }

			$quantityAvailable = ($quantityAvailable > 0) ? $quantityAvailable : 0;
		}

		return $quantityAvailable;
	}

	public function getOrdersQuantity($productId, $cache, $cacheDuration){
		//guardamos en una variable la función para obtener el numero de pedidos
		$ordersFunc = function() use ($productId){
			OrderLine::find()
                ->select('SUM(quantity) as quantity')
                ->joinWith('order')
                ->where("(order.status = '" . Order::STATUS_PENDING .
                    "' OR order.status = '" . Order::STATUS_PROCESSING .
                    "' OR order.status = '" . Order::STATUS_WAITING_ACCEPTANCE .
                    "') AND order_line.product_id = $productId")
                ->scalar();
		};
		//buscamos o no en la caché en función de la petición con la función almacenada en la variable
		if($cache)
			$orders = BlockedStock::getDb()->cache(function () use ($productId, $ordersFunc ) {
				return $ordersFunc();
			}, $cacheDuration);
		else
			$orders = $ordersFunc();

		return (isset($orders)) ? $orders : 0;
	}
	
	public function getBlockedStock($productId, $cache, $cacheDuration){
		//guardamos en una variable la función para obtener el numero de productos bloqueados
		$blockedsFunc = function() use ($productId){
			BlockedStock::find()
                ->select('SUM(quantity) as quantity')
                ->joinWith('shoppingCart')
                ->where("blocked_stock.product_id = $productId AND blocked_stock_date > '" .
                    date('Y-m-d H:i:s') . "' AND (shopping_cart_id IS NULL OR shopping_cart.status = '" .
                    ShoppingCart::STATUS_PENDING . "')")
                ->scalar();
		};

		//buscamos o no en la caché en función de la petición con la función almacenada en la variable
		if($cache)
			$blocked = BlockedStock::getDb()->cache(function () use ($productId, $blockedsFunc) {
				return $blockedsFunc(); 
			}, $cacheDuration);
		else
			$blocked = $blockedsFunc();

		return (isset($blocked)) ? $blocked : 0;
	}

	public function applySecurity($quantity,$securityStockConfig)
	{
		return ShopChannel::applySecurityStockConfig(
		    $quantity,
            @$securityStockConfig->mode,
            @$securityStockConfig->quantity
        );
	}
}


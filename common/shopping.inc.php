<?php


function FormatPrice($price, $show_dollarsign=true, $short=false) {
// turns 400 (internal database price) into $4.00
	$str = "";
	if( $price !== "" ) {
		if( $show_dollarsign ) { $str .= "$"; }
		if( $short && ($price/100) > 99 && ($price/100)==intval($price/100) ) {
			// if the price is more than $99, and there are no cents, then just show $20
			$str .= sprintf("%d",($price/100));
		} else {
			$str .= sprintf("%0.2f",($price/100));
		}
	}
	return $str;
}

function InternalPrice($price) {
// turns $4.00 into internal format (400)
	$price = str_replace("$","",$price);
	return $price * 100;

	// There is a bizarre floating-point bug with intval, where
	// 8.95 * 100 is 894.99999. We'll try not converting to int first.
	// Hopefully only two decimals were specified anyway, and if not,
	// MySQL should be able to convert a float to an int. Looks like
	// MySQL even rounds the number to an integer. 894.99 -> 895.
	//return intval($price * 100);
}

function ShowPrice($sku, $short=false) {
global $DB;

	$info = $DB->SingleQuery("SELECT * FROM inventory WHERE sku='$sku'");
	$issale = $DB->SingleQuery("SELECT COUNT(sku) AS num FROM sale WHERE sku='$sku'");
	if( $issale['num'] == 1 ) {
		if( $info['original_price'] > $info['price'] ) {
			$price  = '<div class="reduced_price">'.FormatPrice($info['price'],true,$short).'</div>';
			$price .= '<div class="original_price">'.FormatPrice($info['original_price'],true,$short).'</div>';
		} else {
			$price = FormatPrice($info['price'],true,$short);
		}
	} else {
		$price = FormatPrice($info['price'],true,$short);
	}
	return $price;
}

function AddToCartLink($sku, $with_text=false, $quantity=1) {
global $SITE;

	$sku = urlencode($sku);
	$add_link = $SITE->add_to_cart_link($sku, $quantity);

	if( $quantity > 0 ) {
		$img = "";
		$img .= "<a href=\"$add_link\">";
		$img .= $SITE->cart_img();
		$img .= "</a>";
		$text = "<a href=\"$add_link\" class=\"add_to_basket\">Add to<br>".$SITE->cart_text()."</a>";
	} else {
		$img = "";
		$img .= $SITE->cart_img('no');
		$text = "<div class=\"add_to_basket\">Add to<br>".$SITE->cart_text().'</div>';
	}

	if( $with_text ) {
		$str = "<table><tr>";
		$str .= "<td>".$img."</td>";
		$str .= "<td>".$text."</td>";
		$str .= "</table>";
	} else {
		$str = $img;
	}

	return $str;
}

function StockText($quantity) {
	if( $quantity > 0 ) {
		return $quantity;
	} else {
		return "Backordered";
	}
}


?>
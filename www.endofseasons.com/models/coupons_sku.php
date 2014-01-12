<?php

class CouponsSku extends AppModel {
	var $name = 'CouponsSku';
	var $belongsTo = array('Coupon', 'Sku');
}


?>
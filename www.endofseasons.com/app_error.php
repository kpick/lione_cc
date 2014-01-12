<?php
	class AppError extends ErrorHandler {
		function modalError($params) {
			foreach($params as $key=>$value ) {
				$this->controller->set($key, $value );
			}
			
			if(! array_key_exists( 'modal_title', $params ) && array_key_exists( 'title', $params ) ) {
				$this->controller->set( 'modal_title', $params['title'] );
			}
			$this->_outputMessage('modal_error');
		}
	}	
?>
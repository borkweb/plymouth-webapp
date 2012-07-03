<?php

namespace PSU\Webapp;

class GoHost extends Host {
	public function routes() {
		require PSU_BASE_DIR . '/routes/go/routes.php';
	}
}

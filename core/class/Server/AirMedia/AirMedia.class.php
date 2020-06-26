<?php
class AirMedia
{
	/*public function airmediaConfig()
	{
		$parametre["enabled"] = $this->getIsEnable();
		$parametre["password"] = $this->getConfiguration('password');
		$return = self::fetch('/api/v3/airmedia/config/', $parametre, "PUT");

		if ($return['success'])
			return $return['result'];
		else
			return false;
	}

	public static function airmediaReceivers()
	{
		$return = self::fetch('/api/v3/airmedia/receivers/');

		if ($return['success'])
			return $return['result'];
		else
			return false;
	}

	public function AirMediaAction($receiver, $action, $media_type, $media = null)
	{
		if ($receiver != "" && $media_type != null) {
			log::add('Freebox_OS', 'debug', 'AirMedia Start Video: ' . $media);
			$parametre["action"] = $action;
			$parametre["media_type"] = $media_type;
			if ($media != null)
				$parametre["media"] = $media;
			$parametre["password"] = $this->getConfiguration('password');
			$return = self::fetch('/api/v3/airmedia/receivers/' . ($receiver) . '/', $parametre, 'POST');
			if ($return['success'])
				return true;
			else
				return false;
		} else
			return false;
	}*/
}

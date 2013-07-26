<?php

namespace GettextTranslator\DI;

use Nette;


if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
}

class Extension extends Nette\DI\CompilerExtension
{
	/** @var array */
	private $defaults = array(
		'lang' => 'en',
		'files' => array(),
		'layout' => 'horizontal',
		'height' => 450
	);


	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$translator = $builder->addDefinition($this->prefix('translator'));
		$translator->setClass('GettextTranslator\Gettext', array('@session', '@cacheStorage', '@httpResponse'));
		$translator->addSetup('setSupportedLangs', $config['supportedLangs']);
		$translator->addSetup('setLang', $config['lang']);
		$translator->addSetup('setProductionMode', $builder->expand('%productionMode%'));

		// at least one language file must be defined
		if (count($config['files']) === 0) {
			throw new InvalidConfigException('Language file(s) must be defined.');
		}
		foreach ($config['files'] as $id => $file) {
			$translator->addSetup('addFile', $file, $id);
		}

		$translator->addSetup('GettextTranslator\Panel::register', array('@application', '@self', '@session', '@httpRequest', $config['layout'], $config['height']));
	}

}

class InvalidConfigException extends Nette\InvalidStateException {

}

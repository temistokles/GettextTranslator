<?php

namespace NetteTranslator\DI;

use Nette;

class Extension extends Nette\Config\CompilerExtension
{
	/** @var array */
	private $defaults = array(
		'lang' => 'en',
		'files' => array(),
		'useCache' => FALSE,
		'layout' => 'horizontal',
		'height' => 450
	);


	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$translator = $builder->addDefinition($this->prefix('translator'));

		$translator->setClass('NetteTranslator\Gettext', array('@session', '@cacheStorage', '@httpResponse'));
		$translator->addSetup('setLang', $config['lang']);
		$translator->addSetup('setCacheUse', $config['useCache']);
		foreach ($config["files"] as $id => $file) {
			$translator->addSetup('addFile', $file, $id);
		}

		$translator->addSetup('NetteTranslator\Panel::register', array('@application', '@self', '@session', '@httpRequest', $config['layout'], $config['height']));
	}

}

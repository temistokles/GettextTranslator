<?php

namespace NetteTranslator\DI;

/**
 * DI extension for NetteTranslator
 *
 * @author: Paulík Miroslav
 */
class NetteTranslatorExtension extends \Nette\Config\CompilerExtension
{

	public $defaults = array(
		'lang'      => 'en',
		'cacheMode' => Gettext::CACHE_DISABLE,
		'files'     => array()
	);

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$translator = $builder->addDefinition($this->prefix('translator'));
		$translator->setClass('NetteTranslator\Gettext', array(
			'@session',
			'@cacheStorage',
			'@httpResponse'
		));
		$translator->addSetup('setLang', $config['lang']);
		foreach ($config["files"] as $id => $file) {
			$translator->addSetup('addFile', $file, $id);
		}
		$translator->addSetup('NetteTranslator\Panel::register');
	}
}

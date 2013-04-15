<?php

namespace NetteTranslator;

use Nette;

class Panel extends Nette\Object implements Nette\Diagnostics\IBarPanel
{
	const XHR_HEADER = 'X-Translation-Client';
	const LANGUAGE_KEY = 'X-GettextTranslator-Lang';
	const FILE_KEY = 'X-GettextTranslator-File';

	/** @var string */
	private $layout;

	/** @var int */
	private $height;

	/** @var Nette\Application\Application */
	private $application;

	/** @var NetteTranslator\Gettext */
	private $translator;

	/** @var Nette\Http\SessionSection */
	private $sessionStorage;

	/** @var Nette\Http\Request */
	private $httpRequest;


	/**
	 * @param Nette\Application\Application
	 * @param Nette\Translator\Gettext 
	 * @param Nette\Http\Session
	 * @param Nette\Http\Request
	 * @param string
	 * @param int
	 */
	public function __construct(Nette\Application\Application $application, Gettext $translator, Nette\Http\Session $session, Nette\Http\Request $httpRequest, $layout, $height)
	{
		$this->application = $application;
		$this->translator = $translator;
		$this->sessionStorage = $session->getSection(Gettext::$namespace);
		$this->httpRequest = $httpRequest;
		$this->height = $height;
		$this->layout = $layout;

		$this->processRequest();
	}


	/**
	 * Return's panel ID
	 * @return string
	 */
	public function getId()
	{	
		return __CLASS__;
	}


	/**
	 * Returns the code for the panel tab.
	 * @return string
	 */
	public function getTab()
	{
		ob_start();
		require __DIR__ . '/tab.latte';
		return ob_get_clean();
	}


	/**
	 * Returns the code for the panel itself.
	 * @return string
	 */
	public function getPanel()
	{
		$files = array_keys($this->translator->getFiles());
		$activeFile = $this->getActiveFile($files);

		$strings = $this->translator->getStrings();
		$untranslatedStack = isset($this->sessionStorage['stack']) ? $this->sessionStorage['stack'] : array();
		foreach ($strings as $string => $data) {
			if (!$data) {
				$untranslatedStack[$string] = FALSE;
			}
		}
		$this->sessionStorage['stack'] = $untranslatedStack;

		foreach ($untranslatedStack as $string => $value) {
			if (!isset($strings[$string])) {
				$strings[$string] = FALSE;
			}
		}

		
		$translator = $this->translator;

		ob_start();
		require __DIR__ . '/panel.latte';
		return ob_get_clean();
	}


	/**
	 * Handles an incomuing request and saves the data if necessary.
	 */
	private function processRequest()
	{
		if ($this->httpRequest->isPost() && $this->httpRequest->isAjax() && $this->httpRequest->getHeader(self::XHR_HEADER)) {
			$data = json_decode(file_get_contents('php://input'));

			if ($data) {
				if ($this->sessionStorage) {
					$stack = isset($this->sessionStorage['stack']) ? $this->sessionStorage['stack'] : array();
				}

				$this->translator->lang = $data->{self::LANGUAGE_KEY};
				$file = $data->{self::FILE_KEY};
				unset($data->{self::LANGUAGE_KEY}, $data->{self::FILE_KEY});

				foreach ($data as $string => $value) {
					$this->translator->setTranslation($string, $value, $file);
					if ($this->sessionStorage && isset($stack[$string])) {
						unset($stack[$string]);
					}
				}
				$this->translator->save($file);

				if ($this->sessionStorage) {
					$this->sessionStorage['stack'] = $stack;
				}
			}

			exit;
		}
	}


	/**
	 * Return an ordinal number suffix
	 * @param string $count
	 * @return string
	 */
	protected function ordinalSuffix($count)
	{
		switch (substr($count, -1)) {
			case '1':
				return 'st';
				break;
			case '2':
				return 'nd';
				break;
			case '3':
				return 'rd';
				break;
			default:
				return 'th';
				break;
		}
	}


	/**
	 * Register this panel
	 * @param Nette\Application\Application
	 * @param GettextTranslator\Gettext
	 * @param Nette\Http\Session
	 * @param Nette\Http\Request
	 * @param int $layout
	 * @param int $height
	 */
	public static function register(Nette\Application\Application $application, Gettext $translator, Nette\Http\Session $session, Nette\Http\Request $httpRequest, $layout, $height) 
	{
		Nette\Diagnostics\Debugger::$bar->addPanel(new static($application, $translator, $session, $httpRequest, $layout, $height));
	}


	/**
	 * Get active file name
	 * @param array
	 * @return string
	 */
	private function getActiveFile($files)
	{
		$requestCount = count($this->application->requests);
		// $presenterName = ($requestCount > 0) ? $this->requests[$requestCount - 1]->presenterName : NULL;
		$presenterName = $this->application->presenter->name;

		$module = (!$presenterName) ?: strtolower(str_replace(':', '.', ltrim(substr($presenterName, 0, -(strlen(strrchr($presenterName, ':')))), ':')));

		$activeFile = (in_array($module, $files)) ? $module : $files[0];

		return $activeFile;
	}

}

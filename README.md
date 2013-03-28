Nette Translator
===

This is short manual how to use Nette Translator in the newest Nette 2.0 in its most simple version. No need to edit or operate with .po/.mo files required.

Actual info and manual: [http://pla.nette.org/cs/zprovozneni-prekladace-nettetranslator](http://pla.nette.org/cs/zprovozneni-prekladace-nettetranslator)

Instalation
===

config.neon
----
    netteTranslator:
        lang: cs
        files:
            front: %appDir%/lang
		# optional with defaults
		layout: horizontal # or: vertical
		height: 450


Bootstrap.php
----
    // this is new
    $configurator->onCompile[] = function ($configurator, $compiler) {
        $compiler->addExtension('netteTranslator', new NetteTranslator\DI\Extension);
    };
    
    // put new lines before the following line
    $container = $configurator->createContainer();


Or you can use *extensions* part in *config.neon* since **Nette 2.1-dev** instead of registration in *bootstrap.php*.

**config.neon**

	extensions:
		netteTranslator: NetteTranslator\DI\Extension
    

---


Usage
===

BasePresenter.php
----
Basic usage + language change

    /** @persistent */
    public $lang;
    
    /** @var NetteTranslator\Gettext */
    protected $translator;
    
    
    /**
     * Inject translator
     * @param NetteTranslator\Gettext
     */
    public function injectTranslator(NetteTranslator\Gettext $translator)
    {
        $this->translator = $translator;
    }


    public function createTemplate($class = NULL)
    {
    	$template = parent::createTemplate($class);
    
    	// if not set, the default language will be used
    	if (!isset($this->lang)) {
    		$this->lang = $this->translator->getLang();
    	}
    
    	$this->translator->setLang($this->lang);
    	$template->setTranslator($this->translator);
    
    	return $template;
    }



---

**Authors in alphabetic order**

- Josef Kufner (jk@frozen-doe.net)
- Miroslav Paulík (https://github.com/castamir)
- Roman Sklenář (http://romansklenar.cz)
- Miroslav Smetana
- Jan Smitka
- Patrik Votoček (patrik@votocek.cz)
- Tomáš Votruba (tomas.vot@gmail.com)
- Václav Vrbka (gmvasek@php-info.cz)


Under *New BSD License*

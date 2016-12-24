<?php

namespace Markette\GopayInline\Bridges\Nette\DI;

use Markette\GopayInline\Client;
use Markette\GopayInline\Config;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Nette\Utils\Validators;

class GopayExtension extends CompilerExtension
{

	/** @var array */
	private $defaults = array(
		'goId' => NULL,
		'clientId' => NULL,
		'clientSecret' => NULL,
		'test' => TRUE,
	);

	/**
	 * Register services
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		Validators::assertField($config, 'goId', 'string|number');
		Validators::assertField($config, 'clientId', 'string|number');
		Validators::assertField($config, 'clientSecret', 'string');
		Validators::assertField($config, 'test', 'bool');

		$builder->addDefinition($this->prefix('client'))
			->setClass('Markette\GopayInline\Client', array(
				new Statement('Markette\GopayInline\Config', array(
					$config['goId'],
					$config['clientId'],
					$config['clientSecret'],
					$config['test'] !== FALSE ? Config::TEST : Config::PROD,
				)),
			));
	}

}

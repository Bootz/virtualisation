<?php
/**
 * Part of the Joomla Virtualisation Package
 *
 * @copyright  Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Virtualisation\Service;

use Joomla\Virtualisation\Template;

/**
 * Class PostgreSql
 *
 * @package  Joomla\Virtualisation
 * @since    __DEPLOY_VERSION__
 */
class PostgreSql extends AbstractService
{
	protected $setup = [];

	/**
	 * Get the setup (suitable for docker-compose files)
	 *
	 * @return  array
	 */
	public function getSetup()
	{
		$config             = reset($this->configs);
		$name               = 'postgresql-' . $this->version;
		$dockerPath         = $this->dockyard . '/' . $name;
		$this->setup[$name] = [
			'image'       => 'postgres:' . $this->version,
			'volumes'     => [
				getcwd() . "/$dockerPath:/docker-entrypoint-initdb.d",
			],
			'environment' => [
				'POSTGRES_DB'       => $config->get('postgresql.name'),
				'POSTGRES_USER'     => $config->get('postgresql.user'),
				'POSTGRES_PASSWORD' => $config->get('postgresql.password'),
			],
		];

		return $this->setup;
	}

	/**
	 * Prepare the dockyard
	 *
	 * @return  void
	 */
	public function prepare()
	{
		$template = new Template(__DIR__ . '/template/postgresql/createdb.sql');

		foreach ($this->configs as $config)
		{
			$databaseName = $config->get('database.name');

			$template->setVariables(
				[
					'postgresql.name' => $databaseName,
					'postgresql.user' => $config->get('postgresql.user'),
				]
			);
			$template->write("{$this->dockyard}/postgresql-{$this->version}/$databaseName.sql");
		}
	}
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Class_rfqwp_Loader
{
	public function __construct()
	{
		$this->actions = array();
		$this->filters = array();
	}

	public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1)
	{
		$this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
	}

	private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
	{
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);
		return $hooks;
	}

	public function run()
	{

		foreach ($this->actions as $hook) {
			add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
		}
	}
}

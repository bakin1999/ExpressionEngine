<?php
/**
 * This source file is part of the open source project
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2019, EllisLab Corp. (https://ellislab.com)
 * @license   https://expressionengine.com/license Licensed under Apache License, Version 2.0
 */

namespace EllisLab\ExpressionEngine\Controller\Jumps;

use CP_Controller;

/**
 * Member Create Controller
 */
class Files extends Jumps {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Publish Jump Data
	 */
	public function index()
	{
		// Should never be here without another segment.
		show_error(lang('unauthorized_access'), 403);
	}

	public function create()
	{
		$channels = $this->loadChannels();

		$this->sendResponse($channels);
	}

	public function view()
	{
		$directories = $this->loadDirectories(ee()->input->post('searchString'));

		$response = array();

		foreach ($directories as $directory) {
			$id = $directory->getId();

			$response['viewFilesIn' . $directory->getId()] = array(
				'icon' => 'fa-eye',
				'command' => $directory->name,
				'command_title' => '<b>' . $directory->name . '</b>',
				'dynamic' => false,
				'addon' => false,
				'target' => ee('CP/URL')->make('files/directory/' . $directory->getId())->compile()
			);
		}

		$this->sendResponse($response);
	}

	public function directories()
	{
		$directories = $this->loadDirectories(ee()->input->post('searchString'));

		$response = array();

		foreach ($directories as $directory) {
			$id = $directory->getId();

			$response['editEntry' . $directory->getId()] = array(
				'icon' => 'fa-pencil-alt',
				'command' => $directory->name,
				'command_title' => '<b>' . $directory->name . '</b>',
				'dynamic' => false,
				'addon' => false,
				'target' => ee('CP/URL')->make('files/uploads/edit/' . $directory->getId())->compile()
			);
		}

		$this->sendResponse($response);
	}

	private function loadDirectories($searchString = false)
	{
		$directories = ee('Model')->get('UploadDestination');

		if (!empty($searchString)) {
			// Break the search string into individual keywords so we can partially match them.
			$keywords = explode(' ', $searchString);

			foreach ($keywords as $keyword) {
				$directories->filter('name', 'LIKE', '%' . $keyword . '%');
			}
		}

		return $directories->order('name', 'ASC')->limit(16)->all();
	}
}

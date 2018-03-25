<?php

/**
 * @copyright  Sven Rhinow 2011-2017
 * @author     sr-tag Sven Rhinow Webentwicklung <http://www.sr-tag.de>
 * @package    beekeeping_management
 * @license    LGPL
 * @filesource
 */

/**
 * Class ModuleBkmSetup
 * Back end module "setup".
 */
class ModuleBkmSetup extends \BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_bkm_setup';

	/**
	 * Isotope modules
	 * @var array
	 */
	protected $arrModules = array();


	/**
	 * Generate the module
	 * @return string
	 */
	public function generate()
	{

		$this->import('BackendUser', 'User');

		foreach ($GLOBALS['BKM_MOD'] as $strGroup => $arrModules)
		{

			foreach ($arrModules as $strModule => $arrConfig)
			{

				if ($this->User->hasAccess($strModule, 'bkm_modules'))
				{

					if (is_array($arrConfig['tables']))
					{
						$GLOBALS['BE_MOD']['beekeeping']['bkm_settings']['tables'] += $arrConfig['tables'];
					}

					$this->arrModules[$GLOBALS['TL_LANG']['BKM'][$strGroup]][$strModule] = array
					(
						'name' => ($GLOBALS['TL_LANG']['BKM'][$strModule][0] ? $GLOBALS['TL_LANG']['BKM'][$strModule][0] : $strModule),
						'description' => $GLOBALS['TL_LANG']['BKM'][$strModule][1],
						'icon' => $arrConfig['icon']
					);
				}
			}
		}

		// Open module
		if (\Input::get('mod') != '')
		{

			return $this->getBKBKMModule($this->Input->get('mod'));
		}

		// Table set but module missing, fix the saveNcreate link
		elseif (\Input::get('table') != '')
		{
			foreach ($GLOBALS['BK_BKM_MOD'] as $arrGroup)
			{
				foreach( $arrGroup as $strModule => $arrConfig )
				{
					if (is_array($arrConfig['tables']) && in_array($this->Input->get('table'), $arrConfig['tables']))
					{
						$this->redirect($this->addToUrl('mod=' . $strModule));
					}
				}
			}
		}

		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{

		$this->Template->modules = $this->arrModules;
		$this->Template->script = \Contao\Environment::get('script').'/contao';
		$this->Template->welcome = sprintf($GLOBALS['TL_LANG']['BKM']['config_module']);
	}


	/**
	 * Open an beekeeper module and return it as HTML
	 * @param string
	 * @return mixed
	 */
	protected function getBKBKMModule($module)
	{
		$arrModule = array();

		foreach ($GLOBALS['BKM_MOD'] as $arrGroup)
		{
			if (!empty($arrGroup) && in_array($module, array_keys($arrGroup)))
			{
				$arrModule =& $arrGroup[$module];
			}
		}

		// Check whether the current user has access to the current module
		if (!$this->User->isAdmin && !$this->User->hasAccess($module, 'bk_modules'))
		{
			$this->log('beekeeping_bee_management module "' . $module . '" was not allowed for user "' . $this->User->username . '"', 'ModuleIBPSetup getBKBPModule()', TL_ERROR);
			$this->redirect($this->Environment->script.'?act=error');
		}

		$strTable = $this->Input->get('table');

		if ($strTable == '' && $arrModule['callback'] == '')
		{
			$this->redirect($this->addToUrl('table='.$arrModule['tables'][0]));
		}

		$id = (!$this->Input->get('act') && $this->Input->get('id')) ? $this->Input->get('id') : $this->Session->get('CURRENT_ID');

		// Add module style sheet
		if (isset($arrModule['stylesheet']))
		{
			$GLOBALS['TL_CSS'][] = $arrModule['stylesheet'];
		}

		// Add module javascript
		if (isset($arrModule['javascript']))
		{
			$GLOBALS['TL_JAVASCRIPT'][] = $arrModule['javascript'];
		}

		// Redirect if the current table does not belong to the current module
		if ($strTable != '')
		{
			if (!in_array($strTable, (array)$arrModule['tables']))
			{
				$this->log('Table "' . $strTable . '" is not allowed in module "' . $module . '"', __METHOD__, TL_ERROR);
				$this->redirect('contao/main.php?act=error');
			}

			// Load the language and DCA file
			\System::loadLanguageFile($strTable);
			$this->loadDataContainer($strTable);

			// Include all excluded fields which are allowed for the current user
			if ($GLOBALS['TL_DCA'][$strTable]['fields'])
			{
				foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $k=>$v)
				{
					if ($v['exclude'])
					{
						if ($this->User->hasAccess($strTable.'::'.$k, 'alexf'))
						{
							if ($strTable == 'tl_user_group')
							{
								$GLOBALS['TL_DCA'][$strTable]['fields'][$k]['orig_exclude'] = $GLOBALS['TL_DCA'][$strTable]['fields'][$k]['exclude'];
							}

							$GLOBALS['TL_DCA'][$strTable]['fields'][$k]['exclude'] = false;
						}
					}
				}
			}

			// Fabricate a new data container object
			if ($GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'] == '')
			{
				$this->log('Missing data container for table "' . $strTable . '"', __METHOD__, TL_ERROR);
				trigger_error('Could not create a data container object', E_USER_ERROR);
			}

			$dataContainer = 'DC_' . $GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];
			$dc = new $dataContainer($strTable, $arrModule);
		}

		// AJAX request
		if ($_POST && $this->Environment->isAjaxRequest)
		{
			$this->objAjax->executePostActions($dc);
		}

		// Call module callback
		elseif ($this->classFileExists($arrModule['callback']))
		{
			$objCallback = new $arrModule['callback']($dc);
			return $objCallback->generate();
		}

		// Custom action (if key is not defined in config.php the default action will be called)
		elseif ($this->Input->get('key') && isset($arrModule[$this->Input->get('key')]))
		{
			$objCallback = new $arrModule[$this->Input->get('key')][0]();
			return $objCallback->$arrModule[$this->Input->get('key')][1]($dc, $strTable, $arrModule);
		}

		// Default action
		elseif (is_object($dc))
		{
			$act = $this->Input->get('act');

			if (!strlen($act) || $act == 'paste' || $act == 'select')
			{
				$act = ($dc instanceof listable) ? 'showAll' : 'edit';
			}

			switch ($act)
			{
				case 'delete':
				case 'show':
				case 'showAll':
				case 'undo':
					if (!$dc instanceof listable)
					{
						$this->log('Data container ' . $strTable . ' is not listable', 'Backend getBackendModule()', TL_ERROR);
						trigger_error('The current data container is not listable', E_USER_ERROR);
					}
					break;

				case 'create':
				case 'cut':
				case 'cutAll':
				case 'copy':
				case 'copyAll':
				case 'move':
				case 'edit':
					if (!$dc instanceof editable)
					{
						$this->log('Data container ' . $strTable . ' is not editable', 'Backend getBackendModule()', TL_ERROR);
						trigger_error('The current data container is not editable', E_USER_ERROR);
					}
					break;
			}

			return $dc->$act();
		}

		return null;
	}
}


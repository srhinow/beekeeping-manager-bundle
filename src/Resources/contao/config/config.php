<?php
/**
 * @copyright  Sven Rhinow 2018
 * @author     sr-tag Sven Rhinow Webentwicklung <http://www.sr-tag.de>
 * @package    beekeeping-manager-bundle
 * @license    LGPL
 * @filesource
 */

/**
 * beekeeping-manager-bundle Version
 */
@define('BKM_VERSION', '1.0');
@define('BKM_BUILD', '6');
@define('BKM_PATH','vendor/srhinow/beekeeping-manager-bundle');
@define('BKM_PUBLIC_FOLDER','bundles/srhinowbeekeepingmanager');
@define('LOCATION_API_KEY','9e4c2e3bd44a3b'); // http://locationiq.org
@define('OPENWEATHERMAP_API_KEY','0ed9e2f4f6b89977b656038daf7c8209');

/**
 * Add back end modules
 */

if(!$GLOBALS['BE_MOD']['beekeeping']) array_insert($GLOBALS['BE_MOD'], 1, array( 'beekeeping' => array() ) );

array_insert($GLOBALS['BE_MOD']['beekeeping'], 0, array(
    'bkm_location' => array
    (
        'tables'	=> array('tl_bkm_location','tl_bkm_colonies','tl_bkm_hivemap'),
        'csvExport' => array('srhinow.beekeeeping_manager.backend.export_controller', 'csvExport'),
        'pdfExport' => array('srhinow.beekeeeping_manager.backend.export_controller', 'pdfExport'),
    ),
    'bkm_weather' => array
    (
        'callback'	=> 'Bkm\Modules\Be\ModuleBkmWeather'
    ),
    'bkm_settings' => array
		(
			'callback'	=> 'ModuleBkmSetup',
			'tables' => array(),
			'icon'   => BKM_PUBLIC_FOLDER.'/icons/beekeeping.png',
			'stylesheet' => BKM_PUBLIC_FOLDER.'/css/be.css',
		)
	)
);

$GLOBALS['TL_MODELS']['tl_bkm_bee_breed'] = \Srhinow\BkmBeeBreedModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_feed_quality'] = \Srhinow\BkmFeedQualityModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_breed_quality'] = \Srhinow\BkmBreedQualityModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_beehive'] = \Srhinow\BkmBeehiveModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_colonies'] = \Srhinow\BkmColoniesModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_frame_dimensions'] = \Srhinow\BkmFrameDimensionsModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_gentleness'] = \Srhinow\BkmGentlenessModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_hivemap'] = \Srhinow\BkmHivemapModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_location'] = \Srhinow\BkmLocationModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_population_size'] = \Srhinow\BkmPopulationSizeModel::class;
$GLOBALS['TL_MODELS']['tl_bkm_weather_forcast'] = \Srhinow\BkmWeatherForcastModel::class;

/**
 * beekeeper management Modules
 */
$GLOBALS['BKM_MOD'] = array
(
	'bkm_properties' => array
	(
        'bkm_beehive' => array
        (
            'tables' => array('tl_bkm_beehive','tl_bkm_colonies','tl_bkm_hivemap'),
            'icon'   => BKM_PUBLIC_FOLDER.'/icons/caution_board.png',
        ),
	    'bkm_bee_breed' => array
		(
			'tables'					=> array('tl_bkm_bee_breed'),
			'icon'   => BKM_PUBLIC_FOLDER.'/icons/crown_gold.png',
		),
        'bkm_feed_quality' => array
        (
            'tables'					=> array('tl_bkm_feed_quality'),
            'icon'   => BKM_PUBLIC_FOLDER.'/icons/cookies.png',
        ),
       'bkm_breed_quality' => array
        (
            'tables'					=> array('tl_bkm_breed_quality'),
            'icon'   => BKM_PUBLIC_FOLDER.'/icons/emotion_baby.png',
        ),
		'bkm_population_size' => array
		(
			'tables'					=> array('tl_bkm_population_size'),
			'icon'						=> BKM_PUBLIC_FOLDER.'/icons/layer_grid.png',
		),
		'bkm_gentleness' => array
		(
			'tables'					=> array('tl_bkm_gentleness'),
			'icon'						=> BKM_PUBLIC_FOLDER.'/icons/bow.png',
		),
		'bkm_frame_dimensions' => array
		(
			'tables'					=> array('tl_bkm_frame_dimensions'),
			'icon'   => BKM_PUBLIC_FOLDER.'/icons/picture_frame.png',
		),
	)
);

if ('BE' === TL_MODE) {
    $GLOBALS['TL_CSS'][] = BKM_PUBLIC_FOLDER.'/css/be.css|static';
}

// Enable tables in iao_setup
if ($_GET['do'] == 'bkm_settings')
{
	foreach ($GLOBALS['BKM_MOD'] as $strGroup=>$arrModules)
	{
		foreach ($arrModules as $strModule => $arrConfig)
		{
			if (is_array($arrConfig['tables']))
			{
				$GLOBALS['BE_MOD']['beekeeping']['bkm_settings']['tables'] = array_merge($GLOBALS['BE_MOD']['beekeeping']['bkm_settings']['tables'], $arrConfig['tables']);
			}
		}
	}
}

/**
 * Frontend modules
 */
// $GLOBALS['FE_MOD']['beekeeping_colonies'] = array('bk_colonies' => 'ModuleColonyTable');

/**
 * HOOKS
 */
// $GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('mpMembers', 'changeIAOTags');

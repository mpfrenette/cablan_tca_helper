<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');



t3lib_extMgm::addService($_EXTKEY,  'TCAmarkers' /* sv type */,  'tx_cablantcahelper_sv1' /* sv key */,
		array(

			'title' => 'TCA Helper service',
			'description' => 'Service which allows to substitute markers from TCA tables',

			'subtype' => '',

			'available' => TRUE,
			'priority' => 50,
			'quality' => 50,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_cablantcahelper_sv1.php',
			'className' => 'tx_cablantcahelper_sv1',
		)
	);


?>
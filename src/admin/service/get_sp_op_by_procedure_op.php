<?php

$main_op = $_POST['main_op'];
$module_search_op = $_POST['module_search_op'];

$procedure_op = array(
	2 => array(
		1 => array(),
		2 => array(
			array(
				'id' => 1,
				'name' => '2.1 Denuncias y querellas'
			)
		),
		3 => array(
			array(
				'id' => 1,
				'name' => '3.1 Denuncias y querellas'
			),
			array(
				'id' => 2,
				'name' => '3.2 Carpetas de investigación abiertas'
			),
			array(
				'id' => 3,
				'name' => '3.3 Delitos registrados en las carpetas de investigación abiertas'
			),
			array(
				'id' => 4,
				'name' => '3.4.1 Características de las víctimas'
			),
			array(
				'id' => 5,
				'name' => '3.4.2 Delitos cometidos a las víctimas'
			),
			array(
				'id' => 6,
				'name' => '3.5.1 Características de los imputados'
			),
			array(
				'id' => 7,
				'name' => '3.5.2 Delitos cometidos por los imputados'
			),
			array(
				'id' => 8,
				'name' => '3.6 Actos realizados en las carpetas de investigación en la etapa de investigación inicial'
			),
			array(
				'id' => 9,
				'name' => '3.7 Determinaciones y/o conclusiones efectuadas en las carpetas de investigación en la etapa de investigación inicial'
			),
			array(
				'id' => 10,
				'name' => '3.8 Delitos registrados en las determinaciones y/o conclusiones efectuadas en las carpetas de investigación en la etapa de investigación inicial'
			),
			array(
				'id' => 11,
				'name' => '3.9 Imputados registrados en las determinaciones y/o conclusiones efectuadas en las carpetas de investigación en la etapa de investigación inicial'
			),
			array(
				'id' => 12,
				'name' => '3.9 Complemento'
			)
		),
		4 => array(
			array(
				'id' => 2,
				'name' => '4.2 Determinaciones y/o conclusiones efectuadas en las carpetas de investigación en la etapa de investigación complementaria'
			),
			array(
				'id' => 3,
				'name' => '4.3 Delitos registrados en las determinaciones y/o conclusiones efectuadas en las carpetas de investigación en la etapa de investigación complementaria'
			)
		),
		5 => array(
			array(
				'id' => 1,
				'name' => '5.1 Procedimientos Pendientes de Concluir'
			)
		),
		6 => array(),
		9 => array(
			array(
				'id' => 1,
				'name' => '9.1 Exploración especifica de los delitos de homicidio registrados en las averiguaciones previas iniciadas en las carpetas de investigación abiertas'
			),
			array(
				'id' => 2,
				'name' => '9.2 Exploración especifica de caracteristicas de las víctimas en delitos seleccionados registradas en las averiguaciones previas iniciadas en las carpetas de investigación abiertas'
			),
			array(
				'id' => 3,
				'name' => '9.3 Exploración especifica de caracteristicas de los inculpados en los delitos seleccionados registradas en las averiguaciones previas iniciadas en las carpetas de investigación abiertas'
			)
		)
	),
	3 => array(
		1 => array(),
		2 => array(
			array(
				'id' => 1,
				'name' => '2.1 Denuncias y querellas'
			),
			array(
				'id' => 2,
				'name' => '2.2 Carpetas de investigación abiertas'
			),
			array(
				'id' => 3,
				'name' => '2.3 Delitos registrados en las carpetas de investigación abiertas'
			),
			array(
				'id' => 4,
				'name' => '2.4 Características de las víctimas'
			),
			array(
				'id' => 5,
				'name' => '2.4.2 Delitos cometidos a las víctimas'
			),
			array(
				'id' => 6,
				'name' => '2.5.1 Características de los adolescentes imputados'
			),
			array(
				'id' => 7,
				'name' => '2.5.2 Delitos cometidos por los adolescentes imputados'
			),
			array(
				'id' => 8,
				'name' => '2.6 Actos realizados en las carpetas de investigación en la etapa de investigación inicial'
			),
			array(
				'id' => 9,
				'name' => '2.7 Determinaciones y/o conclusiones efectuadas en las carpetas de investigación en la etapa de investigación inicial'
			),
			array(
				'id' => 10,
				'name' => '2.8 Delitos registrados en las determinaciones y/o conclusiones efectuadas en las carpetas de investigación en la etapa de investigación inicial'
			),
			array(
				'id' => 11,
				'name' => '2.9 Adolescentes imputados registrados en las determinaciones y/o conclusiones efectuadas en las carpetas de investigación en la etapa de investigación inicial'
			)
		),
		3 => array(
			array(
				'id' => 2,
				'name' => '3.2 Determinaciones y/o conclusiones efectuadas en las carpatas de investigación en la etapa de investigación complementaria'
			),
			array(
				'id' => 3,
				'name' => '3.3 Delitos registrados en las determinaciones y/o conclusiones efectuadas en las carpatas de investigación en la etapa de investigación complementaria'
			),
			array(
				'id' => 4,
				'name' => '3.4 Adolescentes imputados registrados en las determinaciones y/o conclusiones efectuadas en las carpatas de investigación en la etapa de investigación complementaria'
			)
		),
		4 => array(
			array(
				'id' => 1,
				'name' => '4.1 Procedimientos pendientes de concluir'
			),
			array(
				'id' => 2,
				'name' => '4.2 Delitos relacionados con los procedimientos procedimientos pendientes de concluir'
			),
			array(
				'id' => 3,
				'name' => '4.3 Adolescentes imputados relacionados con los procedimientos pendientes de concluir'
			)
		),
		5 => array(
			array(
				'id' => 1,
				'name' => '2.9 Complemento'
			)
		),
		6 => array(),
		8 => array(
			array(
				'id' => 1,
				'name' => '8.1 Exploración específica de los delitos de homicidio registrados en las averiguaciones previas e investigaciones iniciadas en las carpetas de investigación abiertas'
			),
			array(
				'id' => 2,
				'name' => '8.2 Exploración específica de caracteristicas de las víctimas en los delitos seleccionados registrados en las averiguaciones previas e investigaciones iniciadas en las carpetas de investigación abiertas'
			),
			array(
				'id' => 3,
				'name' => '8.3 Exploración específica de caracteristicas de los adolescentes imputados en delitos seleccionados registrados en las averiguaciones previas e investigaciones iniciadas en las carpetas de investigación abiertas'
			)
		),
	)
);

echo json_encode(
	$procedure_op[$module_search_op][$main_op],
	JSON_FORCE_OBJECT
);

?>


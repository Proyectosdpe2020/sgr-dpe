<?php

$search_op = $_POST['search_op'];
$subsec_search_op = $_POST['subsec_search_op'];
$module_search_op = $_POST['module_search_op'];

$procedure_op = array(
	2 => array(
		1 => array(),
		2 => array(
			1 => array(
				'2-1-TotaldeComparecencias',
			)
		),
		3 => array(
			1 => array(
				'3-1-TotalDeCarpetas',
				'3-2-TotalDeCarpetasporFormadeInicio',
				'3-3-TotaldeDenunciasporCompetencia'
			),
			2 => array(
				'3-4-TotaldeCarpetasAbiertas',
				'3-5-TotaldeCarpetasporUnidad',
				'3-6-TotaldeCarpetasporCondiciondelImputado'
			),
			3 => array(
				'3-8-DelitosPorConsumacion',
				'3-9-DelitosPorTipodeDelito',
				'3-10-DelitosporCaracteristicas'
			),
			4 => array(
				'3-11-VictimasPorTipo',
				'3-12-VictimasPorSexoYEdad',
				'3-12a-VictimasPorEscolaridad',
				'3-13-VictimasPorNacionadidadYSexo',
				'3-14-VictimasPorProfesionYSexo',
				'3-14a-VictimasPorCondicionAnalfabetismo',
				'3-15-VictimasPorFamiliaLinguisticaYSexo',
				'3-16-VictimasPorPuebloIndigenaYSexo',
				'3-17-VictimasPorDiscapacidadYSexo',
				'3-18-VictimasPorParentescoYSexo',
				'3-19-VictimasPorTipoDePersonaYSexo'
			),
			5 => array(
				'3-20-DelitosAVictimasPorTipoDePersona',
				'3-21-DelitosAVictimasPorTipoDePersonYSexo',
				'3-22a-DelitosAVictimasHombresEdad',
				'3-22b-DelitosAVictimasMujeresPorEdad'
			),
			6 => array(
				'3-23-ImputadosPorSexo',
				'3-24-ImputadosPorEstatusJuridico',
				'3-26-ImputadosPorSexoYEdad',
				'3-27-ImputadosPorEscolaridadYSexo',
				'3-28-ImputadosPorNacionalidadYSexo',
				'3-29-ImputadosPorProfesionYSexo',
				'3-30-ImputadosPorFamiliaLinguisticaYSexo',
				'3-31-ImputadosPorPuebloIndigenaYSexo',
				'3-32-ImputadosPorDiscapacidadYSexo',
				'3-33-ImputadosPorEstadoPsicofisicoYSexo',
				'3-34-ImputadosPorCantidadDeDelitosYSexo'
			),
			7 => array(
				'3-35-ImputadosPorTotalDeDelitosYSexo',
				'3-36-ImputadosPorDelitoYSexo'
			),
			8 => array(
				/*'3-37-',
				'3-38-'*/
			),
			9 => array(
				'3-42-DeterminacionesEnEtapadeInvestigacion'/*,
				'3-39-DeterminacionesEnEtapadeInvestigacion'
				'3-40-'*/
			),
			10 => array(
				'3-44-DeterminacionesPorConsumacion',
				'3-45-DeterminacionesDesglosadasPorInciso',
				'3-46-DeterminacionesDesglosadasPorCaracteristicasDeEjecucion',
				'3-47-DeterminacionesDesglosadasPorTipo'
			),
			11 => array(
				'3-48-ImputadosEnDeterminacionesPorSexo',
				'3-49-ImputadosEnDeterminacionesPorTipo'
			),
			12 => array(
				'3-9-Complemento'
			)
		),
		4 => array(
			2 => array(
				'4-6-DeterminacionaesEtapaComplementaria',
				'4-7-DeterminacionaesEtapaComplementariaPorConsumacion'
			),
			3 => array(
				'4-8-DeterminacionaesEtapaComplementariaPorInciso',
				'4-9-DeterminacionaesEtapaComplementariaPorCaracteristicas',
				'4-10-DeterminacionaesEtapaComplementariaPorIncisoDeterminacion',
				'4-11-ImputadosenPorSexoDeterminacionaesEtapaComplementaria',
				'4-12-ImputadosenPorDeterminacionaesEtapaComplementaria'
			)
		),
		5 => array(
			1 => array(
				'5-1-ProcedimientosPendientesdeConcluir',
				'5-2-ProcedimientosPendientesdeConcluirPorConsumacion',
				'5-3-ProcedimientosPendientesdeConcluirDesglosadasPorInciso',
				'5-4-ImputadosEnProcedimientosPendientesdeConcluir'
			)
		),
		6 => array(),
		9 => array(
			1 => array(
				'9-1-VictimasDelitosEspeciales'
			),
			2 => array(
				'9-2-VictimasDelitosEspeciales',
				'9-3-VictimasPorEdadDelitosEspeciales',
				'9-4-VictimasPorNacionalidadDelitosEspeciales',
				'9-5-VictimasPorTipoRelacionDelitosEspeciales',
				'9-6-VictimasPorTipoDeArmanDelitosEspeciales',
				'9-7-VictimasPorMunicipioDelitosEspeciales',
				'9-8-VictimasPorActividadDelitosEspeciales'
			),
			3 => array(
				'9-9-ImputadosPorSexoDelitosEspeciales',
				'9-10-ImputadosPorEdadDelitosEspeciales',
				'9-11-ImputadosPorNacionalidadDelitosEspeciales'
			)
		)
	),
	3 => array(
		1 => array(),
		2 => array(
			1 => array(
				'2-1-TotaldeDenuncias',
				'2-2-TotalDeDenunciasPorEscrito',
				'2-3-TotalDeDenunciasFueroComun'
			),
			2 => array(
				'2-4-TotalDeCarpetasAbiertas',
				'2-5-TotaldeCarpetasPorUnidad',
				'2-6-CarpetasPorCondiciondelAdolescente'
			),
			3 => array(
				'2-8-CarpetasPorConsumacion',
				'2-9-DelitosPorTipo',
				'2-10-DelitosPorCaracteristicas'
			),
			4 => array(
				'2-11-VictimasPorTipo',
				'2-12-VictimasPorSexoYEdad',
				'2-12a-VictimasPorEscolaridad',
				'2-13-VictimasPorNacionadidadYSexo',
				'2-14-VictimasPorProfesionYSexo',
				'2-14a-VictimasPorCondicionAnalfabetismo',
				'2-15-VictimasPorFamiliaLinguisticaYSexo',
				'2-16-VictimasPorPuebloIndigenaYSexo',
				'2-17-VictimasPorDiscapacidadYSexo',
				'2-18-VictimasPorParentescoYSexo',
				'2-19-VictimasPorTipoDePersonaYSexo'
			),
			5 => array(
				'2-20-DelitosAVictimasPorTipoDePersona',
				'2-21-DelitosAVictimasPorTipoDePersonaYSexoPorInciso',
				'2-22a-DelitosAVictimasHombresEdad',
				'2-22b-DelitosAVictimasMujeresPorEdad'
			),
			6 => array(
				'2-23-ImputadosRegistradosPorSexo',
				'2-24-ImputadosPorEstatusJuridico',
				'2-26-ImputadosPorEdadYSexo',
				'2-27-ImputadosPorEscolaridadYSexo',
				'2-28-ImputadosPorNacionalidadYSexo',
				'2-29-ImputadosPorOcupacionYSexo',
				'2-30-ImputadosPorFamiliaLinguisticaYSexo',
				'2-31-ImputadosPorPuebloIndigenaYSexo',
				'2-32-ImputadosPorDiscapacidadYSexo',
				'2-33-ImputadosPorEstadoPsicofisicoYSexo',
				'2-34-ImputadosPorCantidadDeDelitosYSexo'
			),
			7 => array(
				'2-35-ImputadosPorTotalDeDelitosYSexo',
				'2-36-ImputadosPorDelitoYSexo'
			),
			8 => array(
				/*'2-37-',
				'2-38-'*/
			),
			9 => array(
				'2-42-DeterminacionesEnEtapadeInvestigacion'/*,
				'2-40-'*/
			),
			10 => array(
				'2-44-DeterminacionesPorConsumacion',
				'2-45-DeterminacionesDesglosadasPorInciso',
				'2-46-DeterminacionesDesglosadasPorCaracteristicasDeEjecucion',
				'2-47-DeterminacionesDesglosadasPorTipo'
			),
			11 => array(
				'2-48-ImputadosEnDeterminacionesPorSexo',
				'2-49-ImputadosEnDeterminacionesPorTipo'
			)
		),
		3 => array(
			2 => array(
				'3-6-DeterminacionaesEtapaComplementaria'
			),
			3 => array(
				'3-7-DeterminacionaesEtapaComplementariaPorConsumacion',
				'3-8-DeterminacionaesEtapaComplementariaPorInciso',
				'3-9-DeterminacionaesEtapaComplementariaPorCaracteristicas',
				'3-10-DeterminacionaesEtapaComplementariaPorIncisoDeterminacion'
			),
			4 => array(
				'3-11-ImputadosenPorSexoDeterminacionaesEtapaComplementaria',
				'3-12-ImputadosenPorDeterminacionaesEtapaComplementaria'
			)
		),
		4 => array(
			1 => array(
				'4-1-ProcedimientosPendientesdeConcluir'
			),
			2 => array(
				'4-2-ProcedimientosPendientesdeConcluirPorConsumacion',
				'4-3-ProcedimientosPendientesdeConcluirDesglosadasPorInciso'
			),
			3 => array(
				'4-4-ImputadosEnProcedimientosPendientesdeConcluir'
			)
		),
		5 => array(
			1 => array(
				'2-9-Complemento'
			)
		),
		6 => array(),
		8 => array(
			1 => array(
				'8-1-VictimasDelitosEspeciales'
			),
			2 => array(
				'8-2-VictimasDelitosEspeciales',
				'8-3-VictimasPorEdadDelitosEspeciales',
				'8-4-VictimasPorNacionalidadDelitosEspeciales',
				'8-5-VictimasPorTipoRelacionDelitosEspeciales',
				'8-6-VictimasPorTipoDeArmanDelitosEspeciales',
				'8-7-VictimasPorMunicipioDelitosEspeciales',
				'8-8-VictimasPorActividadDelitosEspeciales'
			),
			3 => array(
				'8-9-ImputadosPorSexoDelitosEspeciales',
				'8-10-ImputadosPorEdadDelitosEspeciales',
				'8-11-ImputadosPorNacionalidadDelitosEspeciales'
			)
		)
	)
);

echo json_encode($procedure_op[$module_search_op][$search_op][$subsec_search_op]);

?>


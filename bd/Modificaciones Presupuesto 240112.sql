ALTER TABLE  `bancos_cuentas_movimientos` DROP  `num_cheque` ;

ALTER TABLE  `bancos_cuentas_movimientos` ADD  `ano` YEAR NOT NULL AFTER  `id` ,
ADD  `tipo` VARCHAR( 2 ) NOT NULL AFTER  `ano`;

ALTER TABLE  `bancos_cuentas_movimientos` ADD  `fecha` DATE NOT NULL AFTER  `descripcion`;

--
-- Estructura de tabla para la tabla `tipo_movimiento`
--

CREATE TABLE IF NOT EXISTS `tipo_movimiento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod_organizacion` varchar(6) NOT NULL,
  `siglas` varchar(2) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Volcar la base de datos para la tabla `tipo_movimiento`
--

INSERT INTO `tipo_movimiento` (`id`, `cod_organizacion`, `siglas`, `nombre`) VALUES
(1, '000001', 'I', 'INGRESO'),
(2, '000001', 'E', 'EGRESO'),
(3, '000001', 'T', 'TRANSFERENCIA'),
(4, '000001', 'ND', 'NOTA DEBITO'),
(5, '000001', 'NC', 'NOTA CREDITO'),
(7, '000001', 'D', 'DEPOSITO'),
(8, '000001', 'CH', 'CHEQUE'),
(9, '000001', 'R', 'REVERSO');

-- --------------------------------------------------------

-- Volcar la base de datos para la tabla `fuentes_financiamiento`
--

INSERT INTO `fuentes_financiamiento` ( `ano`, `cod_fuente_financiamiento`, `descripcion`, `cod_organizacion`, `cod_presupuesto_ingreso`) VALUES
('2012', '01', 'Bancaribe Cuenta Principal', '000001', '01'),
('2012', '02', 'Bancaribe Cuenta Auxiliar', '000001', '02');

-- Volcar la base de datos para la tabla `fuentes_financiamiento_cuentas`
--

INSERT INTO `fuentes_financiamiento_cuentas` ( `ano`, `cod_organizacion`, `cod_fuente_financiamiento`, `numero_cuenta`) VALUES
('2012', '000001', '01', '01140530455300000209'),
('2012', '000001', '02', '01140530425300023497');

-- Volcar la base de datos para la tabla `presupuesto_ingresos`
--

INSERT INTO `presupuesto_ingresos` (`cod_organizacion`, `cod_presupuesto_ingreso`, `ano`, `ramo`, `generica`, `especifica`, `subespecifica`, `descripcion`, `monto`) VALUES
('000001', '01', '2012', '101', '01', '01', '02', 'Bancaribe Cuenta Principal', '5000.00'),
('000001', '02', '2012', '101', '01', '01', '01', 'Bancaribe Cuenta auxiliar', '10000.00');

-- --------------------------------------------------------
TRUNCATE TABLE  `bancos_cuentas_movimientos`;

UPDATE  `presupuesto`.`bancos_cuentas` SET  `saldo` =  '0' WHERE  `bancos_cuentas`.`id` =1;

UPDATE  `presupuesto`.`bancos_cuentas` SET  `saldo` =  '0' WHERE  `bancos_cuentas`.`id` =2;

UPDATE  `presupuesto`.`estatus_presupuesto` SET  `estatus` =  'PA' WHERE  `estatus_presupuesto`.`id` =6;

UPDATE  `presupuesto`.`estatus_presupuesto` SET  `estatus` =  'PR' WHERE  `estatus_presupuesto`.`id` =7;


UPDATE  `presupuesto`.`presupuesto_ingresos` SET  
`monto` =  '0' WHERE  `presupuesto_ingresos`.`ano` ='2012';

UPDATE  `presupuesto`.`presupuesto_ingresos` SET  
`monto` =  '0' WHERE  `presupuesto_ingresos`.`ano` ='2012';

INSERT INTO  `presupuesto`.`unidad_ejecutora` (
`id` ,
`ano` ,
`cod_organizacion` ,
`sector` ,
`programa` ,
`subprograma` ,
`proyecto` ,
`actividad`
)
VALUES (
NULL ,  '2012',  '000001',  '01',  '02',  '00',  '00',  '51'
);

TRUNCATE TABLE  `temporal_traslados`;
TRUNCATE TABLE  `temporal_articulos_compromisos`;
TRUNCATE TABLE  `temporal_causado`;
TRUNCATE TABLE  `temporal_causado_pagado`;
TRUNCATE TABLE  `temporal_compromisos`;




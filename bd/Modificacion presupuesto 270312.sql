CREATE TABLE IF NOT EXISTS `temporal_retencion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `nombre` varchar(250) DEFAULT NULL,
  `sector` varchar(2) NOT NULL DEFAULT '00',
  `programa` varchar(2) NOT NULL DEFAULT '00',
  `subprograma` varchar(2) NOT NULL DEFAULT '00',
  `proyecto` varchar(2) NOT NULL DEFAULT '00',
  `actividad` varchar(2) NOT NULL DEFAULT '00',
  `partida` varchar(3) NOT NULL DEFAULT '000',
  `generica` varchar(2) NOT NULL DEFAULT '00',
  `especifica` varchar(2) NOT NULL DEFAULT '00',
  `subespecifica` varchar(2) NOT NULL DEFAULT '00',
  `ordinal` varchar(5) NOT NULL DEFAULT '00000',
  `monto` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE  `retencion_pagado` ADD  `monto` DECIMAL( 12, 2 ) NOT NULL AFTER  `codigo_retencion`;





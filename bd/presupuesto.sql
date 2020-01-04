-- phpMyAdmin SQL Dump
-- version 3.1.4
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 12-06-2009 a las 11:39:08
-- Versión del servidor: 5.0.75
-- Versión de PHP: 5.2.6-3ubuntu4.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `presupuesto`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos`
--

CREATE TABLE IF NOT EXISTS `bancos` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `cod_banco` varchar(3) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `info_adicional` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Volcar la base de datos para la tabla `bancos`
--

INSERT INTO `bancos` (`id`, `cod_organizacion`, `cod_banco`, `nombre`, `info_adicional`) VALUES
(1, '000001', '001', 'Bancaribe', 'El que queda en la AsunciÃ³n'),
(2, '000001', '002', 'Banco de Venezuela', ''),
(3, '000001', '003', 'Banesco', ''),
(4, '000001', '004', 'Banco Confederado', ''),
(5, '000001', '005', 'Banco Provincial', ''),
(6, '000001', '006', 'Banfoandes', ''),
(7, '000001', '007', 'Banco del Tesoro', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos_cuentas`
--

CREATE TABLE IF NOT EXISTS `bancos_cuentas` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `cod_banco` varchar(3) NOT NULL,
  `numero_cuenta` varchar(20) NOT NULL,
  `fecha_apertura` date NOT NULL,
  `tipo_cuenta` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Volcar la base de datos para la tabla `bancos_cuentas`
--

INSERT INTO `bancos_cuentas` (`id`, `cod_organizacion`, `cod_banco`, `numero_cuenta`, `fecha_apertura`, `tipo_cuenta`) VALUES
(1, '000001', '001', '11111111111111111111', '0000-00-00', 'Ahorro'),
(2, '000001', '001', '23412341243124312431', '2009-03-04', 'Corriente'),
(3, '000001', '002', '23452345234523452354', '2009-03-12', 'Ahorro'),
(4, '000001', '005', '33333333333333333333', '2009-03-12', 'FAL'),
(5, '000001', '002', '31111111111111111111', '2009-03-12', 'Ahorro'),
(6, '000001', '003', '66666666666666666666', '2009-03-13', 'Corriente'),
(7, '000001', '001', '42255588____________', '2009-03-14', 'Ahorro'),
(8, '000001', '001', '66565165165165165165', '2009-03-06', 'Ahorro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `causado_pagado`
--

CREATE TABLE IF NOT EXISTS `causado_pagado` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `tipo_documento_causado` varchar(2) NOT NULL,
  `numero_documento_causado` varchar(6) NOT NULL,
  `tipo_documento_pagado` varchar(2) NOT NULL,
  `numero_documento_pagado` varchar(15) NOT NULL COMMENT 'numero del cheque',
  `numero_cuenta_banco` varchar(20) NOT NULL,
  `rif_cedula_beneficiario` varchar(15) NOT NULL,
  `nombre_beneficiario` varchar(150) NOT NULL,
  `fecha_documento_pagado` date NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `monto` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `causado_pagado`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compromiso_causado`
--

CREATE TABLE IF NOT EXISTS `compromiso_causado` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `tipo_documento_causado` varchar(2) NOT NULL,
  `numero_documento_causado` varchar(6) NOT NULL,
  `tipo_documento_compromiso` varchar(2) NOT NULL,
  `numero_documento_compromiso` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `monto` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Volcar la base de datos para la tabla `compromiso_causado`
--

INSERT INTO `compromiso_causado` (`id`, `ano`, `cod_organizacion`, `tipo_documento_causado`, `numero_documento_causado`, `tipo_documento_compromiso`, `numero_documento_compromiso`, `sector`, `programa`, `subprograma`, `proyecto`, `actividad`, `partida`, `generica`, `especifica`, `subespecifica`, `ordinal`, `cod_fuente_financiamiento`, `monto`) VALUES
(1, '2009', '000001', 'OP', '000001', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '01', '00', '00000', '00', 7.00),
(2, '2009', '000001', 'OP', '000001', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '02', '00', '00000', '00', 12.00),
(3, '2009', '000001', 'OP', '000001', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '03', '00', '00000', '00', 25.75);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_causado`
--

CREATE TABLE IF NOT EXISTS `detalle_causado` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `tipo_documento` varchar(2) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `monto_parcial` decimal(12,2) NOT NULL default '0.00',
  `monto_pendiente` decimal(12,2) NOT NULL default '0.00',
  `monto_reversos` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `detalle_causado`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compromisos`
--

CREATE TABLE IF NOT EXISTS `detalle_compromisos` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `cod_proveedor` varchar(6) NOT NULL,
  `tipo_documento` varchar(2) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `monto_parcial` decimal(12,2) NOT NULL default '0.00',
  `monto_pendiente` decimal(12,2) NOT NULL default '0.00',
  `monto_reversos` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Volcar la base de datos para la tabla `detalle_compromisos`
--

INSERT INTO `detalle_compromisos` (`id`, `ano`, `cod_organizacion`, `cod_proveedor`, `tipo_documento`, `numero`, `sector`, `programa`, `subprograma`, `proyecto`, `actividad`, `partida`, `generica`, `especifica`, `subespecifica`, `ordinal`, `cod_fuente_financiamiento`, `monto_parcial`, `monto_pendiente`, `monto_reversos`) VALUES
(1, '2009', '000001', '000001', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '01', '00', '00000', '00', 10.00, 3.00, 0.00),
(2, '2009', '000001', '000001', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '02', '00', '00000', '00', 20.00, 8.00, 0.00),
(3, '2009', '000001', '000001', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '03', '00', '00000', '00', 30.00, 4.25, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_creditos`
--

CREATE TABLE IF NOT EXISTS `detalle_creditos` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL default '00',
  `monto_aumento` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `detalle_creditos`
--

INSERT INTO `detalle_creditos` (`id`, `ano`, `cod_organizacion`, `numero`, `sector`, `programa`, `subprograma`, `proyecto`, `actividad`, `partida`, `generica`, `especifica`, `subespecifica`, `ordinal`, `cod_fuente_financiamiento`, `monto_aumento`) VALUES
(1, '2009', '000001', '000001', '01', '02', '00', '00', '51', '401', '01', '01', '00', '00000', '00', 50.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pagado`
--

CREATE TABLE IF NOT EXISTS `detalle_pagado` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `detalle_pagado`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_recortes`
--

CREATE TABLE IF NOT EXISTS `detalle_recortes` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL default '00',
  `monto_disminucion` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `detalle_recortes`
--

INSERT INTO `detalle_recortes` (`id`, `ano`, `cod_organizacion`, `numero`, `sector`, `programa`, `subprograma`, `proyecto`, `actividad`, `partida`, `generica`, `especifica`, `subespecifica`, `ordinal`, `cod_fuente_financiamiento`, `monto_disminucion`) VALUES
(1, '2009', '000001', '000001', '01', '02', '00', '00', '51', '402', '01', '01', '00', '00000', '00', 25.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_traslados`
--

CREATE TABLE IF NOT EXISTS `detalle_traslados` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL default '00',
  `monto_disminucion` decimal(12,2) NOT NULL default '0.00',
  `monto_aumento` decimal(12,2) NOT NULL default '0.00',
  `problema` tinyint(1) NOT NULL default '0' COMMENT '0=Sin Problema, 1=Cod. No Existe, 2=Disponibilidad Insuficiente',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Volcar la base de datos para la tabla `detalle_traslados`
--

INSERT INTO `detalle_traslados` (`id`, `ano`, `cod_organizacion`, `numero`, `sector`, `programa`, `subprograma`, `proyecto`, `actividad`, `partida`, `generica`, `especifica`, `subespecifica`, `ordinal`, `cod_fuente_financiamiento`, `monto_disminucion`, `monto_aumento`, `problema`) VALUES
(1, '2009', '000001', '000001', '01', '02', '00', '00', '51', '402', '01', '01', '00', '00000', '00', 5.00, 0.00, 0),
(2, '2009', '000001', '000001', '01', '02', '00', '00', '51', '401', '01', '01', '00', '00000', '00', 0.00, 5.00, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estatus_documento`
--

CREATE TABLE IF NOT EXISTS `estatus_documento` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `tipo_documento` varchar(2) NOT NULL,
  `numero_documento` varchar(15) NOT NULL,
  `estatus` varchar(15) NOT NULL,
  `observaciones` text NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `estatus_documento`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estatus_presupuesto`
--

CREATE TABLE IF NOT EXISTS `estatus_presupuesto` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `estatus` varchar(2) NOT NULL default 'FU' COMMENT 'FU=Futuro, PR=Presente, PA=Pasado',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Volcar la base de datos para la tabla `estatus_presupuesto`
--

INSERT INTO `estatus_presupuesto` (`id`, `cod_organizacion`, `ano`, `estatus`) VALUES
(2, '000001', '2008', 'PA'),
(4, '000001', '2009', 'PR'),
(5, '000001', '2010', 'FU'),
(6, '000001', '2011', 'FU'),
(7, '000001', '2012', 'FU');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fuentes_financiamiento`
--

CREATE TABLE IF NOT EXISTS `fuentes_financiamiento` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `cod_presupuesto_ingreso` varchar(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Volcar la base de datos para la tabla `fuentes_financiamiento`
--

INSERT INTO `fuentes_financiamiento` (`id`, `ano`, `cod_fuente_financiamiento`, `descripcion`, `cod_organizacion`, `cod_presupuesto_ingreso`) VALUES
(1, '2009', '01', '', '000001', '04'),
(2, '2009', '02', '', '000001', '02'),
(3, '2009', '03', 'SITUADO', '000001', '05'),
(4, '2009', '04', 'asdasd', '000001', '03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fuentes_financiamiento_cuentas`
--

CREATE TABLE IF NOT EXISTS `fuentes_financiamiento_cuentas` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `numero_cuenta` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Volcar la base de datos para la tabla `fuentes_financiamiento_cuentas`
--

INSERT INTO `fuentes_financiamiento_cuentas` (`id`, `ano`, `cod_organizacion`, `cod_fuente_financiamiento`, `numero_cuenta`) VALUES
(1, '2009', '000001', '01', 'Bancaribe - Ahorro /'),
(2, '2009', '000001', '02', '66666666666666666666'),
(3, '2009', '000001', '03', '66565165165165165165'),
(4, '2009', '000001', '04', '31111111111111111111');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestro_causado`
--

CREATE TABLE IF NOT EXISTS `maestro_causado` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_documento` varchar(2) NOT NULL COMMENT 'las siglas de la tabla tipo_documento',
  `numero` varchar(6) NOT NULL,
  `cod_proveedor` varchar(6) NOT NULL,
  `rif_cedula_beneficiario` varchar(15) default NULL,
  `nombre_beneficiario` varchar(150) NOT NULL,
  `motivo` text NOT NULL,
  `monto_total` decimal(12,2) NOT NULL default '0.00',
  `monto_pendiente` decimal(12,2) NOT NULL default '0.00',
  `monto_reversos` decimal(12,2) NOT NULL default '0.00',
  `monto_retenciones` decimal(12,2) NOT NULL default '0.00',
  `estatus_actual` set('ANULADA','NORMAL') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `maestro_causado`
--

INSERT INTO `maestro_causado` (`id`, `cod_organizacion`, `ano`, `fecha`, `tipo_documento`, `numero`, `cod_proveedor`, `rif_cedula_beneficiario`, `nombre_beneficiario`, `motivo`, `monto_total`, `monto_pendiente`, `monto_reversos`, `monto_retenciones`, `estatus_actual`) VALUES
(1, '000001', '2009', '2009-05-20', 'OP', '000001', '000001', NULL, '', '', 44.75, 44.75, 0.00, 0.00, 'NORMAL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestro_compromisos`
--

CREATE TABLE IF NOT EXISTS `maestro_compromisos` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_documento` varchar(2) NOT NULL COMMENT 'las siglas de la tabla tipo_documento',
  `numero` varchar(6) NOT NULL,
  `cod_proveedor` varchar(6) NOT NULL,
  `motivo` text NOT NULL,
  `monto_total` decimal(12,2) NOT NULL default '0.00',
  `monto_pendiente` decimal(12,2) NOT NULL default '0.00',
  `monto_reversos` decimal(12,2) NOT NULL default '0.00',
  `estatus_actual` set('ANULADA','NORMAL') NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `numero_index` (`cod_organizacion`,`ano`,`tipo_documento`,`numero`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `maestro_compromisos`
--

INSERT INTO `maestro_compromisos` (`id`, `cod_organizacion`, `ano`, `fecha`, `tipo_documento`, `numero`, `cod_proveedor`, `motivo`, `monto_total`, `monto_pendiente`, `monto_reversos`, `estatus_actual`) VALUES
(1, '000001', '2009', '2009-05-20', 'OS', '000001', '000001', 'ssss', 60.00, 15.25, 0.00, 'NORMAL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestro_creditos`
--

CREATE TABLE IF NOT EXISTS `maestro_creditos` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `num_documento` varchar(15) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` text NOT NULL,
  `monto_total` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `maestro_creditos`
--

INSERT INTO `maestro_creditos` (`id`, `cod_organizacion`, `ano`, `numero`, `num_documento`, `fecha`, `motivo`, `monto_total`) VALUES
(1, '000001', '2009', '000001', 'CA-0001-2009', '2009-05-19', 'Aumento para pago de nÃ³mina al personal de CENE', 50.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestro_pagado`
--

CREATE TABLE IF NOT EXISTS `maestro_pagado` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `maestro_pagado`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestro_recortes`
--

CREATE TABLE IF NOT EXISTS `maestro_recortes` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `num_documento` varchar(15) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` text NOT NULL,
  `monto_total` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `maestro_recortes`
--

INSERT INTO `maestro_recortes` (`id`, `cod_organizacion`, `ano`, `numero`, `num_documento`, `fecha`, `motivo`, `monto_total`) VALUES
(1, '000001', '2009', '000001', 'RE-0001-2009', '2009-05-19', 'Recorte presupuestario por dÃ©ficit Petrolero', 25.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestro_traslados`
--

CREATE TABLE IF NOT EXISTS `maestro_traslados` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `num_documento` varchar(15) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` text NOT NULL,
  `monto_total` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcar la base de datos para la tabla `maestro_traslados`
--

INSERT INTO `maestro_traslados` (`id`, `cod_organizacion`, `ano`, `numero`, `num_documento`, `fecha`, `motivo`, `monto_total`) VALUES
(1, '000001', '2009', '000001', 'TR-0001-2009', '2009-05-19', 'Traspaso presupuestario para pago adicional de bonos a los funcionarios de CENE', 5.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_gastos`
--

CREATE TABLE IF NOT EXISTS `presupuesto_gastos` (
  `id` int(11) NOT NULL auto_increment,
  `cod_estado` varchar(2) default NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `ano` varchar(4) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `descripcion` varchar(250) default NULL,
  `asignado` decimal(12,2) NOT NULL default '0.00',
  `aumentos` decimal(12,2) NOT NULL default '0.00',
  `disminuciones` decimal(12,2) NOT NULL default '0.00',
  `comprometido` decimal(12,2) NOT NULL default '0.00',
  `causado` decimal(12,2) NOT NULL default '0.00',
  `pagado` decimal(12,2) NOT NULL default '0.00',
  `disponible` decimal(12,2) NOT NULL default '0.00',
  `retencion` decimal(12,2) NOT NULL default '0.00',
  `acumulado` tinyint(1) NOT NULL default '0' COMMENT '0=NO, 1=SI.  Si es acumulado, entonces no se puede imputar de el.',
  `es_retencion` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `codigo_index` (`cod_organizacion`,`ano`,`sector`,`programa`,`subprograma`,`proyecto`,`actividad`,`partida`,`generica`,`especifica`,`subespecifica`,`ordinal`,`cod_fuente_financiamiento`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=143 ;

--
-- Volcar la base de datos para la tabla `presupuesto_gastos`
--

INSERT INTO `presupuesto_gastos` (`id`, `cod_estado`, `cod_organizacion`, `ano`, `sector`, `programa`, `subprograma`, `proyecto`, `actividad`, `partida`, `generica`, `especifica`, `subespecifica`, `ordinal`, `cod_fuente_financiamiento`, `descripcion`, `asignado`, `aumentos`, `disminuciones`, `comprometido`, `causado`, `pagado`, `disponible`, `retencion`, `acumulado`, `es_retencion`) VALUES
(118, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '01', '01', '00', '00000', '', '50', 50.00, 55.00, 0.00, 0.00, 0.00, 0.00, 105.00, 0.00, 0, 0),
(119, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '01', '00', '00', '00000', '', '', 200.00, 55.00, 0.00, 0.00, 0.00, 0.00, 255.00, 0.00, 1, 0),
(120, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '00', '00', '00', '00000', '', '', 350.00, 55.00, 0.00, 15.25, 44.75, 0.00, 345.00, 0.00, 1, 0),
(121, NULL, '000001', '2009', '01', '02', '00', '00', '51', '000', '00', '00', '00', '00000', '', '', 400.00, 55.00, 30.00, 15.25, 44.75, 0.00, 365.00, 0.00, 1, 0),
(122, NULL, '000001', '2009', '01', '02', '00', '00', '00', '000', '00', '00', '00', '00000', '', '', 400.00, 55.00, 30.00, 15.25, 44.75, 0.00, 365.00, 0.00, 1, 0),
(123, NULL, '000001', '2009', '01', '00', '00', '00', '00', '000', '00', '00', '00', '00000', '', '', 400.00, 55.00, 30.00, 15.25, 44.75, 0.00, 365.00, 0.00, 1, 0),
(124, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '01', '02', '00', '00000', '', '50', 50.00, 0.00, 0.00, 0.00, 0.00, 0.00, 50.00, 0.00, 0, 0),
(125, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '01', '03', '00', '00000', '', '50', 50.00, 0.00, 0.00, 0.00, 0.00, 0.00, 50.00, 0.00, 0, 0),
(126, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '02', '01', '00', '00000', '', '50', 50.00, 0.00, 0.00, 3.00, 7.00, 0.00, 40.00, 0.00, 0, 0),
(127, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '02', '00', '00', '00000', '', '', 150.00, 0.00, 0.00, 15.25, 44.75, 0.00, 90.00, 0.00, 1, 0),
(128, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '02', '02', '00', '00000', '', '50', 50.00, 0.00, 0.00, 8.00, 12.00, 0.00, 30.00, 0.00, 0, 0),
(129, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '02', '03', '00', '00000', '', '50', 50.00, 0.00, 0.00, 4.25, 25.75, 0.00, 20.00, 0.00, 0, 0),
(130, NULL, '000001', '2009', '01', '02', '00', '00', '51', '402', '01', '01', '00', '00000', '', '50', 50.00, 0.00, 30.00, 0.00, 0.00, 0.00, 20.00, 0.00, 0, 0),
(131, NULL, '000001', '2009', '01', '02', '00', '00', '51', '402', '01', '00', '00', '00000', '', '', 50.00, 0.00, 30.00, 0.00, 0.00, 0.00, 20.00, 0.00, 1, 0),
(132, NULL, '000001', '2009', '01', '02', '00', '00', '51', '402', '00', '00', '00', '00000', '', '', 50.00, 0.00, 30.00, 0.00, 0.00, 0.00, 20.00, 0.00, 1, 0),
(133, NULL, '000001', '2009', '01', '02', '00', '00', '51', '401', '01', '04', '00', '00000', '', 'Uno mas para la cuenta', 50.00, 0.00, 0.00, 0.00, 0.00, 0.00, 50.00, 0.00, 0, 0),
(140, NULL, '000001', '2009', '01', '02', '00', '00', '51', '201', '01', '01', '00', '00000', '', 'I.S.L.R.', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0, 1),
(141, NULL, '000001', '2009', '01', '02', '00', '00', '51', '201', '01', '00', '00', '00000', '', '', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1, 1),
(142, NULL, '000001', '2009', '01', '02', '00', '00', '51', '201', '00', '00', '00', '00000', '', '', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_ingresos`
--

CREATE TABLE IF NOT EXISTS `presupuesto_ingresos` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `cod_presupuesto_ingreso` varchar(2) NOT NULL default '00',
  `ano` varchar(4) NOT NULL,
  `ramo` varchar(3) NOT NULL,
  `generica` varchar(2) NOT NULL,
  `especifica` varchar(2) NOT NULL,
  `subespecifica` varchar(2) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `monto` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cod_ppto_ingreso_index` (`cod_organizacion`,`ano`,`cod_presupuesto_ingreso`),
  UNIQUE KEY `codigo_index` (`cod_organizacion`,`ano`,`ramo`,`generica`,`especifica`,`subespecifica`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Volcar la base de datos para la tabla `presupuesto_ingresos`
--

INSERT INTO `presupuesto_ingresos` (`id`, `cod_organizacion`, `cod_presupuesto_ingreso`, `ano`, `ramo`, `generica`, `especifica`, `subespecifica`, `descripcion`, `monto`) VALUES
(2, '000001', '01', '2009', '401', '00', '00', '00', 'sss vacio', 0.00),
(3, '000001', '02', '2009', '123', '45', '67', '89', 'ytrew', 0.00),
(4, '000001', '03', '2009', '987', '65', '43', '21', 'Otro mas para hacer Pruebas', 125.85),
(5, '000002', '01', '2009', '000', '00', '30', '10', '401013010', 4010130.10),
(6, '000002', '02', '2009', '151', '60', '00', '00', '401-01-15-16', 15.16),
(7, '000002', '03', '2009', '301', '01', '01', '00', '351321', 0.00),
(8, '000002', '04', '2009', '222', '22', '22', '22', '2222', 0.00),
(9, '000001', '04', '2009', '308', '08', '08', '08', '150000', 150000.00),
(10, '000001', '05', '2009', '309', '09', '09', '09', '3090909', 3090909.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `cod_proveedor` varchar(6) NOT NULL,
  `rif` varchar(20) NOT NULL,
  `nombre` varchar(254) NOT NULL,
  `direccion` text NOT NULL,
  `telefono1` varchar(15) NOT NULL,
  `telefono2` varchar(15) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cod_proveedor_index` (`cod_organizacion`,`cod_proveedor`),
  UNIQUE KEY `rif_index` (`cod_organizacion`,`rif`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Volcar la base de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `cod_organizacion`, `cod_proveedor`, `rif`, `nombre`, `direccion`, `telefono1`, `telefono2`) VALUES
(1, '000002', '000001', '12345', '5135135', '351', '351', '351'),
(2, '000001', '000001', '137296221', 'Pedro Arrioja', '654654654654vvvv', '0416-7961306', '654564564'),
(3, '000001', '000002', 'J-0202020202', 'Proveedor Mayorista de Libros C.A.', '8979879879876', '98798798', '98798798'),
(4, '000001', '000003', 'J8080808080', 'Empresa 8080.C.A.', '6546546', '0295-8888888', '0295-8888888');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporal_causado`
--

CREATE TABLE IF NOT EXISTS `temporal_causado` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `tipo_documento` varchar(2) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `monto_parcial` decimal(12,2) NOT NULL default '0.00',
  `monto_pendiente` decimal(12,2) NOT NULL default '0.00',
  `monto_reversos` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `temporal_causado`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporal_compromisos`
--

CREATE TABLE IF NOT EXISTS `temporal_compromisos` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `tipo_documento` varchar(2) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `monto_parcial` decimal(12,2) NOT NULL default '0.00',
  `monto_pendiente` decimal(12,2) NOT NULL default '0.00',
  `monto_reversos` decimal(12,2) NOT NULL default '0.00',
  `problema` tinyint(1) NOT NULL default '0' COMMENT '0=Sin Problema, 1=Cod. No Existe, 2=Disponibilidad Insuficiente',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `temporal_compromisos`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporal_compromiso_causado`
--

CREATE TABLE IF NOT EXISTS `temporal_compromiso_causado` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `tipo_documento_causado` varchar(2) NOT NULL,
  `numero_documento_causado` varchar(6) NOT NULL,
  `tipo_documento_compromiso` varchar(2) NOT NULL,
  `numero_documento_compromiso` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL,
  `monto` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Volcar la base de datos para la tabla `temporal_compromiso_causado`
--

INSERT INTO `temporal_compromiso_causado` (`id`, `ano`, `cod_organizacion`, `tipo_documento_causado`, `numero_documento_causado`, `tipo_documento_compromiso`, `numero_documento_compromiso`, `sector`, `programa`, `subprograma`, `proyecto`, `actividad`, `partida`, `generica`, `especifica`, `subespecifica`, `ordinal`, `cod_fuente_financiamiento`, `monto`) VALUES
(10, '2009', '000001', 'OP', '905181', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '01', '00', '00000', '00', 3.00),
(12, '2009', '000001', 'OP', '905181', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '03', '00', '00000', '00', 4.25),
(13, '2009', '000001', 'OP', '905181', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '02', '00', '00000', '00', 8.00),
(14, '2009', '000001', 'OP', '364921', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '01', '00', '00000', '00', 3.00),
(15, '2009', '000001', 'OP', '364921', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '02', '00', '00000', '00', 8.00),
(16, '2009', '000001', 'OP', '364921', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '03', '00', '00000', '00', 4.25),
(23, '2009', '000001', 'OP', '235654', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '01', '00', '00000', '00', 3.00),
(24, '2009', '000001', 'OP', '235654', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '02', '00', '00000', '00', 8.00),
(25, '2009', '000001', 'OP', '235654', 'OS', '000001', '01', '02', '00', '00', '51', '401', '02', '03', '00', '00000', '00', 4.25);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporal_creditos`
--

CREATE TABLE IF NOT EXISTS `temporal_creditos` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL default '00',
  `monto_aumento` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `temporal_creditos`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporal_recortes`
--

CREATE TABLE IF NOT EXISTS `temporal_recortes` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL default '00',
  `monto_disminucion` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `temporal_recortes`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporal_traslados`
--

CREATE TABLE IF NOT EXISTS `temporal_traslados` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `numero` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  `partida` varchar(3) NOT NULL default '000',
  `generica` varchar(2) NOT NULL default '00',
  `especifica` varchar(2) NOT NULL default '00',
  `subespecifica` varchar(2) NOT NULL default '00',
  `ordinal` varchar(5) NOT NULL default '00000',
  `cod_fuente_financiamiento` varchar(2) NOT NULL default '00',
  `monto_disminucion` decimal(12,2) NOT NULL default '0.00',
  `monto_aumento` decimal(12,2) NOT NULL default '0.00',
  `problema` tinyint(1) NOT NULL default '0' COMMENT '0=Sin Problema, 1=Cod. No Existe, 2=Disponibilidad Insuficiente',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `temporal_traslados`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_documento`
--

CREATE TABLE IF NOT EXISTS `tipo_documento` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `siglas` varchar(2) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `operacion` varchar(2) NOT NULL COMMENT 'CO=Compromete, CA=Causa, PA=Paga',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cod_org_siglas` (`cod_organizacion`,`siglas`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Volcar la base de datos para la tabla `tipo_documento`
--

INSERT INTO `tipo_documento` (`id`, `cod_organizacion`, `siglas`, `nombre`, `operacion`) VALUES
(6, '000001', 'OC', 'Orden de Compra', 'CO'),
(7, '000001', 'OS', 'Orden de Servicio', 'CO'),
(9, '000001', 'OP', 'Orden de Pago', 'CA'),
(10, '000001', 'CH', 'Cheque', 'PA'),
(11, '000002', 'OP', 'Orden de Pago', 'CA'),
(12, '000002', 'OC', 'Orden de Compra', 'CO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidad_ejecutora`
--

CREATE TABLE IF NOT EXISTS `unidad_ejecutora` (
  `id` int(11) NOT NULL auto_increment,
  `ano` varchar(4) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `sector` varchar(2) NOT NULL default '00',
  `programa` varchar(2) NOT NULL default '00',
  `subprograma` varchar(2) NOT NULL default '00',
  `proyecto` varchar(2) NOT NULL default '00',
  `actividad` varchar(2) NOT NULL default '00',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Volcar la base de datos para la tabla `unidad_ejecutora`
--

INSERT INTO `unidad_ejecutora` (`id`, `ano`, `cod_organizacion`, `sector`, `programa`, `subprograma`, `proyecto`, `actividad`) VALUES
(1, '2009', '000001', '01', '02', '00', '00', '51'),
(2, '2008', '000001', '01', '02', '00', '00', '51');

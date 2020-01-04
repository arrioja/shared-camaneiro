-- phpMyAdmin SQL Dump
-- version 3.1.2deb1ubuntu0.2
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 14-06-2010 a las 11:06:24
-- Versión del servidor: 5.0.75
-- Versión de PHP: 5.2.6-3ubuntu4.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `bienes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bienes_imagenes`
--

CREATE TABLE IF NOT EXISTS `bienes_imagenes` (
  `id` int(11) NOT NULL auto_increment,
  `cod_bien` varchar(20) NOT NULL,
  `cod_imagen` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `bienes_imagenes`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bienes_muebles`
--

CREATE TABLE IF NOT EXISTS `bienes_muebles` (
  `id` int(11) NOT NULL auto_increment,
  `cod_organizacion` varchar(6) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `grupo` varchar(2) NOT NULL,
  `subgrupo` varchar(2) NOT NULL,
  `secciones` varchar(3) NOT NULL,
  `cantidad` varchar(3) NOT NULL,
  `cod_direccion` varchar(5) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `concepto_incorporacion` varchar(6) NOT NULL,
  `precio_incorporacion` double NOT NULL,
  `precio_desincorporacion` double NOT NULL,
  `serial` varchar(30) NOT NULL,
  `fecha_incorporacion` date NOT NULL,
  `fecha_desincorporacion` date default NULL,
  `a_vida_util` varchar(2) NOT NULL,
  `meses_vida_util` varchar(2) NOT NULL,
  `dias_vida_util` varchar(2) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Volcar la base de datos para la tabla `bienes_muebles`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conceptos`
--

CREATE TABLE IF NOT EXISTS `conceptos` (
  `id` int(11) NOT NULL auto_increment,
  `cod` varchar(6) NOT NULL,
  `descripcion` varchar(80) NOT NULL,
  `tipo` varchar(1) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `cod` (`cod`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35 ;

--
-- Volcar la base de datos para la tabla `conceptos`
--

INSERT INTO `conceptos` (`id`, `cod`, `descripcion`, `tipo`) VALUES
(1, '51', 'Desincorporación por traspaso', '1'),
(2, '52', 'Ventas', '1'),
(3, '53', 'Suministro de bienes a contratistas', '1'),
(4, '54', 'Suministro de bienes a otras entidades', '1'),
(5, '55', 'Desarme', '1'),
(6, '56', 'Inservibilidad', '1'),
(7, '57', 'Deterioro', '1'),
(8, '58', 'Demolición', '1'),
(9, '59', 'Muerte de Semovientes', '1'),
(10, '60', 'Faltantes por investigar', '1'),
(11, '61', 'Desincorporación por permuta', '1'),
(12, '62', 'Desincorporación por donación', '1'),
(13, '63', 'Desincorporación por adscripción', '1'),
(14, '65', 'Desincorporación por cambio de sub-grupación', '1'),
(15, '66', 'Correción de incorporaciones', '1'),
(16, '67', 'Desincorporación por otros conceptos', '1'),
(17, '01', 'Inventario Inicial', '0'),
(18, '02', 'Incorporación por traspaso', '0'),
(19, '03', 'Compras', '0'),
(20, '04', 'Construcción de inmuebles', '0'),
(21, '05', 'Adiciones y mejoras', '0'),
(22, '06', 'Producción de elementos (muebles)', '0'),
(23, '07', 'suministro de bienes de otras entidades', '0'),
(24, '08', 'Devolución de bienes suministrados a contratistas', '0'),
(25, '09', 'Nacimiento de Semovientes', '0'),
(26, '10', 'Reconstrucción de equipos', '0'),
(27, '11', 'Incorporación por donación', '0'),
(28, '12', 'Incorporación por permuta', '0'),
(29, '13', 'Adscripción de bienes', '0'),
(30, '14', 'Omisión de Inventario', '0'),
(31, '16', 'Incroporación por cambio de sub-grupación', '0'),
(32, '17', 'Corrección de desincorporaciones', '0'),
(33, '18', 'Incorporaciones por otros conceptos', '0'),
(34, '19', 'Incorporación de muebles procedentes de los almacenes', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descripcion_bienes_muebles`
--

CREATE TABLE IF NOT EXISTS `descripcion_bienes_muebles` (
  `id` int(11) NOT NULL auto_increment,
  `grupo` varchar(2) NOT NULL,
  `subgrupo` varchar(2) NOT NULL,
  `secciones` varchar(2) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `grupo` (`grupo`,`subgrupo`,`secciones`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=60 ;

--
-- Volcar la base de datos para la tabla `descripcion_bienes_muebles`
--

INSERT INTO `descripcion_bienes_muebles` (`id`, `grupo`, `subgrupo`, `secciones`, `descripcion`, `cod_organizacion`) VALUES
(1, '09', '01', '01', 'Televisores', '000001'),
(2, '09', '01', '02', 'Microondas', '000001'),
(3, '09', '01', '03', 'Cafetera', '000001'),
(4, '09', '01', '04', 'Nevera', '000001'),
(5, '09', '01', '05', 'Estantes', '000001'),
(6, '09', '01', '06', 'Archivadores', '000001'),
(7, '09', '01', '07', 'Maquina de Escribir', '000001'),
(8, '09', '01', '08', 'Calculadora de Mesa', '000001'),
(9, '09', '01', '09', 'Escritorio', '000001'),
(10, '09', '01', '10', 'Sillas', '000001'),
(11, '09', '01', '11', 'Telefonos', '000001'),
(12, '09', '01', '12', 'Mesa para Computadora', '000001'),
(13, '09', '01', '13', 'Persianas', '000001'),
(14, '09', '01', '14', 'Mesa Normal', '000001'),
(15, '09', '01', '15', 'Obras de Arte', '000001'),
(16, '09', '01', '16', 'Pizarra', '000001'),
(17, '09', '01', '17', 'Fax', '000001'),
(18, '09', '01', '18', 'Gavetero', '000001'),
(19, '09', '01', '19', 'Cartelera', '000001'),
(20, '09', '01', '20', 'Gabinetes', '000001'),
(21, '09', '01', '21', 'Central Telefonica', '000001'),
(22, '09', '01', '22', 'Plastificadora', '000001'),
(23, '09', '01', '23', 'Encuadernadora', '000001'),
(24, '09', '01', '24', 'Mueble Fijo', '000001'),
(25, '09', '01', '25', 'Corta Papeles', '000001'),
(26, '09', '01', '26', 'Fotocopiadoras', '000001'),
(27, '09', '01', '27', 'Soporte para Television', '000001'),
(28, '09', '01', '28', 'Filtro de Agua', '000001'),
(29, '09', '01', '29', 'Recipiente de Agua', '000001'),
(30, '09', '01', '30', 'Utiles Deportivos', '000001'),
(31, '09', '01', '31', 'Maletines, Morrales y Bolsos', '000001'),
(32, '09', '01', '32', 'Rotafolio', '000001'),
(33, '09', '01', '33', 'Engrapadora Semi Industrial', '000001'),
(34, '09', '01', '34', 'Banderas', '000001'),
(35, '09', '01', '35', 'Archimovil', '000001'),
(36, '09', '01', '36', 'Podium', '000001'),
(37, '09', '01', '37', 'Mueble (Tipo Sofa)', '000001'),
(38, '09', '01', '38', 'Juego de Escritorio', '000001'),
(39, '09', '01', '39', 'Equipo de Sonido', '000001'),
(40, '09', '02', '01', 'CPUs', '000001'),
(41, '09', '02', '02', 'Monitor', '000001'),
(42, '09', '02', '03', 'Teclado', '000001'),
(43, '09', '02', '04', 'Regulador de Voltaje', '000001'),
(44, '09', '02', '05', 'Cornetas (Speakers)', '000001'),
(45, '09', '02', '06', 'Escaner', '000001'),
(46, '09', '02', '07', 'Camara Digital', '000001'),
(47, '09', '02', '08', 'Concetrador (HUB) ', '000001'),
(48, '09', '02', '09', 'Lector de Barra', '000001'),
(49, '09', '02', '10', 'Impresora', '000001'),
(50, '09', '02', '11', 'U.P.S', '000001'),
(51, '09', '02', '12', 'Data Switch', '000001'),
(52, '09', '02', '13', 'Computadoras portatiles (laptop)', '000001'),
(53, '09', '02', '14', 'Proyector (Video Beam)', '000001'),
(54, '09', '02', '15', 'Llave de Seguridad para Programas', '000001'),
(55, '09', '02', '16', 'Modem', '000001'),
(56, '99', '01', '01', 'Odometro', '000001'),
(57, '99', '01', '02', 'Vara de Ext. de Medida', '000001'),
(58, '99', '01', '03', 'Estuche de Herramienta', '000001'),
(59, '05', '01', '01', 'Automoviles', '000001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo`
--

CREATE TABLE IF NOT EXISTS `grupo` (
  `id` int(11) NOT NULL auto_increment,
  `grupo` varchar(2) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Volcar la base de datos para la tabla `grupo`
--

INSERT INTO `grupo` (`id`, `grupo`, `descripcion`, `cod_organizacion`) VALUES
(2, '09', 'MÃ¡quinas, Equipos y DemÃ¡s Equipos de Oficina y Alojamiento', '000001'),
(3, '99', 'Otros Activos Reales', '000001'),
(4, '05', 'Equipos de Transporte, Traccion y ElevaciÃ³n', '000001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_bienes`
--

CREATE TABLE IF NOT EXISTS `movimientos_bienes` (
  `id` int(11) NOT NULL auto_increment,
  `tipo_movimiento` varchar(2) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  `cod_bien` varchar(10) NOT NULL,
  `cod_direccion` varchar(6) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Volcar la base de datos para la tabla `movimientos_bienes`
--

INSERT INTO `movimientos_bienes` (`id`, `tipo_movimiento`, `cod_organizacion`, `cod_bien`, `cod_direccion`, `fecha`, `motivo`) VALUES
(17, '01', '000001', '4444444444', '01001', '2010-06-14', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subgrupo`
--

CREATE TABLE IF NOT EXISTS `subgrupo` (
  `id` int(11) NOT NULL auto_increment,
  `grupo` varchar(2) NOT NULL,
  `subgrupo` varchar(2) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `cod_organizacion` varchar(6) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `grupo` (`grupo`,`subgrupo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Volcar la base de datos para la tabla `subgrupo`
--

INSERT INTO `subgrupo` (`id`, `grupo`, `subgrupo`, `descripcion`, `cod_organizacion`) VALUES
(1, '09', '01', 'Mobiliario y Equipos de Oficina y Alojamiento', '000001'),
(2, '09', '02', 'Equipos de Computacion y Procesamiento de Datos', '000001'),
(3, '99', '01', 'Otros Activos Reales', '000001'),
(4, '05', '01', 'Vehiculos Automotores Terrestres', '000001');

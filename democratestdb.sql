-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-03-2021 a las 05:15:16
-- Versión del servidor: 10.1.34-MariaDB
-- Versión de PHP: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `democratestdb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `laboratorio`
--

CREATE TABLE `laboratorio` (
  `id` int(11) NOT NULL,
  `nombre` varchar(60) COLLATE latin1_general_ci NOT NULL,
  `id_plantilla` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Volcado de datos para la tabla `laboratorio`
--

INSERT INTO `laboratorio` (`id`, `nombre`, `id_plantilla`) VALUES
(1, 'ARQUIMEA', 1),
(2, 'LIFE LENGTH', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillaxls`
--

CREATE TABLE `plantillaxls` (
  `id` int(11) NOT NULL,
  `nombre` varchar(60) COLLATE latin1_general_ci NOT NULL,
  `ruta` varchar(150) COLLATE latin1_general_ci NOT NULL,
  `fila_encab` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Volcado de datos para la tabla `plantillaxls`
--

INSERT INTO `plantillaxls` (`id`, `nombre`, `ruta`, `fila_encab`) VALUES
(1, 'Envio de datos al laboratorio Arquimea.xlsx', 'C:\\xampp\\htdocs\\democratest\\files', 1),
(2, 'Envio de datos al laboratorio.xlsx', 'C:\\xampp\\htdocs\\democratest\\files', 1),
(3, 'informe de resultados del laboratorio LL.XLSX', 'C:\\xampp\\htdocs\\democratest\\files', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `test_paciente`
--

CREATE TABLE `test_paciente` (
  `id` int(11) NOT NULL,
  `codigo_lab` int(11) NOT NULL,
  `fecha_carga` varchar(10) COLLATE latin1_general_ci NOT NULL,
  `cedula_paciente` varchar(35) COLLATE latin1_general_ci DEFAULT ' ',
  `pasaporte_paciente` varchar(35) COLLATE latin1_general_ci DEFAULT ' ',
  `nombre_paciente` varchar(60) COLLATE latin1_general_ci NOT NULL,
  `apellido1_paciente` varchar(60) COLLATE latin1_general_ci NOT NULL,
  `apellido2_paciente` varchar(60) COLLATE latin1_general_ci DEFAULT ' ',
  `fecha_nac_paciente` varchar(10) COLLATE latin1_general_ci DEFAULT ' ',
  `sexo_paciente` varchar(10) COLLATE latin1_general_ci DEFAULT ' ',
  `email_paciente` varchar(140) COLLATE latin1_general_ci DEFAULT ' ',
  `fecha_test` varchar(20) COLLATE latin1_general_ci DEFAULT ' ',
  `motivo_test` varchar(140) COLLATE latin1_general_ci DEFAULT ' ',
  `codigo_test` varchar(10) COLLATE latin1_general_ci DEFAULT ' ',
  `tipo_test` varchar(10) COLLATE latin1_general_ci DEFAULT ' ',
  `resultado_test` varchar(200) COLLATE latin1_general_ci DEFAULT ' ',
  `nProtein` varchar(15) COLLATE latin1_general_ci DEFAULT ' ',
  `sProtein` varchar(15) COLLATE latin1_general_ci DEFAULT ' ',
  `orfLab` varchar(15) COLLATE latin1_general_ci DEFAULT ' ',
  `ELISA` varchar(15) COLLATE latin1_general_ci DEFAULT ' ',
  `PCRMultidiagnostico` varchar(15) COLLATE latin1_general_ci DEFAULT ' ',
  `pdf_resultado` varchar(250) COLLATE latin1_general_ci DEFAULT ' ',
  `sts_enviomail` varchar(2) COLLATE latin1_general_ci DEFAULT ' ',
  `fecha_envio` varchar(10) COLLATE latin1_general_ci DEFAULT ' ',
  `enviar` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `laboratorio`
--
ALTER TABLE `laboratorio`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `plantillaxls`
--
ALTER TABLE `plantillaxls`
  ADD UNIQUE KEY `ID` (`id`);

--
-- Indices de la tabla `test_paciente`
--
ALTER TABLE `test_paciente`
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `laboratorio`
--
ALTER TABLE `laboratorio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `plantillaxls`
--
ALTER TABLE `plantillaxls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `test_paciente`
--
ALTER TABLE `test_paciente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


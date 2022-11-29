-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2022 at 10:27 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `localdecomida`
--

-- --------------------------------------------------------

--
-- Table structure for table `elementos_del_pedido`
--

CREATE TABLE `elementos_del_pedido` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `precio` float NOT NULL,
  `codigo_pedido` varchar(5) COLLATE utf8mb4_spanish_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'en espera',
  `tiempo_estimado` int(11) NOT NULL,
  `id_empleado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `elementos_del_pedido`
--

INSERT INTO `elementos_del_pedido` (`id`, `id_producto`, `precio`, `codigo_pedido`, `estado`, `tiempo_estimado`, `id_empleado`) VALUES
(1, 1, 850, 'AAAAA', 'servido', 45, 12),
(2, 1, 850, 'AAAAA', 'servido', 45, 12),
(3, 1, 850, 'AAAAA', 'servido', 45, 12),
(23, 4, 750, 'AAAAA', 'servido', 2, 11),
(24, 5, 600, 'AAAAA', 'servido', 2, 6),
(25, 2, 950, 'AAAAA', 'servido', 45, 12),
(26, 7, 625, 'AAAAA', 'servido', 2, 6),
(52, 2, 950, 'PEDID', 'servido', 35, 12),
(53, 3, 750, 'PEDID', 'servido', 25, 12),
(54, 3, 750, 'PEDID', 'servido', 25, 12),
(55, 4, 750, 'PEDID', 'servido', 2, 11),
(56, 5, 600, 'PEDID', 'servido', 5, 6),
(57, 2, 950, 'PPZZZ', 'servido', 31, 12),
(58, 3, 750, 'PPZZZ', 'servido', 20, 12),
(59, 3, 750, 'PPZZZ', 'servido', 20, 12),
(60, 4, 300, 'PPZZZ', 'servido', 2, 11),
(61, 5, 500, 'PPZZZ', 'servido', 5, 6);

-- --------------------------------------------------------

--
-- Table structure for table `encuestas`
--

CREATE TABLE `encuestas` (
  `id` int(11) NOT NULL,
  `id_mesa` varchar(5) COLLATE utf8mb4_spanish_ci NOT NULL,
  `id_pedido` varchar(5) COLLATE utf8mb4_spanish_ci NOT NULL,
  `comentario` varchar(66) COLLATE utf8mb4_spanish_ci NOT NULL,
  `nota_mesa` int(11) NOT NULL,
  `nota_restaurante` int(11) NOT NULL,
  `nota_comida` int(11) NOT NULL,
  `nota_mozo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `encuestas`
--

INSERT INTO `encuestas` (`id`, `id_mesa`, `id_pedido`, `comentario`, `nota_mesa`, `nota_restaurante`, `nota_comida`, `nota_mozo`) VALUES
(4, 'ABCDE', 'AAAAA', 'Todo muy bueno', 10, 10, 10, 10),
(5, '85085', 'PEDID', 'Me gusto, lindo ambiente', 6, 6, 7, 6),
(6, '85085', 'PPZZZ', 'Muy buena comida, me gusto.', 8, 8, 9, 10);

-- --------------------------------------------------------

--
-- Table structure for table `mesas`
--

CREATE TABLE `mesas` (
  `codigo` varchar(5) COLLATE utf8mb4_spanish_ci NOT NULL,
  `estado` varchar(28) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'cerrada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `mesas`
--

INSERT INTO `mesas` (`codigo`, `estado`) VALUES
('12345', 'cerrada'),
('85085', 'cerrada'),
('ABCDE', 'cerrada');

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `codigo` varchar(5) COLLATE utf8mb4_spanish_ci NOT NULL,
  `id_mesa` varchar(5) COLLATE utf8mb4_spanish_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'con cliente pidiendo',
  `id_mozo` int(11) NOT NULL,
  `tiempo_estimado` int(11) DEFAULT NULL,
  `fecha_inicio` varchar(15) COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha_fin` varchar(15) COLLATE utf8mb4_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `pedidos`
--

INSERT INTO `pedidos` (`codigo`, `id_mesa`, `estado`, `id_mozo`, `tiempo_estimado`, `fecha_inicio`, `fecha_fin`) VALUES
('AAAAA', 'ABCDE', 'finalizado', 10, 45, '22-11-27 21:40', '22-11-27 22:40'),
('PEDID', '85085', 'finalizado', 10, 35, '22-11-28 19:52', '22-11-28 20:29'),
('PPZZZ', '85085', 'finalizado', 10, 31, '22-11-29 16:10', '22-11-29 16:19');

-- --------------------------------------------------------

--
-- Table structure for table `personal`
--

CREATE TABLE `personal` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `clave` varchar(250) COLLATE utf8mb4_spanish_ci NOT NULL,
  `rol` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `salario` float NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `personal`
--

INSERT INTO `personal` (`id`, `usuario`, `clave`, `rol`, `salario`, `estado`) VALUES
(1, 'socioJuan', '$2y$10$wrcMdGQWaXdkcy7rbCsacuXNqCjsB2A8hRlwhYDJBc3bOFGxqwfae', 'socio', 180000, 1),
(2, 'socioPedro', '$2y$10$xdsaLF/YhudvOvwZz4HU9ujhcWTE/B9WFvOw2cAtCFoi24yfSs3Ji', 'socio', 180000, 1),
(3, 'socioOkabe', '$2y$10$zJSXuL9Mz1QNOXnRb.aV.eYLw.rOneygdJ0f1YTqdsiVNuhaCqXf2', 'socio', 180000, 1),
(4, 'mozoPedro', '$2y$10$gyJW4CIu61z1ZXHssXRwU.0cN8OSSZnk373.i/2E.f3tyDgt4dCWC', 'mozo', 80000, 1),
(5, 'cocineroAna', '$2y$10$U8tlXeaqJS5GD1G0k6rYEOwxgKW8DhJGknPac61UJSmlFniIoE9G6', 'cocinero', 80000, 1),
(6, 'bartenderJuan', '$2y$10$KwMsan.wMMA8VKUns2AwfOeYzamap.XapqO1cAc2IO/xYPM.EVZ4e', 'bartender', 80000, 1),
(7, 'cervezeroAlejando', '$2y$10$sLtwaATwIbLfLDM8681m6.zQ.tJTLvy615yptfThmxy5WIRXQhPCC', 'cervezero', 80000, 1),
(9, 'cervezeroMateo', '$2y$10$/Wa5FmfbEOHPPj3MtGxx7etgPTMeR0ZdZOOw5m7133LJ2N176Ybnm', 'cervezero', 80000, 1),
(10, 'mozoJuan', '$2y$10$tmrSghTSkt7Wjhio.5LsHuM9.rrcuR/0GDG09zgNVKx83yKf70OIa', 'mozo', 80000, 1),
(11, 'cervezeroJuan', '$2y$10$ND6OF1VzbBSQHM6qLKQexusvzkFWrXW0OzZ2cfneOOkFjleLC2EK2', 'cervezero', 80000, 1),
(12, 'cocineroJuan', '$2y$10$1WDAR3wRiggxFlKw6a2WCeHpCC1C5LYOl8Go2sRyyoqQX97n8Ek1y', 'cocinero', 80000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `precio` float NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `tiempo_estimado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id`, `precio`, `nombre`, `tipo`, `tiempo_estimado`) VALUES
(1, 850, 'Bife de chorizo', 'cocina', 30),
(2, 950, 'Milanesa a Caballo', 'cocina', 30),
(3, 750, 'hamburguesas de garbanzo', 'cocina', 20),
(4, 300, 'corona', 'cerveza', 2),
(5, 500, 'daikiri', 'bar', 5),
(7, 625, 'black russian', 'bar', 6);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `elementos_del_pedido`
--
ALTER TABLE `elementos_del_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `codigo_pedido` (`codigo_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `encuestas`
--
ALTER TABLE `encuestas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`codigo`);

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`codigo`);

--
-- Indexes for table `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `elementos_del_pedido`
--
ALTER TABLE `elementos_del_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `encuestas`
--
ALTER TABLE `encuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

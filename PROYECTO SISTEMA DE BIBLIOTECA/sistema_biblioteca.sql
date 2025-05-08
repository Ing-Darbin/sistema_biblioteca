-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 08-05-2025 a las 21:00:16
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_biblioteca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`) VALUES
(3, 'Ciencia'),
(4, 'Historia'),
(5, 'Literatura'),
(6, 'Tecnología'),
(8, 'Sociales'),
(12, 'Matemáticas'),
(13, 'Inglés'),
(15, 'Física'),
(16, 'Historia'),
(17, 'Filosofía'),
(19, 'Química'),
(20, 'Biología'),
(21, 'Geografía'),
(22, 'Arte'),
(23, 'Música');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id_libro` int(11) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `autor` varchar(100) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `estado` enum('disponible','prestado') DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id_libro`, `titulo`, `autor`, `isbn`, `id_categoria`, `estado`) VALUES
(2, 'Matemática básica ', 'Juan Pérez', '9781234567890', 12, 'disponible'),
(4, 'Programación Orientada a Objeto', 'google', '978-01020102', 6, 'disponible'),
(5, 'Cosmos', 'Carl Sagan', '978-0345539434', 4, 'disponible'),
(6, 'Una Breve Historia de Casi Todo', 'Bill Bryson', '978-0767908177', 4, 'disponible'),
(7, 'El Gen: Una Historia Íntima', 'Siddhartha Mukherjee', '978-1476733500', 4, 'disponible'),
(8, 'Sapiens: De animales a dioses', 'Yuval Noah Harari', '978-0062316097', 5, 'disponible'),
(9, 'Armas, Gérmenes y Acero', 'Jared Diamond', '978-0393317558', 5, 'disponible'),
(10, 'La Segunda Guerra Mundial', 'Antony Beevor', '978-0143122099', 5, 'disponible'),
(11, 'Cien Años de Soledad', 'Gabriel García Márquez', '978-0060883287', 6, 'disponible'),
(12, 'Don Quijote de la Mancha', 'Miguel de Cervantes', '978-0060934347', 6, 'disponible'),
(13, 'Matar un Ruiseñor', 'Harper Lee', '978-0061120084', 6, 'disponible'),
(14, 'Innovadores: Los genios que inventaron el futuro', 'Walter Isaacson', '978-1476708690', 8, 'disponible'),
(15, 'Inteligencia Artificial: Lo que todo el mundo debe saber', 'Jerry Kaplan', '978-0190294809', 8, 'disponible'),
(16, 'El Hombre y la Máquina', 'José Luis Cordeiro', '978-8423427307', 8, 'disponible'),
(17, 'Las Venas Abiertas de América Latina', 'Eduardo Galeano', '978-0853459910', 12, 'disponible'),
(18, 'Vigilar y Castigar', 'Michel Foucault', '978-0679752554', 12, 'disponible'),
(19, 'El Contrato Social', 'Jean-Jacques Rousseau', '978-0140442014', 12, 'disponible'),
(20, 'Álgebra de Baldor', 'Aurelio Baldor', '978-9708170000', 13, 'disponible'),
(21, 'El Hombre que Calculaba', 'Malba Tahan', '978-8498800700', 13, 'disponible'),
(22, 'Introducción al Cálculo y al Análisis Matemático', 'Richard Courant', '978-3540665694', 13, 'disponible'),
(23, 'English Grammar in Use', 'Raymond Murphy', '978-1108457651', 15, 'disponible'),
(24, 'To Kill a Mockingbird (Intermediate Reader)', 'Harper Lee (Adapted)', '978-0141332938', 15, 'disponible'),
(25, 'Oxford Picture Dictionary (Monolingual English)', 'Jayme Adelson-Goldstein', '978-0194505298', 15, 'disponible'),
(26, 'Física Conceptual', 'Paul G. Hewitt', '978-6073230497', 16, 'disponible'),
(27, 'Breve Historia del Tiempo', 'Stephen Hawking', '978-0553380163', 16, 'disponible'),
(28, 'Seis Piezas Fáciles', 'Richard P. Feynman', '978-0465025275', 16, 'disponible'),
(29, 'El Mundo de Sofía', 'Jostein Gaarder', '978-8478448253', 19, 'disponible'),
(30, 'Ética para Amador', 'Fernando Savater', '978-8434412087', 19, 'disponible'),
(31, 'Meditaciones', 'Marco Aurelio', '978-0140449334', 19, 'disponible'),
(32, 'Química General', 'Raymond Chang', '978-6071509284', 20, 'disponible'),
(33, 'Química Orgánica', 'Paula Yurkanis Bruice', '978-6073230916', 20, 'prestado'),
(34, 'La Cuchara Menguante', 'Sam Kean', '978-0316051735', 20, 'disponible'),
(35, 'Biología Celular y Molecular', 'Gerald Karp', '978-6071511362', 21, 'disponible'),
(36, 'El Origen de las Especies', 'Charles Darwin', '978-0451529060', 21, 'disponible'),
(37, 'Vida: La Ciencia de la Biología', 'Sadava, Hillis, Heller, Hacker', '978-9500698519', 21, 'disponible'),
(38, 'Geografía General', 'Antonio Tovar', '978-8434434652', 22, 'disponible'),
(39, 'Atlas Geográfico Universal', 'Varios Autores (Editorial Planeta)', '978-8408194200', 22, 'disponible'),
(40, 'Prisioneros de la Geografía', 'Tim Marshall', '978-1501121463', 22, 'disponible'),
(41, 'Historia del Arte', 'E.H. Gombrich', '978-0714897499', 23, 'disponible'),
(42, 'Modos de Ver', 'John Berger', '978-0140135152', 23, 'disponible'),
(43, 'El Color: Historia de un Concepto', 'Michel Pastoureau', '978-8449322262', 23, 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id_prestamo` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_libro` int(11) DEFAULT NULL,
  `fecha_prestamo` date DEFAULT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `devuelto` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id_prestamo`, `id_usuario`, `id_libro`, `fecha_prestamo`, `fecha_devolucion`, `devuelto`) VALUES
(2, 7, 2, '2025-05-08', '2025-05-15', 1),
(3, 7, 4, '2025-05-08', '2025-05-15', 1),
(7, 12, 33, '2025-05-08', '2025-05-10', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_reserva` datetime NOT NULL,
  `fecha_limite_recogida` datetime DEFAULT NULL,
  `estado_reserva` enum('pendiente','lista_para_recoger','completada','cancelada','vencida') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `id_libro`, `id_usuario`, `fecha_reserva`, `fecha_limite_recogida`, `estado_reserva`) VALUES
(1, 4, 12, '2025-05-08 14:58:50', NULL, 'pendiente'),
(2, 37, 12, '2025-05-08 15:52:29', NULL, 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contraseña` varchar(255) DEFAULT NULL,
  `tipo_usuario` enum('estudiante','bibliotecario') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `contraseña`, `tipo_usuario`) VALUES
(1, 'Javier ', 'javierbeltran12@gmail.com', '1234567890', 'estudiante'),
(2, 'Jvier', 'jvierbel@gmail.com', '$2y$10$xBKiflj0JfVDIcsXZVxVPuzWKlmg9sUTK7Odtldc1uCaG3ceh8/du', 'estudiante'),
(3, 'Calos Davila ', 'davilacarlos34@gmail.com', '$2y$10$Lj.gLzJ.yB5mggxOWqmfR.6HjFBMVua9Dnzudcu/Lgq7WXFdtTSv.', 'estudiante'),
(4, 'José Alvarez', 'alvarezjose56@gmail.com', '$2y$10$oU/4SQmg3QDWgq8CBZxNeOXeLMZDynbTFYMu5ZdF7N7oSfDvRHpj2', 'bibliotecario'),
(5, 'Alonso Beltran', 'alonsobeltran@gmail.com', '$2y$10$n0OZg0wxWy4VogHmf49GTOlzh54OrJmppZnW4AoFqkiz9sr9Ihgwa', 'bibliotecario'),
(6, 'Lucia Alvarado', 'alvaradolucia123@gmail.com', '$2y$10$sJXMwQsk42Q/asjTRrzS7eM742WlEGjpIJAAhrX2mD8PSXLNd4cY.', 'bibliotecario'),
(7, 'Hector Diaz', 'diaz23@gmail.com', '$2y$10$Ndi09zrMBSEJ22rXYO6vKuE5RCEbN30.fhBQl/8noG4dbstTuSeci', 'estudiante'),
(8, 'Darwing', 'pereiradarwing81@gmail.com', '$2y$10$EzptlUCIcPxB1q4qUXMRH.paaurjk9C8wTPB1GlxfqCi6FTqJvqBu', 'bibliotecario'),
(12, 'Pedro', 'pedro@gmail.com', '$2y$10$ieYYiT1MWNapJ60KjC1JvOFnLIQOvVfpiQx.TmYefl5.MSTSSiXqi', 'estudiante'),
(13, 'admin', 'admin@gmail.com', '$2y$10$DOagBEof1HosfeePt974Hu73us6oicb1ZCZ1IDpRA.s1/6d9Y6SlK', 'bibliotecario'),
(14, 'darbin', 'pereiradarwing81@outlook.com', '$2y$10$sh/eJuxpG8tb2R1nY0a3feCjL.RWZQjm7yFOlEWFXy7JBeQCyzLo.', 'bibliotecario');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id_libro`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id_prestamo`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_libro` (`id_libro`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `id_libro` (`id_libro`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id_libro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id_prestamo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `libros_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `prestamos_ibfk_2` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

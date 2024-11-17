-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-11-2024 a las 20:18:30
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tpe-web2-hiese-peralta`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_user`
--

CREATE TABLE `admin_user` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `email` varchar(250) NOT NULL,
  `contraseña` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `admin_user`
--

INSERT INTO `admin_user` (`id`, `nombre_usuario`, `email`, `contraseña`) VALUES
(1, '2Ailen4', 'ailen@gmail.com', '$2y$10$G8ZqydTlUwaUN2n5U1oWoeoaGU.Gt69EL/n1tofqBeC1JMrgFb6fS'),
(2, 'webadmin', 'webadmin@gmail.com', '$2y$10$4mYQibyjU3nAXCOm8Zy.f.yQwohm8i5GzFuj1ZaRMZxRIyx6kPyY2'),
(3, 'Marian07', 'mariano@gmail.com', '$2y$10$vJpeBh/wRpjORwwQemI/e.Zxtq8KdCYPyMSODEVtFS4p30uWSSJLm');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aprendizaje`
--

CREATE TABLE `aprendizaje` (
  `FK_id_pokemon` int(11) NOT NULL,
  `FK_id_movimiento` int(11) NOT NULL,
  `nivel_aprendizaje` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `aprendizaje`
--

INSERT INTO `aprendizaje` (`FK_id_pokemon`, `FK_id_movimiento`, `nivel_aprendizaje`) VALUES
(1, 1, 15),
(2, 1, 15),
(4, 1, 1),
(17, 1, 12),
(18, 1, 30),
(20, 1, 14),
(21, 1, 2),
(1, 2, 10),
(5, 2, 50),
(9, 2, 50),
(12, 2, 35),
(15, 2, 4),
(16, 2, 19),
(2, 3, 20),
(4, 3, 3),
(7, 3, 40),
(10, 3, 35),
(13, 3, 15),
(15, 3, 41),
(6, 4, 35),
(7, 4, 1),
(9, 4, 25),
(12, 4, 40),
(19, 4, 18),
(21, 4, 10),
(3, 5, 25),
(9, 5, 40),
(22, 5, 12),
(2, 6, 15),
(3, 6, 10),
(10, 6, 45),
(12, 6, 20),
(17, 6, 15),
(23, 6, 15),
(2, 7, 24),
(5, 7, 36),
(6, 7, 40),
(14, 7, 32),
(19, 7, 28),
(23, 7, 8),
(8, 8, 10),
(11, 8, 16),
(15, 8, 1),
(17, 8, 17),
(20, 8, 3),
(22, 8, 22),
(2, 9, 20),
(5, 9, 36),
(8, 9, 20),
(11, 9, 25),
(14, 9, 12),
(18, 9, 20),
(6, 10, 20),
(7, 10, 55),
(9, 10, 30),
(10, 10, 20),
(16, 10, 20),
(20, 10, 33),
(8, 11, 15),
(13, 11, 5),
(3, 12, 18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrenadorpokemon`
--

CREATE TABLE `entrenadorpokemon` (
  `id_entrenador` int(12) NOT NULL,
  `nombre_entrenador` varchar(15) NOT NULL,
  `ciudad_origen` varchar(20) NOT NULL,
  `nivel_entrenador` int(11) NOT NULL DEFAULT 1,
  `cant_medallas` int(11) NOT NULL DEFAULT 0,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `entrenadorpokemon`
--

INSERT INTO `entrenadorpokemon` (`id_entrenador`, `nombre_entrenador`, `ciudad_origen`, `nivel_entrenador`, `cant_medallas`, `descripcion`, `imagen`) VALUES
(1, 'Ash Ketchum', 'Pueblo Paleta', 10, 8, 'Un joven entrenador que sueña con ser un Maestro Pokémon.', 'images/trainers/Ash Ketchum.jpg'),
(2, 'Misty', 'Cerulean City', 9, 6, 'Una entrenadora de tipo Agua que es la líder del gimnasio de Cerulean.', 'images/trainers/Misty.jpg'),
(3, 'Brock', 'Pueblo Pewter', 10, 5, 'El líder del gimnasio de Pewter y un gran cocinero.', 'images/trainers/Brock.jpg'),
(4, 'Tracey Sketchit', 'Pueblo Paleta', 8, 2, 'Un observador Pokémon que acompaña a Ash en su viaje.', 'images/trainers/Tracey Sketchit.jpg'),
(5, 'May', 'Hoenn', 7, 4, 'Una entrenadora de tipo Planta que quiere ser coordinadora.', 'images/trainers/May.jpg'),
(6, 'Dawn', 'Sinnoh', 8, 3, 'Una coordinadora que aspira a ganar el Gran Festival Pokémon.', 'images/trainers/Dawn.jpg'),
(7, 'Serena', 'Kalos', 9, 5, 'Una coordinadora que compite en concursos Pokémon.', 'images/trainers/Serena.jpg'),
(8, 'Gary Oak', 'Pueblo Paleta', 10, 7, 'El rival de Ash, un entrenador ambicioso y talentoso.', 'images/trainers/Gary Oak.jpg'),
(9, 'Cynthia', 'Region Sinnoh', 12, 8, 'La Campeona de la Liga Pokémon Sinnoh, experta en Pokémon de tipo Dragón.', 'images/trainers/Cynthia.jpg'),
(10, 'Giovanni', 'Viridian City', 11, 9, 'Líder del Team Rocket y uno de los antagonistas más poderosos.', 'images/trainers/Giovanni.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento`
--

CREATE TABLE `movimiento` (
  `id_movimiento` int(11) NOT NULL,
  `nombre_movimiento` varchar(50) NOT NULL,
  `tipo_movimiento` varchar(20) NOT NULL,
  `poder_movimiento` int(11) NOT NULL,
  `precision_movimiento` int(11) NOT NULL,
  `descripcion_movimiento` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `movimiento`
--

INSERT INTO `movimiento` (`id_movimiento`, `nombre_movimiento`, `tipo_movimiento`, `poder_movimiento`, `precision_movimiento`, `descripcion_movimiento`) VALUES
(1, 'Impactrueno', 'Electrico', 40, 90, 'Un pequeño rayo que golpea al oponente.'),
(2, 'Llamarada', 'Fuego', 110, 85, 'Un poderoso ataque de fuego con posibilidad de quemar al oponente.'),
(3, 'Rayo', 'Electrico', 90, 100, 'Un ataque de rayo que puede paralizar al objetivo.'),
(4, 'Terremoto', 'Tierra', 100, 100, 'Provoca un terremoto que daña a todos los Pokémon en el campo.'),
(5, 'Hidrobomba', 'Agua', 110, 80, 'Un fuerte ataque de agua con alta potencia pero baja precisión.'),
(6, 'Lanzallamas', 'Fuego', 90, 100, 'Una llama intensa que golpea al oponente con posibilidad de quemarlo.'),
(7, 'Vuelo', 'Volador', 90, 95, 'El Pokémon vuela y ataca en el siguiente turno.'),
(8, 'Latigo Cepa', 'Planta', 45, 100, 'Golpea al oponente con látigos de plantas.'),
(9, 'Hoja Afilada', 'Planta', 55, 95, 'Ataca con hojas filosas con alta probabilidad de golpe crítico.'),
(10, 'Mordisco', 'Siniestro', 60, 100, 'Muerde al oponente con posibilidad de hacer retroceder.'),
(11, 'Burbujas', 'Agua', 25, 90, 'Causa daño y tiene una probabilidad del 10% de bajar en un nivel la velocidad del objetivo'),
(12, 'Burbujas', 'Agua', 25, 90, 'Causa daño y tiene una probabilidad del 10% de bajar en un nivel la velocidad del objetivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pokemon`
--

CREATE TABLE `pokemon` (
  `id` int(11) NOT NULL,
  `nro_pokedex` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `fecha_captura` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `peso` int(11) NOT NULL,
  `FK_id_entrenador` int(12) DEFAULT NULL,
  `imagen_pokemon` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `pokemon`
--

INSERT INTO `pokemon` (`id`, `nro_pokedex`, `nombre`, `tipo`, `fecha_captura`, `peso`, `FK_id_entrenador`, `imagen_pokemon`) VALUES
(1, 1, 'Bulbasaur', 'Planta', '2018-11-12 19:16:51', 70, 1, 'images/pokemons/Bulbasaur.jpg'),
(2, 4, 'Charmander', 'Fuego', '2022-01-07 16:16:00', 85, 2, 'images/pokemons/Charmander.jpg'),
(3, 7, 'Squirtle', 'Agua', '2024-01-18 15:06:51', 90, 3, 'images/pokemons/Squirtle.jpg'),
(4, 25, 'Pikachu', 'Electrico', '2024-11-17 14:31:45', 60, 4, 'images/pokemons/Pikachu.jpg'),
(5, 6, 'Charizard', 'Fuego Volador', '2024-11-04 16:16:51', 905, 2, 'images/pokemons/Charizard.jpg'),
(6, 130, 'Gyarados', 'Agua Volador', '2010-06-04 02:17:50', 2350, 3, 'images/pokemons/Gyarados.jpg'),
(7, 150, 'Mewtwo', 'Psiquico', '2024-11-17 14:32:34', 1220, NULL, 'images/pokemons/Mewtwo.jpg'),
(8, 39, 'Jigglypuff', 'Normal Hada', '2024-08-21 10:08:51', 55, 5, 'images/pokemons/Jigglypuff.jpg'),
(9, 143, 'Snorlax', 'Normal', '2012-01-12 13:09:00', 4600, 1, 'images/pokemons/Snorlax.jpg'),
(10, 94, 'Gengar', 'Fantasma Veneno', '2021-12-06 03:04:13', 405, 6, 'images/pokemons/Gengar.jpg'),
(11, 2, 'Ivysaur', 'Planta Veneno', '2020-05-10 14:12:30', 130, 1, 'images/pokemons/Ivysaur.jpg'),
(12, 5, 'Charmeleon', 'Fuego', '2023-06-14 18:45:00', 190, 2, 'images/pokemons/Charmeleon.jpg'),
(13, 8, 'Wartortle', 'Agua', '2019-11-22 08:30:15', 225, 3, 'images/pokemons/Wartortle.jpg'),
(14, 10, 'Caterpie', 'Bicho', '2018-09-05 12:00:00', 29, 4, 'images/pokemons/Caterpie.jpg'),
(15, 26, 'Raichu', 'Electrico', '2024-11-17 14:31:57', 300, 1, 'images/pokemons/Raichu.jpg'),
(16, 37, 'Vulpix', 'Fuego', '2022-11-23 13:15:20', 99, 2, 'images/pokemons/Vulpix.jpg'),
(17, 63, 'Abra', 'Psiquico', '2024-11-17 14:32:16', 90, 5, 'images/pokemons/Abra.jpg'),
(18, 135, 'Jolteon', 'Electrico', '2024-11-17 14:32:22', 245, 6, 'images/pokemons/Jolteon.jpg'),
(19, 147, 'Dratini', 'Dragon', '2024-11-17 14:32:07', 330, NULL, 'images/pokemons/Dratini.jpg'),
(20, 92, 'Haunter', 'Fantasma Veneno', '2023-10-31 22:22:22', 600, 6, 'images/pokemons/Haunter.jpg'),
(21, 25, 'Pikachu', 'Electrico', '2024-11-17 14:36:43', 15, NULL, NULL),
(22, 25, 'Pikachu', 'Electrico', '2024-11-17 14:36:43', 32, NULL, NULL),
(23, 25, 'Pikachu', 'Electrico', '2024-11-17 14:36:43', 25, NULL, NULL),
(24, 4, 'Charmander', 'Fuego', '2024-11-17 14:36:43', 23, NULL, NULL),
(25, 4, 'Charmander', 'Fuego', '2024-11-17 14:36:43', 19, NULL, NULL),
(26, 94, 'Gengar', 'Fantasma Veneno', '2024-11-17 14:36:43', 37, NULL, NULL),
(27, 26, 'Raichu', 'Electrico', '2024-11-17 14:36:43', 37, NULL, NULL),
(28, 147, 'Dratini', 'Dragon', '2024-11-17 14:36:43', 37, NULL, NULL),
(29, 10, 'Caterpie', 'Bicho', '2024-11-17 14:36:43', 13, NULL, NULL),
(30, 10, 'Caterpie', 'Bicho', '2024-11-17 14:36:43', 9, NULL, NULL),
(31, 143, 'Snorlax', 'Normal', '2024-11-17 14:36:43', 9, NULL, NULL),
(32, 143, 'Snorlax', 'Normal', '2024-11-17 14:36:43', 9, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- Indices de la tabla `aprendizaje`
--
ALTER TABLE `aprendizaje`
  ADD PRIMARY KEY (`FK_id_movimiento`,`FK_id_pokemon`),
  ADD KEY `FK_id_pokemon` (`FK_id_pokemon`),
  ADD KEY `FK_id_movimiento` (`FK_id_movimiento`);

--
-- Indices de la tabla `entrenadorpokemon`
--
ALTER TABLE `entrenadorpokemon`
  ADD PRIMARY KEY (`id_entrenador`),
  ADD KEY `id_entrenador` (`id_entrenador`);

--
-- Indices de la tabla `movimiento`
--
ALTER TABLE `movimiento`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `id_movimiento` (`id_movimiento`);

--
-- Indices de la tabla `pokemon`
--
ALTER TABLE `pokemon`
  ADD PRIMARY KEY (`id`,`nro_pokedex`),
  ADD KEY `FK_id_entrenador` (`FK_id_entrenador`),
  ADD KEY `id` (`id`,`nro_pokedex`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admin_user`
--
ALTER TABLE `admin_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `entrenadorpokemon`
--
ALTER TABLE `entrenadorpokemon`
  MODIFY `id_entrenador` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `movimiento`
--
ALTER TABLE `movimiento`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `pokemon`
--
ALTER TABLE `pokemon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1011;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `aprendizaje`
--
ALTER TABLE `aprendizaje`
  ADD CONSTRAINT `aprendizaje_ibfk_1` FOREIGN KEY (`FK_id_movimiento`) REFERENCES `movimiento` (`id_movimiento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aprendizaje_ibfk_2` FOREIGN KEY (`FK_id_pokemon`) REFERENCES `pokemon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pokemon`
--
ALTER TABLE `pokemon`
  ADD CONSTRAINT `pokemon_ibfk_1` FOREIGN KEY (`FK_id_entrenador`) REFERENCES `entrenadorpokemon` (`id_entrenador`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

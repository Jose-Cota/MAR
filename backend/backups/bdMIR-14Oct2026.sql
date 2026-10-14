-- ========================================================
-- RESPALDO DE BASE DE DATOS: bdMIR
-- FECHA: 2026-10-14 17:20:26
-- SERVIDOR: 192.168.22.238:3306
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';


-- --------------------------------------------------------
-- Estructura para tabla `avances`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `avances`;
CREATE TABLE `avances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ficha_id` bigint(20) unsigned NOT NULL,
  `periodo` varchar(50) NOT NULL,
  `ejercicio` int(11) NOT NULL DEFAULT 2026,
  `valor_a` decimal(15,2) DEFAULT NULL,
  `valor_b` decimal(15,2) DEFAULT NULL,
  `valor_c` decimal(15,2) DEFAULT NULL,
  `valor_ai` decimal(15,2) DEFAULT NULL,
  `valor_bi` decimal(15,2) DEFAULT NULL,
  `resultado` decimal(15,2) DEFAULT NULL,
  `semaforo` varchar(50) DEFAULT NULL,
  `validado` tinyint(1) NOT NULL DEFAULT 0,
  `estatus` varchar(50) NOT NULL DEFAULT 'Borrador',
  `validador_id` bigint(20) unsigned DEFAULT NULL,
  `fecha_validacion` timestamp NULL DEFAULT NULL,
  `fecha_desvalidacion` timestamp NULL DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `medio_verificacion_url` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `avances_ficha_id_foreign` (`ficha_id`),
  CONSTRAINT `avances_ficha_id_foreign` FOREIGN KEY (`ficha_id`) REFERENCES `fichas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `avances`
INSERT INTO `avances` VALUES 
(1, 6, '1er. Trim', 2026, '45.00', '99.00', NULL, NULL, NULL, '0.45', 'Verde', 0, 'Captura', 1, '2026-10-14 09:05:09', '2026-10-14 09:26:37', 'dfdfdsfsdfsd', NULL, '2026-10-14 09:05:09', '2026-10-14 09:27:15'),
(2, 6, '2do. Trim', 2026, '45.00', '99.00', NULL, NULL, NULL, '0.45', 'Verde', 0, 'Captura', NULL, NULL, NULL, 'yuiyuiyui', NULL, '2026-10-14 09:06:36', '2026-10-14 09:06:36'),
(3, 9, 'Marzo', 2026, '343.00', '34.00', NULL, NULL, NULL, '1008.82', 'Verde', 1, 'Validado', 1, '2026-10-14 09:27:57', NULL, '3434333434', NULL, '2026-10-14 09:27:47', '2026-10-14 09:27:57'),
(4, 8, 'Semestre 1', 2026, '45.00', '4555.00', NULL, NULL, NULL, '0.01', 'Rojo', 1, 'Validado', 1, '2026-10-14 09:42:27', '2026-10-14 09:42:17', '454454545454545', NULL, '2026-10-14 09:41:43', '2026-10-14 09:42:27');

-- --------------------------------------------------------
-- Estructura para tabla `cache`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `cache`

-- --------------------------------------------------------
-- Estructura para tabla `cache_locks`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `cache_locks`

-- --------------------------------------------------------
-- Estructura para tabla `failed_jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `failed_jobs`

-- --------------------------------------------------------
-- Estructura para tabla `fichas`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `fichas`;
CREATE TABLE `fichas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nivel_id` bigint(20) unsigned NOT NULL,
  `ejercicio` int(11) NOT NULL DEFAULT 2026,
  `unidad_responsable_id` bigint(20) unsigned DEFAULT NULL,
  `resumen_narrativo` text DEFAULT NULL,
  `supuestos` text DEFAULT NULL,
  `medios_verificacion` text DEFAULT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `definicion` text DEFAULT NULL,
  `metodo_calculo_tipo` varchar(10) DEFAULT NULL,
  `formula` varchar(500) DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `unidad_medida` varchar(100) DEFAULT NULL,
  `frecuencia_medicion` varchar(50) DEFAULT NULL,
  `linea_base` varchar(255) DEFAULT NULL,
  `metas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metas`)),
  `sentido` varchar(50) DEFAULT NULL,
  `umbral_verde` varchar(255) DEFAULT NULL,
  `umbral_amarillo` varchar(255) DEFAULT NULL,
  `umbral_rojo` varchar(255) DEFAULT NULL,
  `estatus` varchar(50) NOT NULL DEFAULT 'Borrador',
  `fecha_validacion` timestamp NULL DEFAULT NULL,
  `fecha_desvalidacion` timestamp NULL DEFAULT NULL,
  `validador_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fichas_nivel_id_foreign` (`nivel_id`),
  CONSTRAINT `fichas_nivel_id_foreign` FOREIGN KEY (`nivel_id`) REFERENCES `niveles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `fichas`
INSERT INTO `fichas` VALUES 
(1, 1, 2026, 2, 'Apoyar en la consolidación de un sistema de justicia electoral como instrumento clave del estado de derecho, garantizando resoluciones bajo los principios constitucionales de autonomía, legalidad, independencia, progresividad y equidad que contribuyan a la celebración de elecciones libres, justas y auténticas.', 'Es requisito que el sistema judicial electoral cuente con una percepción ante la sociedad de un funcionamiento eficaz, y que muestre características de independencia e imparcialidad para promover la justicia, transparencia, accesibilidad, inclusividad e igualdad.', 'Resumen de los asuntos recibidos y resueltos por año (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Evaluar el tiempo promedio de la emisión de resoluciones por parte del Tribunal Electoral de la Ciudad de México a partir de que son recibidos los asuntos, el cual debe ser menor al establecido conforme al Código de Instituciones y Procedimientos Electorales de la Ciudad de México.', NULL, '% atención oportuna = ((a^i+b^i) / (a+b-c))*100', NULL, 'Porcentaje.', 'Anual', 'No disponible, ya que el ejercicio 2025 será el primero en ser evaluado.', '\"El porcentaje de atenci\\u00f3n oportuna se mantenga o incremente.\"', 'Ascendente', 'Mayor o igual a 90%', 'Mayor a 70% y menor a 90%', 'Menor o igual a 70%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 17:19:01'),
(2, 2, 2026, 2, 'Garantizar el pleno ejercicio de los derechos y el acceso efectivo, igualitario y participativo de la ciudadanía mediante la emisión de resoluciones en materia electoral sustentadas, justas y pacíficas.', 'Los actores judiciales respetan y cumplen las decisiones del TECDMX.', 'Concentrado de las resoluciones impugnadas por año y las sentencias que al respecto haya emitido el Tribunal Electoral del Poder Judicial de la Federación (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Identificar el porcentaje de las resoluciones que emite el TECDMX, y que son impugnadas por los diversos actores, las cuales fueron ratificadas por el Tribunal Electoral del Poder Judicial de la Federación.', NULL, '% resoluciones impugnadas y ratificadas = ((a^i+b^i) / (a+b-c))*100', NULL, 'Porcentaje.', 'Anual', 'No disponible, ya que el ejercicio 2025 será el primero en ser evaluado.', '\"El porcentaje de sentencias impugnadas que son ratificadas se mantenga o incremente en el transcurso del tiempo.\"', 'Ascendente', 'Mayor o igual a 80%', 'Mayor a 60% y menor a 80%', 'Menor o igual a 60%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 17:19:01'),
(3, 3, 2026, 2, 'Impartir justicia electoral sustentada en la congruencia, neutralidad, imparcialidad, y resoluciones con lenguaje ciudadano e incluyente.', 'Los asuntos en materia electoral y de los procedimientos de participación ciudadana son resueltos considerando los principios de congruencia, neutralidad, imparcialidad, asimismo, las sentencias son estructuradas con lenguaje ciudadano e incluyente.', 'Relación de las resoluciones emitidas, respecto de las impugnaciones recibidas por el TECDMX en materia electoral y de los procedimientos de participación ciudadana (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Permite conocer el porcentaje de las resoluciones emitidas, respecto de las impugnaciones recibidas por el TECDMX en materia electoral y de los procedimientos de participación ciudadana.', NULL, '% resoluciones emitidas = ((a^i+b^i) / (a+b-c))*100', NULL, 'Porcentaje.', 'Semestral', 'No disponible, ya que el ejercicio 2025 será el primero en ser evaluado.', '\"El porcentaje de impugnaciones atendidas se mantenga o aumente en el transcurso del tiempo.\"', 'Ascendente', 'Mayor o igual a 90%', 'Menor a 90% y mayor o igual a 80%', 'Menor a 80%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 17:19:01'),
(4, 4, 2026, 18, 'Acceso a la justicia electoral incluyente, con base en la igualdad, no discriminación, libre de violencia de género y que garantice la protección para grupos vulnerables.', 'Las acciones de igualdad e inclusión garantizan la justicia en un plano de igualdad, no discriminación, libre de violencia de género y con protección para los grupos vulnerables.', 'Listado de las solicitudes atendidas por la Defensoría Pública de Participación Ciudadana y de Procesos Democráticos, con relación a aquéllas que recibió (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Permite identificar la proporción de las solicitudes atendidas por la Defensoría Pública de Participación Ciudadana y de Procesos Democráticos, con relación a aquéllas que recibió.', NULL, '% atención de solicitudes recibidas = (a / b)*100', NULL, 'Porcentaje.', 'Semestral', 'No disponible, ya que el ejercicio 2025 será el primero en ser evaluado.', '\"El porcentaje de las solicitudes atendidas se mantenga o incremente a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a 90%', 'Menor a 90% y mayor o igual a 80%', 'Menor a 80%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 17:19:48'),
(5, 5, 2026, NULL, 'Fortalecimiento y la consolidación de la imagen institucional de este Tribunal Electoral ante la ciudadanía en general, mediante la difusión de información relevante sobre sus acciones y logros alcanzados.', 'Acciones para fortalecer la imagen institucional a través del diseño, diagramación y elaboración de materiales impresos y/o digitales dirigidos a los habitantes de la Ciudad de México, así como a las personas servidoras públicas del TECDMX.', 'Resumen de las actividades de difusión realizadas, respecto de las programadas relativas a la promoción de la imagen institucional y la cultura democrática (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Mide las actividades de difusión realizadas, respecto de las programadas relativas a la promoción de la imagen institucional y la cultura democrática.', NULL, '% actividades de difusión realizadas = (a / b)*100', NULL, 'Porcentaje.', 'Semestral', 'No disponible, ya que el ejercicio 2025 será el primero en ser evaluado.', '\"El porcentaje de las acciones de difusi\\u00f3n efectuadas se mantenga o incremente a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a 95%', 'Mayor o igual a 80% y menor a 95%', 'Menor a 80%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 16:12:08'),
(6, 6, 2026, 2, 'El Tribunal Electoral atiende de manera expedita los asuntos que recibe de la población en general, organizaciones y órganos de representación ciudadana correspondientes a la Ciudad de México.', 'La sociedad espera que además de la aplicación de las normas jurídicas, las tesis de jurisprudencia y relevantes para sustentar las resoluciones que emita el TECDMX, éstas sean expeditas y emitidas en plazos menores a los establecidos.', 'Integración con los días que transcurrieron desde que Oficialía de Partes recibió los asunto en ventanilla y lo turna a la instancia atinente para su atención (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Mide los días que transcurren desde que Oficialía de Partes recibe el asunto en ventanilla y lo turna a la instancia atinente para su atención.', NULL, 'Promedio de días = a / b', '{\"a\":\"45\",\"b\":\"99\",\"resultado_calculo\":\"0.45\"}', 'Días.', 'Trimestral', 'No disponible, ya que el ejercicio 2026 será el primero en ser evaluado.', '\"Los d\\u00edas que transcurren entre que Oficial\\u00eda de Partes recibe el asunto hasta que lo turna a la instancia competente, se mantenga o disminuya a lo largo del tiempo.\"', 'Descendente', 'Menor o igual a 1 día', 'Mayor a 1 día pero menor o igual a 2 días', 'Mayor a 2 días', 'Captura', '2026-10-14 09:05:09', '2026-10-14 09:26:37', 1, '2026-09-21 17:35:10', '2026-10-14 17:19:01'),
(7, 7, 2026, 2, 'Contar con un acervo documental ordenado y correctamente clasificado que permita dar certeza de las actuaciones llevadas a cabo.', 'Las áreas jurisdiccionales cumplan con diligencia contar con expedientes que contengan todas las documentales que sustentan las actuaciones y resoluciones tomadas.', 'Relación de expedientes que fueron integrados en su totalidad, una vez concluido el proceso correspondiente (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Evalúa la proporción de expedientes que son integrados en su totalidad, una vez concluido el proceso correspondiente.', NULL, '% de integración de expedientes de asuntos concluidos = (a / b)*100', NULL, 'Porcentaje.', 'Trimestral', 'No disponible, ya que el ejercicio 2026 será el primero en ser evaluado.', '\"Se mantenga o incremente el porcentaje de expedientes integrados dentro del plazo establecido, una vez concluido el proceso jurisdiccional.\"', 'Ascendente', 'Mayor o igual a 95%', 'Mayor o igual a 80% y menor a 95%', 'Menor a 80%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 17:19:01'),
(8, 8, 2026, NULL, 'Consolidar la imagen institucional de este Tribunal Electoral ante la ciudadanía en general, como un órgano profesional, especializado, independiente y ético.', 'Contar con personal altamente especializado y actualizado en la materia electoral, que requiere el Tribunal, a efecto de garantizar un estado democrático y de derecho.', 'Concentrado de las horas promedio de capacitación del personal adscrito a las Ponencias, la Unidad Especializada de Procedimientos Sancionadores (UEPS) y la Defensoría Pública de Participación Ciudadana de Procesos Democráticos (DPE) (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Mide las horas promedio de capacitación del personal adscrito a las Ponencias, la Unidad Especializada de Procedimientos Sancionadores (UEPS) y la Defensoría Pública de Participación Ciudadana de Procesos Democráticos (DPE).', NULL, 'Horas promedio de capacitación = a / b', '{\"a\":\"45\",\"b\":\"4555\",\"resultado_calculo\":\"0.01\"}', 'Horas.', 'Semestral', 'No disponible, ya que el ejercicio 2026 será el primero en ser evaluado.', '\"Las horas de capacitaci\\u00f3n se mantengan o incrementen a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a X horas', 'Menor a X horas pero mayor o igual a Y días', 'Menor a Y horas', 'Validado', '2026-10-14 09:42:27', '2026-10-14 09:42:17', 1, '2026-09-21 17:35:10', '2026-10-14 16:11:24'),
(9, 9, 2026, 2, 'Propiciar una justicia electoral oportuna, al realizar las notificaciones a las partes interesadas dentro de los plazos establecidos legalmente.', 'Se de un seguimiento oportuno al proceso jurisdiccional, elaborando en tiempo y forma las notificaciones y contando con una logística para su entrega oportuna.', 'Integración de las notificaciones referentes a las actividades jurisdiccionales identificando los plazos en que se realizaron (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Permite identificar que las notificaciones referentes a las actividades jurisdiccionales se realicen en los plazos establecidos en la norma.', NULL, '% de notificaciones practicadas = (a / b)*100', '{\"a\":\"343\",\"b\":\"34\",\"resultado_calculo\":\"1008.82\"}', 'Porcentaje.', 'Mensual', 'No disponible, ya que el ejercicio 2026 será el primero en ser evaluado.', '\"El porcentaje de las notificaciones realizadas dentro de los plazos establecidos legalmente se mantenga o incremente a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a 98%', 'Menor a 98% y mayor o igual a 95%', 'Menor a 95%', 'Validado', '2026-10-14 09:27:57', NULL, 1, '2026-09-21 17:35:10', '2026-10-14 17:19:01'),
(10, 10, 2026, 18, 'Tribunal Electoral cercano a la sociedad en general, ya que difunde las actividades que realiza para la defensa de los derechos de participación ciudadana a través de la Defensoría Pública de Participación Ciudadana y de Procesos Democráticos.', 'Los habitantes, ciudadanía, organizaciones y órganos de representación ciudadana electos en las colonias, barrios o pueblos originarios de la Ciudad de México tienen interés en conocer sobre los derechos político-electorales que puede ejercer.', 'Relación de las actividades de difusión realizadas por la Defensoría Pública de Participación Ciudadana y de Procesos Democráticos (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Mide las actividades de difusión realizadas por la Defensoría Pública de Participación Ciudadana y de Procesos Democráticos respecto de las programadas, relativas a la promoción de la defensa y cumplimiento de los derechos reconocidos en la Constitución Local y en el Código de Instituciones y Procedimientos Electorales, así como la realización de prácticas efectivas de participación ciudadana.', NULL, '% de actividades de difusión realizadas = (a / b)*100', NULL, 'Porcentaje.', 'Trimestral', 'No disponible, ya que el ejercicio 2025 será el primero en ser evaluado.', '\"El porcentaje de las acciones de difusi\\u00f3n en materia de derechos pol\\u00edtico-electorales de la ciudadan\\u00eda y organizaciones realizadas se mantenga o incremente respecto de las programadas a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a 95%', 'Mayor o igual a 80% y menor a 95%', 'Menor a 80%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 17:19:48'),
(11, 11, 2026, 18, 'El TECDMX a través de la Defensoría Pública de Participación Ciudadana y de Procesos Democráticos pone a disposición de la población de la Ciudad de México medios electrónicos eficientes para brindar servicios de asesoría y defensa solicitados por la ciudadanía.', 'Se cuenta con herramientas tecnológicas que contribuyan a facilitar la accesibilidad a los habitantes, ciudadanía, organizaciones y órganos de representación ciudadana electos en las colonias, barrios o pueblos originarios, así como la población vulnerable de la Ciudad de México TECDMX.', 'Resumen con el detalle de las herramientas tecnológicas que la Defensoría Pública de Participación Ciudadana y de Procesos Democráticos puso a disposición de la ciudadanía y organizaciones para que tengan un acceso oportuno y expedito en la defensa de sus derechos político-electorales (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficiencia', 'Mide el uso de las herramientas tecnológicas que la Defensoría Pública de Participación Ciudadana y de Procesos Democráticos pone a disposición de los habitantes, ciudadanía, organizaciones y órganos de representación ciudadana electos, así como la población vulnerable de la Ciudad de México para que tengan un acceso oportuno y expedito en la defensa de sus derechos político-electorales.', NULL, '% de asuntos atendidos mediante el uso de las herramientas tecnológicas = (a / b)*100', NULL, 'Porcentaje.', 'Trimestral', 'No disponible, ya que el ejercicio 2026 será el primero en ser evaluado.', '\"El porcentaje en el uso de las herramientas tecnol\\u00f3gicas por parte de la ciudadan\\u00eda y organizaciones se mantenga o incremente a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a 70%', 'Mayor o igual a 60% y menor a 70%', 'Menor a 60%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 17:19:48'),
(12, 12, 2026, NULL, 'Prevenir y atender las situaciones de violencia política de género, la cual atenta contra el libre ejercicio de los derechos político-electorales de los sectores más vulnerables, mediante la impartición de cursos, talleres, conferencias, etc.', 'La sociedad y sobre todo los grupos vulnerables demandan no sólo que se respeten los derechos político-electorales, sino también conocer el ámbito legal respectivo, pero bajo un lenguaje ciudadano y sin tecnicismos que les permita un mejor entendimiento.', 'Relaciones de cursos, talleres y conferencias, señalando los programados y los impartidos, ambos del periodo a medir (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficiencia', 'Cuantifica los cursos, talleres y conferencias impartidos por el TECDMX al público en general en materia de derechos político-electorales de los sectores más vulnerables, realizados en el periodo respecto de las programadas.', NULL, '% de cursos, talleres y conferencias = (a / b)*100', NULL, 'Cursos, talleres y conferencias.', 'Trimestral', 'No disponible, ya que el ejercicio 2026 será el primero en ser evaluado.', '\"La cantidad de cursos, talleres y conferencias impartidos respecto de los programados se mantenga o incremente a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a 95%', 'Mayor o igual a 90% y menor a 95%', 'Menor a 90%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 16:12:31'),
(13, 13, 2026, NULL, 'Difusión en vivo de las sesiones públicas fortalece la confianza de la sociedad en el Tribunal Electoral.', 'Los habitantes de la Ciudad de México tienen interés en conocer los argumentos presentados por las Magistraturas integrantes del Pleno que derivan en las decisiones y resoluciones emitidas por el Tribunal Electoral.', 'Relación de las sesiones públicas del Pleno de este Órgano Jurisdiccional que fueron transmitidas en vivo, señalando las plataformas utilizadas, para cada uno de los casos (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Mide las sesiones públicas que son transmitidas en vivo en las distintas plataformas que permitan a la sociedad conocer las decisiones tomadas por el Pleno de este Órgano Jurisdiccional.', NULL, 'Paso 1) = (a/b)*100, Paso 2) = (c/d)*100', NULL, 'Porcentaje.', 'Trimestral', 'No disponible, ya que el ejercicio 2026 será el primero en ser evaluado.', '\"El porcentaje de las transmisiones en vivo de las sesiones p\\u00fablicas se mantenga o incremente a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a 98%', 'Mayor o igual a 95% y menor a 98%', 'Menor a 95%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 16:13:21'),
(14, 14, 2026, NULL, 'Tribunal Electoral identifica y atiende las demandas de la sociedad en materia electoral y facilita su acceso para su consulta.', 'La ciudadanía, organizaciones y órganos de representación, así como los sectores más vulnerables tienen interés en conocer sobre las sentencias emitidas por el TECDMX.', 'Contador de visitas en la página del TECDMX para consultar las resoluciones y sentencias emitidas en el periodo; así como, el número de resoluciones y sentencias emitidas en el periodo (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Cuantifica las visitas realizadas en el buscador del TECDMX para consultar las resoluciones y sentencias emitidas en el periodo.', NULL, 'Promedio de visitas = a / b', NULL, 'Promedio de visitas por resolución o sentencia.', 'Trimestral', 'No disponible, ya que el ejercicio 2026 será el primero en ser evaluado.', '\"El promedio de visitas efectuadas en el buscador de las resoluciones y sentencias emitidas en el periodo, se mantenga o incremente a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a 10', 'Mayor o igual a 6 y menor a 10', 'Menor a 6', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 16:12:50'),
(15, 15, 2026, NULL, 'Hacer accesible al público interesado la consulta de las tesis de jurisprudencia y relevantes emitidas por el TECDMX.', 'La sociedad en general cada vez está más interesada en involucrarse en el tema electoral, ya que es el medio para elegir a sus gobernantes y representantes lo cual afecta su esfera como persona gobernada. Contar con un acceso ágil, así como, mantener actualizadas las tesis de jurisprudencia y relevantes fomenta el interés de la ciudadanía, organizaciones y órganos de representación ciudadana para consultarlas.', 'Contador de las consultas de tesis de jurisprudencia y relevantes efectuadas (https://www.tecdmx.org.mx/index.php/MIR)', 'Eficacia', 'Mide las consultas realizadas de tesis de jurisprudencia y relevantes en el periodo y permite monitorear que el porcentaje sea igual o mayor al periodo anterior.', NULL, '% de consulta = (a/b - 1)*100', NULL, 'Porcentaje.', 'Trimestral', 'No disponible, ya que el ejercicio 2026 será el primero en ser evaluado.', '\"El porcentaje de las consultas de tesis de jurisprudencia y relevantes efectuadas se mantenga o incremente a lo largo del tiempo.\"', 'Ascendente', 'Mayor o igual a 1%', 'Mayor o igual a 0.0% y menor a 1%', 'Menor a 0.0%', 'Captura', NULL, NULL, NULL, '2026-09-21 17:35:10', '2026-10-14 16:13:30');

-- --------------------------------------------------------
-- Estructura para tabla `job_batches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `job_batches`

-- --------------------------------------------------------
-- Estructura para tabla `jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `jobs`

-- --------------------------------------------------------
-- Estructura para tabla `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `migrations`
INSERT INTO `migrations` VALUES 
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_17_000003_create_niveles_table', 1),
(5, '2026_09_17_000004_create_fichas_table', 1),
(6, '2026_09_17_000005_create_avances_table', 1),
(7, '2026_09_21_163940_create_personal_access_tokens_table', 1),
(8, '2026_10_13_000001_add_role_to_users_table', 1),
(9, '2026_10_13_000002_add_profile_fields_to_users_table', 1),
(10, '2026_10_14_000001_create_unidades_responsables_table', 1),
(11, '2026_10_14_000002_add_validado_to_avances_table', 1),
(12, '2026_10_14_000003_add_fechas_estatus_to_fichas_and_avances', 1),
(13, '2026_10_14_000004_add_ejercicio_to_fichas_and_avances', 1),
(14, '2026_10_14_000005_add_roles_to_users_table', 1);

-- --------------------------------------------------------
-- Estructura para tabla `niveles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `niveles`;
CREATE TABLE `niveles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(10) NOT NULL,
  `nivel` varchar(50) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `frecuencia` varchar(50) NOT NULL,
  `unidad_responsable_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `niveles_clave_unique` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `niveles`
INSERT INTO `niveles` VALUES 
(1, 'FIN', 'Fin', 'Resoluciones emitidas oportunamente que garantizan la impartición de justicia', 'Estratégico', 'Anual', 2, '2026-09-21 16:40:03', '2026-10-14 17:19:01'),
(2, 'PROP', 'Propósito', 'Resoluciones impugnadas pero ratificadas por el Tribunal Electoral del Poder Judicial de la Federación', 'Estratégico', 'Anual', 2, '2026-09-21 16:40:03', '2026-10-14 17:19:01'),
(3, 'C1', 'Componente 1', 'Resoluciones emitidas respecto de impugnaciones recibidas en materia electoral y de los procedimientos de participación ciudadana de la CDMX', 'Gestión', 'Semestral', 2, '2026-09-21 16:40:03', '2026-10-14 17:19:01'),
(4, 'C2', 'Componente 2', 'Atención de las solicitudes recibidas por la Defensoría Pública de Participación Ciudadana y de Procesos Democráticos', 'Gestión', 'Semestral', 18, '2026-09-21 16:40:03', '2026-10-14 17:19:48'),
(5, 'C3', 'Componente 3', 'Cultura democrática difundida', 'Gestión', 'Semestral', NULL, '2026-09-21 16:40:03', '2026-10-14 16:12:08'),
(6, 'A1.1', 'Actividad 1.1', 'Tiempo para turnar los asuntos recibidos por la Oficialía de Partes', 'Gestión', 'Trimestral', 2, '2026-09-21 16:40:03', '2026-10-14 17:19:01'),
(7, 'A1.2', 'Actividad 1.2', 'Integración de expedientes completos y adecuada clasificación', 'Gestión', 'Trimestral', 2, '2026-09-21 16:40:03', '2026-10-14 17:19:01'),
(8, 'A1.3', 'Actividad 1.3', 'Capacitación continua y permanente del personal que realiza actividades jurisdiccionales', 'Gestión', 'Semestral', NULL, '2026-09-21 16:40:03', '2026-10-14 16:11:24'),
(9, 'A1.4', 'Actividad 1.4', 'Notificaciones realizadas en tiempo y forma', 'Gestión', 'Mensual', 2, '2026-09-21 16:40:03', '2026-10-14 17:19:01'),
(10, 'A2.1', 'Actividad 2.1', 'Actividades para difundir el derecho a la Participación Ciudadana', 'Gestión', 'Trimestral', 18, '2026-09-21 16:40:03', '2026-10-14 17:19:48'),
(11, 'A2.2', 'Actividad 2.2', 'Accesibilidad tecnológica que facilite el acercamiento de los servicios de asesoría y defensa solicitados por la ciudadanía', 'Gestión', 'Trimestral', 18, '2026-09-21 16:40:03', '2026-10-14 17:19:48'),
(12, 'A2.3', 'Actividad 2.3', 'Prevención a la violencia política de género', 'Gestión', 'Trimestral', NULL, '2026-09-21 16:40:03', '2026-10-14 16:12:31'),
(13, 'A3.1', 'Actividad 3.1', 'Transmisión en vivo de sesiones públicas', 'Gestión', 'Trimestral', NULL, '2026-09-21 16:40:03', '2026-10-14 16:13:21'),
(14, 'A3.2', 'Actividad 3.2', 'Visitas al buscador para consultar las sentencias y resoluciones emitidas', 'Gestión', 'Trimestral', NULL, '2026-09-21 16:40:03', '2026-10-14 16:12:50'),
(15, 'A3.3', 'Actividad 3.3', 'Consulta de tesis de jurisprudencia y relevantes', 'Gestión', 'Trimestral', NULL, '2026-09-21 16:40:03', '2026-10-14 16:13:30');

-- --------------------------------------------------------
-- Estructura para tabla `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `password_reset_tokens`

-- --------------------------------------------------------
-- Estructura para tabla `personal_access_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `personal_access_tokens`
INSERT INTO `personal_access_tokens` VALUES 
(1, 'App\\Models\\User', 1, 'api_token', '2ade6c96c72d0f603362481707033d39f4a0a114cd0d964f45c0ed5bacbcb790', '[\"*\"]', '2026-10-14 09:41:43', NULL, '2026-10-14 09:41:14', '2026-10-14 09:41:43'),
(2, 'App\\Models\\User', 1, 'api_token', '9346f4e4b8e3ff81227abe9b6ddf212d753e7ad291aa42e389f18c1f8a5d532d', '[\"*\"]', '2026-10-14 16:51:38', NULL, '2026-10-14 09:48:29', '2026-10-14 16:51:38'),
(3, 'App\\Models\\User', 4, 'api_token', '5967fce6bcb70ad338e0f204a59b3e23c31810be78fa3c688b8f5bf165224797', '[\"*\"]', NULL, NULL, '2026-10-14 16:09:22', '2026-10-14 16:09:22');

-- --------------------------------------------------------
-- Estructura para tabla `sessions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `sessions`

-- --------------------------------------------------------
-- Estructura para tabla `unidades_responsables`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `unidades_responsables`;
CREATE TABLE `unidades_responsables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `unidades_responsables`
INSERT INTO `unidades_responsables` VALUES 
(1, '01', 'Presidencia', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(2, '02', 'Secretaría General', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(3, '03', 'Secretaría Administrativa', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(4, '04', 'Dirección de Planeación y Recursos Financieros', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(5, '05', 'Dirección de Recursos Humanos', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(6, '06', 'Dirección de Recursos Materiales y Servicios Generales', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(7, '07', 'Contraloría Interna', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(8, '08', 'Dirección General Jurídica', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(9, '09', 'Coordinación de Comunicación Social y Relaciones Públicas', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(10, '10', 'Coordinación de Transparencia y Datos Personales', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(11, '11', 'Instituto de Formación y Capacitación', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(12, '12', 'Comisión de Controversias Laborales y Administrativas', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(13, '13', 'Unidad de Servicios Informáticos', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(14, '14', 'Unidad de Estadística y Jurisprudencia', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(15, '15', 'Coordinación de Difusión y Publicación', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(16, '16', 'Coordinación de Archivo', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(17, '17', 'Coordinación de Derechos Humanos y Género', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(18, '18', 'Defensoría Pública de Participación Ciudadana y de Procesos Democráticos', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(19, '19', 'Coordinación de Vinculación y Relaciones Internacionales', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(20, '20', 'Unidad Especializada de Procedimientos Sancionadores', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(21, '01-01', 'Ponencia del Magistrado Armando Ámbriz Hernández', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(22, '01-02', 'Ponencia del Magistrado José Jesús Hernández Rodríguez', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(23, '01-03', 'Ponencia del Magistrado Osiris Vázquez Rangel', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(24, '01-04', 'Ponencia de la Magistrada Karina Salgado Lunar', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(25, '01-05', 'Ponencia de la Magistrada Laura Patricia Jiménez Castillo', '2026-10-14 09:24:53', '2026-10-14 09:24:53'),
(26, '00', 'Pleno', '2026-10-14 09:24:53', '2026-10-14 09:24:53');

-- --------------------------------------------------------
-- Estructura para tabla `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'Administrador',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activo` int(11) NOT NULL DEFAULT 1,
  `nombre` varchar(255) DEFAULT NULL,
  `apellido_paterno` varchar(255) DEFAULT NULL,
  `apellido_materno` varchar(255) DEFAULT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `area_id` int(11) NOT NULL DEFAULT 1,
  `area_numero` varchar(255) NOT NULL DEFAULT '01',
  `area_nombre` varchar(255) NOT NULL DEFAULT 'Presidencia',
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`roles`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcado de datos para la tabla `users`
INSERT INTO `users` VALUES 
(1, 'Administrador', 'admin', 'Administrador', '2026-09-21 16:40:02', '$2y$12$Fk1WYHXT.uPAQZJ5CoC2zeOWvI1B/Yp/nC6/bctBOl7chFyEadcUW', 'KT2hNpBBwV', '2026-09-21 16:40:03', '2026-09-21 16:40:03', 1, 'Administrador', '', '', 'admin@tecdmx.org.mx', 1, '01', 'Presidencia', '[\"Administrador\"]'),
(2, 'Jose Cota', 'jose.cota', 'Administrador', NULL, '$2y$12$flo./r0O/wi/nHxpGL7GU.DJgjMW79apGaQymKW88KPYuFlO4CScm', NULL, '2026-09-21 16:43:43', '2026-09-21 16:44:53', 1, 'Jose', 'Cota', '', 'jose.cota@tecdmx.org.mx', 1, '01', 'Presidencia', '[\"Administrador\"]'),
(3, 'Fernando Cortés', 'fernando.cortes', 'Validador', '2026-10-13 17:13:23', '$2y$12$jaHFw7jlF79JXuqAw7UhX.pxgsx2p.o6KClmpwqfbirMtOJXVW.0u', NULL, '2026-10-13 17:13:23', '2026-10-14 09:04:35', 1, 'Fernando', 'Cortés', '', 'fernando.cortes@tecdmx.org.mx', 7, '07', 'Secretaría Administrativa', '[\"Validador\",\"Capturador\"]'),
(4, 'Gilberto Gómez', 'gilberto.gomez', 'Administrador', '2026-10-13 17:13:23', '$2y$12$wGFy03ZqWA1kUOWrICa2.e9/tksvU5ofM0QrWGIuW9BMq..dP5qLC', NULL, '2026-10-13 17:13:23', '2026-10-13 23:44:50', 1, 'Gilberto', 'Gómez', '', 'gilberto.gomez@tecdmx.org.mx', 1, '01', 'Presidencia', '[\"Administrador\"]');

SET FOREIGN_KEY_CHECKS = 1;

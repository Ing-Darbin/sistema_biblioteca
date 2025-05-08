-- Script de Inserción de Datos para la Tabla 'categorias'
--
-- Este script SQL se utiliza para insertar un conjunto inicial de categorías
-- en la tabla `categorias` de la base de datos `sistema_biblioteca`.
-- Cada categoría tiene un único campo `nombre`.
-- Estas categorías se utilizarán para clasificar los libros en el sistema.

INSERT INTO categorias (nombre) VALUES
('Ficción'),
('No Ficción'),
('Ciencia'),
('Historia'),
('Literatura'),
('Tecnología');

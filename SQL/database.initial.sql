CREATE DATABASE IF NOT EXISTS `sistemanfse`
USE `sistemanfse`;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `cpf_cnpj` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `pasta` varchar(255) NOT NULL,
  `codigo_verificacao` varchar(10) DEFAULT NULL,
  `codigo_expiracao` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `perfil` enum('admin','user') DEFAULT 'user',
  `is_admin` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf_cnpj` (`cpf_cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id`, `nome`, `cpf_cnpj`, `email`, `senha`, `pasta`, `codigo_verificacao`, `codigo_expiracao`, `created_at`, `perfil`, `is_admin`) VALUES
	(1, 'Administrador', '00000000000000', 'admin@seudominio.com.br', '$2a$12$xTdoDhLNo4RrGTD.y/NfZu/Vn6kiFEdQaWcsi64or76mTi2drNo2O', 'admin', NULL, NULL, '2026-06-22 22:40:00', 'admin', 1);

CREATE TABLE IF NOT EXISTS contadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_email_usuario (email, usuario_id)
);

ALTER TABLE usuarios ADD COLUMN ativo TINYINT(1) DEFAULT 1;

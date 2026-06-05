-- Banco de Dados: Portal Imobiliário
-- Compatível com XML Open Classifieds e portais imobiliários

CREATE DATABASE IF NOT EXISTS portal_imobiliario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_imobiliario;

-- 1. Tabela de Usuários (Administradores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tipos de Propriedade (para compatibilidade XML)
CREATE TABLE IF NOT EXISTS tipos_propriedade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tipo_xml INT,
    nome_tipo VARCHAR(100) NOT NULL,
    id_subtipo_xml INT,
    nome_subtipo VARCHAR(100),
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir tipos padrão
INSERT INTO tipos_propriedade (id_tipo_xml, nome_tipo, id_subtipo_xml, nome_subtipo, slug) VALUES
(1, 'Apartamento', 1, 'Apartamento Padrão', 'apartamento'),
(2, 'Casa', 2, 'Casa Padrão', 'casa'),
(3, 'Cobertura', 3, 'Cobertura', 'cobertura'),
(4, 'Terreno', 4, 'Terreno', 'terreno'),
(5, 'Comercial', 5, 'Sala Comercial', 'comercial'),
(6, 'Galpão', 6, 'Galpão', 'galpao'),
(7, 'Fazenda', 7, 'Fazenda', 'fazenda'),
(8, 'Chácara', 8, 'Chácara', 'chacara');

-- 3. Tabela Principal de Imóveis
CREATE TABLE IF NOT EXISTS imoveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_anuncio VARCHAR(100) NOT NULL UNIQUE,
    codigo_referencia VARCHAR(100),
    titulo VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    descricao TEXT NOT NULL,
    tipo_propriedade_id INT NOT NULL,
    operacao ENUM('Venda','Aluguel','Temporada') NOT NULL,
    valor DECIMAL(15,2) NOT NULL,
    moeda VARCHAR(3) DEFAULT 'BRL',
    endereco VARCHAR(255) NOT NULL,
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    cep VARCHAR(10),
    id_localidade_xml VARCHAR(20),
    localidade_xml VARCHAR(255),
    latitud DECIMAL(10,8),
    longitud DECIMAL(11,8),
    area_util DECIMAL(10,2),
    area_total DECIMAL(10,2),
    quartos INT,
    suites INT,
    banheiros INT,
    garagens INT,
    condominio DECIMAL(15,2),
    iptu DECIMAL(15,2),
    destaque TINYINT(1) DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    data_modificacao_xml BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_propriedade_id) REFERENCES tipos_propriedade(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Características (Comodidades)
CREATE TABLE IF NOT EXISTS imoveis_caracteristicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icone VARCHAR(100),
    nome_xml VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir características padrão
INSERT INTO imoveis_caracteristicas (nome, slug, icone, nome_xml) VALUES
('Piscina', 'piscina', '🏊', 'Piscina'),
('Churrasqueira', 'churrasqueira', '🔥', 'Churrasqueira'),
('Academia', 'academia', '🏋️', 'Academia'),
('Condomínio Fechado', 'condominio-fechado', '🔒', 'CondomínioFechado'),
('Mobiliado', 'mobiliado', '🛋️', 'Mobiliado'),
('Frente Mar', 'frente-mar', '🌊', 'FrenteMar'),
('Aceita Pet', 'aceita-pet', '🐶', 'AceitaPet'),
('Elevador', 'elevador', '🛗', 'Elevador'),
('Segurança 24h', 'seguranca-24h', '👮', 'Seguranca24h'),
('Jardim', 'jardim', '🌳', 'Jardim');

-- 5. Relacionamento Imóvel ↔ Características
CREATE TABLE IF NOT EXISTS imovel_caracteristica (
    imovel_id INT NOT NULL,
    caracteristica_id INT NOT NULL,
    PRIMARY KEY (imovel_id, caracteristica_id),
    FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE,
    FOREIGN KEY (caracteristica_id) REFERENCES imoveis_caracteristicas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Fotos dos Imóveis
CREATE TABLE IF NOT EXISTS imoveis_fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imovel_id INT NOT NULL,
    arquivo VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    principal TINYINT(1) DEFAULT 0,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Configurações do Site
CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações padrão
INSERT INTO configuracoes (chave, valor) VALUES
('nome_empresa', 'Minha Imobiliária'),
('email_contato', 'contato@minhaimobiliaria.com.br'),
('telefone', '(11) 99999-9999'),
('whatsapp', '5511999999999'),
('endereco', 'Rua Exemplo, 123 - São Paulo/SP'),
('script_head', ''),
('script_body_top', ''),
('script_body_bottom', '');

-- 8. Leads (Contatos Recebidos)
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imovel_id INT,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mensagem TEXT,
    origem VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Páginas do Site
CREATE TABLE IF NOT EXISTS paginas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    conteudo TEXT NOT NULL,
    meta_title VARCHAR(200),
    meta_description VARCHAR(300),
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir páginas padrão
INSERT INTO paginas (titulo, slug, conteudo, meta_title, meta_description) VALUES
('Sobre Nós', 'sobre', '<h1>Sobre Nós</h1><p>Conheça nossa imobiliária.</p>', 'Sobre Nós - Minha Imobiliária', 'Saiba mais sobre nossa história e equipe.'),
('Contato', 'contato', '<h1>Contato</h1><p>Entre em contato conosco.</p>', 'Contato - Minha Imobiliária', 'Entre em contato com nossa equipe.');

-- 10. Inserir usuário admin padrão (senha: admin123 - ALTERE ISTO APÓS INSTALAÇÃO!)
INSERT INTO usuarios (nome, email, senha) VALUES
('Administrador', 'admin@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

create database db_mpd;
use db_mpd;

CREATE TABLE funcionarios (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cargo VARCHAR(100),
    departamento VARCHAR(100),
    horario_padrao TIME DEFAULT '09:00:00',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pontos (
    id_ponto INT AUTO_INCREMENT PRIMARY KEY,
    id_funcionario INT NOT NULL,

    data DATE NOT NULL,
    hora_entrada TIME,
    hora_saida TIME,

    total_horas DECIMAL(5,2) DEFAULT NULL,

    status ENUM('completo', 'atraso', 'em andamento', 'ausente') 
        DEFAULT 'em andamento',

    justificativa VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_funcionario)
        REFERENCES funcionarios(id_funcionario)
        ON DELETE CASCADE
);

CREATE TABLE banco_horas (
    id_banco INT AUTO_INCREMENT PRIMARY KEY,
    id_funcionario INT NOT NULL,

    mes VARCHAR(7) NOT NULL,
    saldo_total DECIMAL(6,2) DEFAULT 0,
    saldo_mes DECIMAL(6,2) DEFAULT 0,

    horas_extras_mes DECIMAL(6,2) DEFAULT 0,
    horas_debito_mes DECIMAL(6,2) DEFAULT 0,

    data_atualizacao DATE,

    status ENUM('positivo', 'negativo', 'neutro') DEFAULT 'neutro',

    FOREIGN KEY (id_funcionario)
        REFERENCES funcionarios(id_funcionario)
        ON DELETE CASCADE
);

CREATE TABLE banco_horas_movimentacao (
    id_mov INT AUTO_INCREMENT PRIMARY KEY,
    id_funcionario INT NOT NULL,

    data DATE NOT NULL,

    tipo ENUM('extra', 'debito') NOT NULL,

    horas DECIMAL(5,2) NOT NULL,

    descricao VARCHAR(255),

    FOREIGN KEY (id_funcionario)
        REFERENCES funcionarios(id_funcionario)
        ON DELETE CASCADE
);

CREATE TABLE comunicados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    categoria VARCHAR(100),
    fixado TINYINT(1) DEFAULT 0,
    autor VARCHAR(150),
    publico VARCHAR(150),
    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,

    id_funcionario INT NULL,

    nome VARCHAR(150) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    senha VARCHAR(255) NOT NULL,

    tipo ENUM('rh', 'funcionario') DEFAULT 'funcionario',

    status ENUM('ativo', 'inativo') DEFAULT 'ativo',

    ultimo_login DATETIME NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_funcionario)
        REFERENCES funcionarios(id_funcionario)
        ON DELETE SET NULL
);


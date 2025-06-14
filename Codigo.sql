/* CRIA O BANCO DE DADOS AUTOMATICO */

CREATE DATABASE IF NOT EXISTS JundBio;

USE JundBio;

CREATE TABLE IF NOT EXISTS USUARIO (
	Id INTEGER PRIMARY KEY AUTO_INCREMENT,
	Nome VARCHAR(128) NOT NULL,
	Tipo ENUM('COMUM', 'ADMIN') NOT NULL DEFAULT 'COMUM',
    Email VARCHAR(256) NOT NULL,
    Senha VARCHAR(64) NOT NULL,
    Pontos INTEGER NOT NULL DEFAULT 0,
    Ativo BOOLEAN NOT NULL DEFAULT TRUE,
    Foto VARCHAR(256),
    Biografia VARCHAR(256),
    Ocupacao VARCHAR(64),
    DataRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UltimoLogin TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ESPECIE (
	Id INTEGER PRIMARY KEY AUTO_INCREMENT,
    NomeComum VARCHAR(128) DEFAULT 'Desconhecido',
	NomeCientifico VARCHAR(128),
    Familia VARCHAR(128),
    Classificacao VARCHAR(128),
    Descricao VARCHAR(512),
	StatusExtincao VARCHAR(64),
    Invasor BOOLEAN NOT NULL DEFAULT FALSE,
    Tipo ENUM('FAUNA', 'FLORA') NOT NULL
);

CREATE TABLE IF NOT EXISTS LOCALIZACAO (
    Id INTEGER PRIMARY KEY AUTO_INCREMENT,
    Latitude FLOAT NOT NULL,
    Longitude FLOAT NOT NULL,
    Descricao VARCHAR(256)
);

CREATE TABLE IF NOT EXISTS ESPECIALISTA (
	Id_Usuario INTEGER PRIMARY KEY,
	Registro VARCHAR(11) NOT NULL,
    Area VARCHAR(128) NOT NULL,
	Lattes VARCHAR(20) NOT NULL,
	FOREIGN KEY (Id_Usuario) REFERENCES USUARIO(Id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS POSTAGEM (
	Id INTEGER PRIMARY KEY AUTO_INCREMENT,
    Tipo VARCHAR(32) NOT NULL,
    Texto VARCHAR(1024),
	Status ENUM('PENDENTE', 'NEGADO', 'APROVADO') NOT NULL DEFAULT 'PENDENTE',
    DataHora_Envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Id_Usuario INTEGER,
	FOREIGN KEY (Id_Usuario) REFERENCES USUARIO (Id) ON DELETE SET NULL
);



CREATE TABLE IF NOT EXISTS CURTIDA (
    Id_Usuario INTEGER,
    Id_Postagem INTEGER,
    DataHora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(Id_Usuario, Id_Postagem),
	FOREIGN KEY (Id_Usuario) REFERENCES USUARIO (Id) ON DELETE CASCADE,
	FOREIGN KEY (Id_Postagem) REFERENCES POSTAGEM (Id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS FOTO (
    Id INTEGER PRIMARY KEY AUTO_INCREMENT,
    URL VARCHAR(256) NOT NULL,
    Descricao VARCHAR(256),
    DataHora_Foto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	Status ENUM('PENDENTE', 'NEGADO', 'APROVADO') NOT NULL DEFAULT 'PENDENTE',
	
    Id_Especie INTEGER,
    Id_Localizacao INTEGER,
    Id_Postagem INTEGER,
	FOREIGN KEY (Id_Especie) REFERENCES ESPECIE(Id) ON DELETE SET NULL,
	FOREIGN KEY (Id_Localizacao) REFERENCES LOCALIZACAO(Id) ON DELETE SET NULL,
	FOREIGN KEY (Id_Postagem) REFERENCES POSTAGEM(Id) ON DELETE RESTRICT
);


CREATE TABLE IF NOT EXISTS HISTORICO (
    Id_Postagem INTEGER,
    Id_Especialista INTEGER,
	DataHora TIMESTAMP,
    Acao VARCHAR(128),
    PRIMARY KEY (Id_Postagem, Id_Especialista),
	FOREIGN KEY (Id_Postagem) REFERENCES POSTAGEM (Id) ON DELETE CASCADE,
	FOREIGN KEY (Id_Especialista) REFERENCES ESPECIALISTA (Id_Usuario) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS COMENTARIO (
    Id INTEGER PRIMARY KEY AUTO_INCREMENT,
    Texto VARCHAR(512),
    DataHora TIMESTAMP,
    Id_Usuario INTEGER,
    Id_Postagem INTEGER,
	FOREIGN KEY (Id_Usuario) REFERENCES USUARIO (Id) ON DELETE CASCADE,
	FOREIGN KEY (Id_Postagem) REFERENCES POSTAGEM (Id) ON DELETE CASCADE
);

INSERT INTO USUARIO (`Nome`, `Tipo`, `Email`, `Senha`, `Pontos`, `Ativo`) VALUES ('Administrador', 'ADMIN', 'admin@jundbio.com', '$2y$10$XGTFx8aTDgCy9nMVoIF7buLaSFkXZwcs9A8BqH2IyDUFF7F0taPZq', '0', '1'); 

INSERT INTO ESPECIE (`Id`, `NomeComum`, `NomeCientifico`, `Familia`, `Classificacao`, `Descricao`, `StatusExtincao`) VALUES (NULL, 'Desconhecido', NULL, NULL, NULL, NULL, NULL);
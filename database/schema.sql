CREATE DATABASE IF NOT EXISTS laravel;
USE laravel;

CREATE TABLE IF NOT EXISTS produtos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS variacoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id BIGINT UNSIGNED NOT NULL,
    nome VARCHAR(255) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    desconto DECIMAL(10,2) NOT NULL,
    valor_minimo DECIMAL(10,2) NOT NULL DEFAULT 0,
    data_inicio TIMESTAMP NULL,
    data_fim TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS pedidos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('pendente', 'processando', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente',
    subtotal DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) NOT NULL DEFAULT 0,
    frete DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    cupom_id BIGINT UNSIGNED NULL,
    cep VARCHAR(8) NOT NULL,
    logradouro VARCHAR(255) NOT NULL,
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    uf CHAR(2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (cupom_id) REFERENCES cupons(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS pedido_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id BIGINT UNSIGNED NOT NULL,
    produto_id BIGINT UNSIGNED NOT NULL,
    variacao_id BIGINT UNSIGNED NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (variacao_id) REFERENCES variacoes(id) ON DELETE SET NULL
);

CREATE INDEX idx_produtos_nome ON produtos(nome);
CREATE INDEX idx_cupons_codigo ON cupons(codigo);
CREATE INDEX idx_pedidos_numero ON pedidos(numero_pedido);
CREATE INDEX idx_pedidos_status ON pedidos(status);
CREATE INDEX idx_pedido_items_pedido ON pedido_items(pedido_id); 
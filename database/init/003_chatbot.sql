-- Vector embedding của sản phẩm cho chatbot (tìm kiếm ngữ nghĩa).
CREATE TABLE IF NOT EXISTS product_embeddings (
    product_id BIGINT UNSIGNED NOT NULL,
    content_hash CHAR(64) NOT NULL,
    embedding MEDIUMTEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id),
    CONSTRAINT fk_product_embeddings_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

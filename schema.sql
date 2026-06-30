-- Multi-tenant tracking pixel schema

CREATE TABLE IF NOT EXISTS sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id VARCHAR(32) NOT NULL UNIQUE,      -- public id, goes in the snippet (e.g. "WH-7f3a9c2e")
    api_key VARCHAR(64) NOT NULL UNIQUE,       -- private, used only server-side (e.g. for reporting API)
    name VARCHAR(255) NOT NULL,
    owner_email VARCHAR(255) DEFAULT NULL,
    status ENUM('active','paused') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    site_id VARCHAR(32) NOT NULL,
    event_type VARCHAR(50) NOT NULL,           -- 'pageview' | 'conversion' | custom name
    session_id VARCHAR(64) NOT NULL,           -- generated client-side, persists across pages in one visit
    click_id VARCHAR(64) DEFAULT NULL,         -- affiliate/campaign click id if present in URL (?cid=, ?clickid=, etc.)
    value DECIMAL(10,2) DEFAULT NULL,          -- generic value field, e.g. sale amount, lead score
    page_url VARCHAR(2048) DEFAULT NULL,
    referrer VARCHAR(2048) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(512) DEFAULT NULL,
    meta JSON DEFAULT NULL,                    -- catch-all for any custom key/values the customer sends
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_site_time (site_id, created_at),
    INDEX idx_session (session_id),
    INDEX idx_click (click_id),
    CONSTRAINT fk_events_site FOREIGN KEY (site_id) REFERENCES sites(site_id)
);

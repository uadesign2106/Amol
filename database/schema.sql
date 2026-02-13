CREATE DATABASE IF NOT EXISTS green_plus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE green_plus;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(120) NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE industries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  industry_name VARCHAR(120) NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(180) NOT NULL,
  description TEXT,
  image VARCHAR(255),
  price_type ENUM('Fixed','Hidden') NOT NULL DEFAULT 'Hidden',
  price DECIMAL(10,2) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

CREATE TABLE product_industries (
  product_id INT NOT NULL,
  industry_id INT NOT NULL,
  PRIMARY KEY(product_id, industry_id),
  CONSTRAINT fk_pi_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_pi_industry FOREIGN KEY (industry_id) REFERENCES industries(id) ON DELETE CASCADE
);

CREATE TABLE enquiries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL,
  mobile VARCHAR(20) NOT NULL,
  product_id INT NULL,
  message TEXT,
  status ENUM('Pending','Responded','Closed') NOT NULL DEFAULT 'Pending',
  source VARCHAR(30) NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_enquiry_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- legacy admin table retained to avoid breaking older data
CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- RBAC tables
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  permission_key VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE role_permissions (
  role_id INT NOT NULL,
  permission_id INT NOT NULL,
  PRIMARY KEY(role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

INSERT INTO admin(username, password) VALUES ('admin', '$2y$12$W5jyMxFi6hnR/Eo6NpUIHOueM.6m5hHqa/bbQebP3Sq4fQwPa2mgm');
INSERT INTO categories(category_name,status) VALUES ('Fertilizers',1),('Crop Protection',1),('Seeds',1);
INSERT INTO industries(industry_name,status) VALUES ('Agriculture',1),('Food Processing',1),('Horticulture',1);

INSERT INTO roles (role_name) VALUES ('Super Admin'), ('Admin'), ('Staff');
INSERT INTO permissions (permission_key) VALUES
('dashboard.view'),
('products.manage'),
('categories.manage'),
('industries.manage'),
('enquiries.view'),
('enquiries.respond'),
('enquiries.manage'),
('users.manage');

-- Super Admin: full access
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Admin: product + enquiries access
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE permission_key IN ('dashboard.view','products.manage','enquiries.view','enquiries.respond','enquiries.manage');

-- Staff: enquiries view/respond only
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE permission_key IN ('dashboard.view','enquiries.view','enquiries.respond');

INSERT INTO users(name,email,password,role_id,status,created_at)
VALUES
('Super Admin','admin@greenpluspune.com','$2y$12$W5jyMxFi6hnR/Eo6NpUIHOueM.6m5hHqa/bbQebP3Sq4fQwPa2mgm',1,1,NOW());

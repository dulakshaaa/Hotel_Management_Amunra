-- Database and tables for portfolio booking demo
CREATE DATABASE IF NOT EXISTS portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio;

-- users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- reservations table (updated)
CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_name VARCHAR(200) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  checkin DATE NOT NULL,
  checkout DATE NOT NULL,
  checkin_time TIME DEFAULT '14:00',
  checkout_time TIME DEFAULT '11:00',
  guests INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- rooms table (updated with availability)
DROP TABLE IF EXISTS rooms;
CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(50) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description TEXT NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  features JSON NOT NULL,
  total_rooms INT NOT NULL DEFAULT 10,
  available_rooms INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- menu table
CREATE TABLE IF NOT EXISTS menu (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  category VARCHAR(50) NOT NULL,
  image_url VARCHAR(500),
  available BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- orders table
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT NOT NULL,
  user_id INT NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- order_items table
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  menu_item_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_item_id) REFERENCES menu(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Example user (username: demo, password: 'password')
-- Password is a bcrypt hash for 'password' (ensure you replace with your own in production)
INSERT INTO users (username, email, password) VALUES
('demo', 'demo@example.com', '$2y$10$wH6YHkV8V0x/5r9r1x8g9uKQyq1C0cw/0ZpBqD.3Qk5zFkh6a0q1G');

-- Insert rooms with availability
INSERT INTO rooms (name, category, price, description, image_url, features, total_rooms, available_rooms) VALUES
('Hermes Chambers', 'standard', 199.00, 'Enjoy stunning views of the Nile River from your private balcony in our elegantly appointed standard room.', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80', '["King Bed", "River View", "Free WiFi"]', 8, 8),

('Aphrodite Suites', 'deluxe', 299.00, 'Experience royal comfort in our spacious deluxe room featuring Egyptian-inspired decor and premium amenities.', 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80', '["King Bed", "Sitting Area", "Luxury Bath"]', 6, 6),

('Zues\' Throne', 'suite', 499.00, 'Live like Egyptian royalty in our expansive suite with separate living area, dining space, and panoramic Nile views.', 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80', '["Separate Living Room", "Dining Area"]', 4, 4),

('Ra-Apollo Penthouse', 'suite', 499.00, 'Live like Egyptian royalty in our expansive suite with separate living area, dining space, and panoramic Nile views.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS5Wf8WxRdfCGXG6TLperpYJkaMtf0VnX03TQ&s', '["Separate Living Room", "Dining Area"]', 3, 3);

-- Sample menu items (with images)
DELETE FROM menu;
INSERT INTO menu (name, description, price, category, image_url) VALUES
('Caesar Salad', 'Fresh romaine with parmesan and croutons', 12.99, 'appetizers', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'),
('Grilled Salmon', 'Premium Atlantic salmon with seasonal vegetables', 28.99, 'main', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'),
('Filet Mignon', 'USDA Prime beef with truffle butter', 42.99, 'main', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'),
('Lobster Tail', 'Fresh Maine lobster tail with lemon butter', 35.99, 'main', 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'),
('Chocolate Lava Cake', 'Warm chocolate cake with vanilla ice cream', 9.99, 'desserts', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'),
('Tiramisu', 'Classic Italian dessert with espresso', 8.99, 'desserts', 'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'),
('Egyptian Ful Medames', 'Traditional fava bean dip', 7.99, 'appetizers', 'https://images.unsplash.com/photo-1585238341710-4913d3a3a48f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'),
('Koshari', 'Egyptian pasta with lentils and chickpeas', 14.99, 'main', 'https://images.unsplash.com/photo-1585238341710-4913d3a3a48f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'),
('Fresh Orange Juice', 'Freshly squeezed', 5.99, 'beverages', 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60'),
('Premium Egyptian Wine', 'Red wine from local vineyards', 24.99, 'beverages', 'https://images.unsplash.com/photo-1510812431401-41d2cab2707d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60');


-- Create restaurants table
CREATE TABLE restaurants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    image_url VARCHAR(500),
    description TEXT,
    price_range VARCHAR(10),
    open BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create restaurant_features table (for storing features/amenities)
CREATE TABLE restaurant_features (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT NOT NULL,
    feature VARCHAR(100) NOT NULL,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- Insert sample restaurants
INSERT INTO restaurants (name, type, image_url, description, price_range, open) VALUES
('Pharaoh\'s Feast', 'Fine Dining', 'https://cdn.pixabay.com/photo/2017/08/07/08/56/restaurant-2607129_1280.jpg', 'Luxury Egyptian fine dining experience.', '$$$', TRUE),
('Nile Breeze Café', 'Cafe', 'https://cdn.pixabay.com/photo/2016/11/18/14/05/cafe-1836415_1280.jpg', 'Relaxed café with drinks and light meals.', '$$', TRUE),
('Golden Pyramid Grill', 'Buffet', 'https://cdn.pixabay.com/photo/2016/11/29/12/54/buffet-1866499_1280.jpg', 'International buffet experience.', '$$$', FALSE);

-- Insert restaurant features
INSERT INTO restaurant_features (restaurant_id, feature) VALUES
(1, 'Fine Dining'),
(1, 'Live Music'),
(1, 'Elegant Interior'),
(2, 'Coffee'),
(2, 'Outdoor Seating'),
(2, 'Wi-Fi'),
(3, 'Buffet'),
(3, 'Family Friendly'),
(3, 'Live Cooking');
CREATE TABLE IF NOT EXISTS User (
    id TEXT PRIMARY KEY,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    role TEXT NOT NULL,
    password TEXT NOT NULL,
    company_id TEXT,
    balance REAL DEFAULT 800,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS Bus_Company (
    id TEXT PRIMARY KEY,
    name TEXT UNIQUE NOT NULL,
    logo_path TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Trips (
    id TEXT PRIMARY KEY,
    company_id TEXT NOT NULL,
    destination_city TEXT NOT NULL,
    arrival_time DATETIME NOT NULL,
    departure_time DATETIME NOT NULL,
    departure_city TEXT NOT NULL,
    price INTEGER NOT NULL,
    capacity INTEGER NOT NULL,
    created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Tickets (
    id TEXT PRIMARY KEY,
    trip_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    status TEXT DEFAULT 'active',
    total_price INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES Trips(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Booked_Seats (
    id TEXT PRIMARY KEY,
    ticket_id TEXT NOT NULL,
    seat_number INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES Tickets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Coupons (
    id TEXT PRIMARY KEY,
    code TEXT NOT NULL,
    discount REAL NOT NULL,
    company_id TEXT,
    usage_limit INTEGER NOT NULL,
    expire_date DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS User_Coupons (
    id TEXT PRIMARY KEY,
    coupon_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES Coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);

INSERT INTO User (id, email, password, full_name, role, balance) VALUES 
('admin-001', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin', 10000.0);

INSERT INTO Bus_Company (id, name) VALUES 
('company-001', 'Metro Turizm'),
('company-002', 'Pamukkale Turizm'),
('company-003', 'Kamil Koç');

INSERT INTO User (id, email, password, full_name, role, balance, company_id) VALUES 
('fadmin-001', 'metro@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Metro Admin', 'firma_admin', 0, 'company-001'),
('fadmin-002', 'pamukkale@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pamukkale Admin', 'firma_admin', 0, 'company-002'),
('user-001', 'user@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Kullanıcı', 'user', 1500, NULL);

INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) VALUES 
('trip-001', 'company-001', 'İstanbul', 'Ankara', '2025-10-25 09:00:00', '2025-10-25 14:30:00', 250, 45),
('trip-002', 'company-001', 'Ankara', 'İzmir', '2025-10-25 10:00:00', '2025-10-25 18:00:00', 300, 45),
('trip-003', 'company-002', 'İstanbul', 'Antalya', '2025-10-25 20:00:00', '2025-10-26 08:00:00', 350, 50),
('trip-004', 'company-002', 'Mersin', 'Ankara', '2025-10-24 15:00:00', '2025-10-24 22:00:00', 280, 40),
('trip-005', 'company-003', 'İzmir', 'İstanbul', '2025-10-26 08:00:00', '2025-10-26 16:00:00', 270, 45),
('trip-006', 'company-001', 'Ankara', 'Mersin', '2025-10-24 10:00:00', '2025-10-24 17:00:00', 260, 40),
('trip-007', 'company-001', 'İstanbul', 'Bursa', '2025-10-25 08:00:00', '2025-10-25 11:00:00', 120, 45),
('trip-008', 'company-002', 'Ankara', 'Konya', '2025-10-25 09:30:00', '2025-10-25 13:00:00', 150, 40),
('trip-009', 'company-003', 'İzmir', 'Antalya', '2025-10-25 22:00:00', '2025-10-26 06:00:00', 280, 45),
('trip-010', 'company-001', 'Adana', 'İstanbul', '2025-10-25 19:00:00', '2025-10-26 07:00:00', 350, 50),
('trip-011', 'company-002', 'Gaziantep', 'Ankara', '2025-10-25 20:00:00', '2025-10-26 06:00:00', 320, 45),
('trip-012', 'company-003', 'Kayseri', 'İstanbul', '2025-10-25 18:00:00', '2025-10-26 04:00:00', 300, 40),
('trip-013', 'company-001', 'Eskişehir', 'İzmir', '2025-10-25 10:00:00', '2025-10-25 16:00:00', 200, 45),
('trip-014', 'company-002', 'Samsun', 'Ankara', '2025-10-25 08:00:00', '2025-10-25 14:00:00', 220, 40),
('trip-015', 'company-003', 'Trabzon', 'İstanbul', '2025-10-25 17:00:00', '2025-10-26 08:00:00', 400, 45),
('trip-016', 'company-001', 'Denizli', 'İstanbul', '2025-10-25 19:00:00', '2025-10-26 06:00:00', 320, 45),
('trip-017', 'company-002', 'Malatya', 'Ankara', '2025-10-25 18:00:00', '2025-10-26 04:00:00', 300, 40),
('trip-018', 'company-003', 'Diyarbakır', 'İstanbul', '2025-10-25 16:00:00', '2025-10-26 08:00:00', 450, 50),
('trip-019', 'company-001', 'Manisa', 'Ankara', '2025-10-25 09:00:00', '2025-10-25 16:00:00', 250, 45),
('trip-020', 'company-002', 'Balıkesir', 'İstanbul', '2025-10-25 07:00:00', '2025-10-25 11:00:00', 150, 40),
('trip-021', 'company-003', 'Kahramanmaraş', 'Ankara', '2025-10-25 19:00:00', '2025-10-26 05:00:00', 310, 45),
('trip-022', 'company-001', 'Van', 'İstanbul', '2025-10-25 14:00:00', '2025-10-26 10:00:00', 500, 50),
('trip-023', 'company-002', 'Aydın', 'İzmir', '2025-10-25 08:00:00', '2025-10-25 10:00:00', 80, 40),
('trip-024', 'company-003', 'Tekirdağ', 'İstanbul', '2025-10-25 06:00:00', '2025-10-25 08:30:00', 100, 45),
('trip-025', 'company-001', 'Şanlıurfa', 'Ankara', '2025-10-25 17:00:00', '2025-10-26 05:00:00', 380, 45),
('trip-026', 'company-002', 'Muğla', 'İstanbul', '2025-10-25 20:00:00', '2025-10-26 08:00:00', 380, 50),
('trip-027', 'company-003', 'Kocaeli', 'Ankara', '2025-10-25 09:00:00', '2025-10-25 14:00:00', 200, 45),
('trip-028', 'company-001', 'Hatay', 'İstanbul', '2025-10-25 18:00:00', '2025-10-26 08:00:00', 420, 50),
('trip-029', 'company-002', 'Mardin', 'Ankara', '2025-10-25 16:00:00', '2025-10-26 06:00:00', 400, 45),
('trip-030', 'company-003', 'Elazığ', 'İstanbul', '2025-10-25 17:00:00', '2025-10-26 07:00:00', 380, 45),
('trip-031', 'company-001', 'Erzurum', 'Ankara', '2025-10-25 15:00:00', '2025-10-26 05:00:00', 420, 50),
('trip-032', 'company-002', 'Sakarya', 'İzmir', '2025-10-25 10:00:00', '2025-10-25 18:00:00', 280, 45),
('trip-033', 'company-003', 'Ordu', 'İstanbul', '2025-10-25 16:00:00', '2025-10-26 06:00:00', 350, 45),
('trip-034', 'company-001', 'Afyonkarahisar', 'İstanbul', '2025-10-25 11:00:00', '2025-10-25 17:00:00', 220, 40),
('trip-035', 'company-002', 'Sivas', 'Ankara', '2025-10-25 14:00:00', '2025-10-25 22:00:00', 280, 45),
('trip-036', 'company-003', 'Zonguldak', 'İstanbul', '2025-10-25 08:00:00', '2025-10-25 14:00:00', 200, 40),
('trip-037', 'company-001', 'Çanakkale', 'İstanbul', '2025-10-25 07:00:00', '2025-10-25 12:00:00', 180, 45),
('trip-038', 'company-002', 'Kütahya', 'Ankara', '2025-10-25 10:00:00', '2025-10-25 15:00:00', 180, 40),
('trip-039', 'company-003', 'Tokat', 'İstanbul', '2025-10-25 16:00:00', '2025-10-26 04:00:00', 320, 45),
('trip-040', 'company-001', 'Rize', 'Ankara', '2025-10-25 15:00:00', '2025-10-26 07:00:00', 450, 50),
('trip-041', 'company-002', 'Edirne', 'İstanbul', '2025-10-25 06:00:00', '2025-10-25 09:00:00', 120, 40),
('trip-042', 'company-003', 'Uşak', 'İzmir', '2025-10-25 09:00:00', '2025-10-25 12:00:00', 130, 40),
('trip-043', 'company-001', 'Bolu', 'İstanbul', '2025-10-25 08:00:00', '2025-10-25 12:00:00', 150, 45),
('trip-044', 'company-002', 'Isparta', 'Ankara', '2025-10-25 12:00:00', '2025-10-25 18:00:00', 240, 40),
('trip-045', 'company-003', 'Giresun', 'İstanbul', '2025-10-25 17:00:00', '2025-10-26 07:00:00', 370, 45),
('trip-046', 'company-001', 'Çorum', 'Ankara', '2025-10-25 09:00:00', '2025-10-25 12:00:00', 120, 40),
('trip-047', 'company-002', 'Düzce', 'İstanbul', '2025-10-25 07:00:00', '2025-10-25 11:00:00', 140, 40),
('trip-048', 'company-003', 'Osmaniye', 'Ankara', '2025-10-25 18:00:00', '2025-10-26 04:00:00', 320, 45),
('trip-049', 'company-001', 'Kırıkkale', 'İstanbul', '2025-10-25 10:00:00', '2025-10-25 16:00:00', 220, 40),
('trip-050', 'company-002', 'Amasya', 'Ankara', '2025-10-25 11:00:00', '2025-10-25 16:00:00', 200, 40),
('trip-051', 'company-003', 'Kastamonu', 'İstanbul', '2025-10-25 09:00:00', '2025-10-25 16:00:00', 240, 45),
('trip-052', 'company-001', 'Nevşehir', 'Ankara', '2025-10-25 10:00:00', '2025-10-25 14:00:00', 160, 40),
('trip-053', 'company-002', 'Burdur', 'İzmir', '2025-10-25 11:00:00', '2025-10-25 16:00:00', 200, 40),
('trip-054', 'company-003', 'Kırşehir', 'İstanbul', '2025-10-25 11:00:00', '2025-10-25 18:00:00', 250, 40),
('trip-055', 'company-001', 'Niğde', 'Ankara', '2025-10-25 09:00:00', '2025-10-25 13:00:00', 150, 40),
('trip-056', 'company-002', 'Aksaray', 'İstanbul', '2025-10-25 12:00:00', '2025-10-25 20:00:00', 280, 45),
('trip-057', 'company-003', 'Yozgat', 'Ankara', '2025-10-25 10:00:00', '2025-10-25 14:00:00', 140, 40),
('trip-058', 'company-001', 'Karaman', 'İstanbul', '2025-10-25 13:00:00', '2025-10-25 22:00:00', 300, 45),
('trip-059', 'company-002', 'Kırklareli', 'İstanbul', '2025-10-25 06:00:00', '2025-10-25 09:00:00', 110, 40),
('trip-060', 'company-003', 'Bartın', 'Ankara', '2025-10-25 08:00:00', '2025-10-25 14:00:00', 200, 40),
('trip-061', 'company-001', 'Sinop', 'İstanbul', '2025-10-25 10:00:00', '2025-10-25 19:00:00', 280, 45),
('trip-062', 'company-002', 'Artvin', 'Ankara', '2025-10-25 14:00:00', '2025-10-26 06:00:00', 480, 50),
('trip-063', 'company-003', 'Bilecik', 'İstanbul', '2025-10-25 07:00:00', '2025-10-25 10:00:00', 110, 40),
('trip-064', 'company-001', 'Karabük', 'Ankara', '2025-10-25 08:00:00', '2025-10-25 13:00:00', 180, 40),
('trip-065', 'company-002', 'Yalova', 'İstanbul', '2025-10-25 06:00:00', '2025-10-25 08:00:00', 80, 40),
('trip-066', 'company-003', 'Kilis', 'Ankara', '2025-10-25 19:00:00', '2025-10-26 07:00:00', 400, 45),
('trip-067', 'company-001', 'Ardahan', 'İstanbul', '2025-10-25 13:00:00', '2025-10-26 08:00:00', 520, 50),
('trip-068', 'company-002', 'Iğdır', 'Ankara', '2025-10-25 14:00:00', '2025-10-26 07:00:00', 500, 50),
('trip-069', 'company-003', 'Batman', 'İstanbul', '2025-10-25 16:00:00', '2025-10-26 08:00:00', 460, 50),
('trip-070', 'company-001', 'Şırnak', 'Ankara', '2025-10-25 15:00:00', '2025-10-26 07:00:00', 480, 50),
('trip-071', 'company-002', 'Siirt', 'İstanbul', '2025-10-25 16:00:00', '2025-10-26 08:00:00', 470, 50),
('trip-072', 'company-003', 'Hakkari', 'Ankara', '2025-10-25 14:00:00', '2025-10-26 08:00:00', 520, 50),
('trip-073', 'company-001', 'Muş', 'İstanbul', '2025-10-25 15:00:00', '2025-10-26 08:00:00', 490, 50),
('trip-074', 'company-002', 'Bitlis', 'Ankara', '2025-10-25 15:00:00', '2025-10-26 06:00:00', 460, 50),
('trip-075', 'company-003', 'Ağrı', 'İstanbul', '2025-10-25 14:00:00', '2025-10-26 08:00:00', 510, 50),
('trip-076', 'company-001', 'Gümüşhane', 'Ankara', '2025-10-25 13:00:00', '2025-10-26 02:00:00', 380, 45),
('trip-077', 'company-002', 'Bayburt', 'İstanbul', '2025-10-25 14:00:00', '2025-10-26 06:00:00', 420, 45),
('trip-078', 'company-003', 'Tunceli', 'Ankara', '2025-10-25 15:00:00', '2025-10-26 04:00:00', 400, 45),
('trip-079', 'company-001', 'Bingöl', 'İstanbul', '2025-10-25 15:00:00', '2025-10-26 07:00:00', 450, 50),
('trip-080', 'company-002', 'Kars', 'Ankara', '2025-10-25 14:00:00', '2025-10-26 07:00:00', 490, 50);

INSERT INTO Coupons (id, code, discount, usage_limit, expire_date, company_id) VALUES 
('coupon-001', 'INDIRIM10', 10, 100, '2025-12-31 23:59:59', NULL),
('coupon-002', 'METRO20', 20, 50, '2025-11-30 23:59:59', 'company-001'),
('coupon-003', 'PAMUKKALE15', 15, 75, '2025-12-15 23:59:59', 'company-002');

INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) VALUES 
('trip-today-1', 'company-001', 'İstanbul', 'Ankara', datetime('now', '+30 minutes'), datetime('now', '+6 hours'), 250, 44),
('trip-today-2', 'company-002', 'Ankara', 'İzmir', datetime('now', '+2 hours'), datetime('now', '+10 hours'), 300, 44),
('trip-today-3', 'company-003', 'İzmir', 'Antalya', datetime('now', '+4 hours'), datetime('now', '+12 hours'), 320, 44),
('trip-tomorrow-1', 'company-001', 'İstanbul', 'Bursa', datetime('now', '+1 day', '+2 hours'), datetime('now', '+1 day', '+5 hours'), 150, 44),
('trip-tomorrow-2', 'company-002', 'Ankara', 'Konya', datetime('now', '+1 day', '+3 hours'), datetime('now', '+1 day', '+7 hours'), 180, 44);

UPDATE Trips SET capacity = 44;


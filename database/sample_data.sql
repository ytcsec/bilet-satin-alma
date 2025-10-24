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
('trip-006', 'company-001', 'Ankara', 'Mersin', '2025-10-24 10:00:00', '2025-10-24 17:00:00', 260, 40);

INSERT INTO Coupons (id, code, discount, usage_limit, expire_date, company_id) VALUES 
('coupon-001', 'INDIRIM10', 10, 100, '2025-12-31 23:59:59', NULL),
('coupon-002', 'METRO20', 20, 50, '2025-11-30 23:59:59', 'company-001'),
('coupon-003', 'PAMUKKALE15', 15, 75, '2025-12-15 23:59:59', 'company-002');

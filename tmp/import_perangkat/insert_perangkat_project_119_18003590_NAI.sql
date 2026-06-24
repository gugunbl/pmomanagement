-- Insert perangkat dari Grant Number 18003590-NAI untuk id_project = 119
-- Asumsi: jenis=8 (Firewall), lokasi=DC CIBITUNG/id_lokasi=6, merk=Trellix.
-- Jalankan di database yang sudah memiliki m_project.id_project = 119.
START TRANSACTION;

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS7500', 'A092150120', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', 'RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A092150120');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS 4port FO Mod 10-1GigE', 'A0C5147050', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', 'RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A0C5147050');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS7500', 'A092150134', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', 'RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A092150134');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS7500', 'A092150126', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', 'RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A092150126');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS7500', 'A092150136', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', 'RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A092150136');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS 4port FO Mod 10-1GigE', 'A0C5147052', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', 'RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A0C5147052');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS7500', 'A092150117', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', 'RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A092150117');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS7500', 'A092150133', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', 'RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A092150133');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS 4port FO Mod 10-1GigE', 'A0C5147071', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', 'RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A0C5147071');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS7500', 'A092150159', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', 'RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A092150159');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS 4port FO Mod 10-1GigE', 'A0C5147070', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', 'RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A0C5147070');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS 4port FO Mod 10-1GigE', 'A0C5144013', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', 'RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A0C5144013');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS 4port FO Mod 10-1GigE', 'A0C5147051', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', 'RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A0C5147051');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS7500', 'A092150121', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', 'RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A092150121');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS 4port FO Mod 10-1GigE', 'A0C5145078', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', 'RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A0C5145078');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS 4port FO Mod 10-1GigE', 'A0C5147069', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', 'RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A0C5147069');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS 4port FO Mod 10-1GigE', 'A0C5147049', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', 'RBIAC4P1GMM50MOD - NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A0C5147049');

INSERT INTO m_perangkat (id_project, jenis, merk, type, serialnumber, sik, hostname, status, status_server, site, id_lokasi, lantai, start_warranty, end_warranty, keterangan, type_lisensi, tgl_online, created_at, updated_at)
SELECT 119, 8, 'Trellix', 'NS7500', 'A092150119', '18003590-NAI', NULL, 1, 'New Device', 'DC CIBITUNG', 6, NULL, '2026-01-14', '2027-01-13', 'Grant Number 18003590-NAI; RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', 'RBIPSNS7500NBD - Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS', '2026-01-14', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = 'A092150119');

COMMIT;

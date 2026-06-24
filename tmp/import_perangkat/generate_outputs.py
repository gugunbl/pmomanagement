import pathlib

import openpyxl
from openpyxl.styles import Font, PatternFill
from openpyxl.utils import get_column_letter


BASE = pathlib.Path(__file__).resolve().parent
TEMPLATE = BASE / "template_import_perangkat IPS.xlsx"
OUT_XLSX = BASE / "template_import_perangkat_IPS_18003590_NAI_filled.xlsx"
OUT_SQL = BASE / "insert_perangkat_project_119_18003590_NAI.sql"

ROWS = [
    ("RBIPSNS7500NBD", "Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS", "A092150120", "NS7500"),
    ("RBIAC4P1GMM50MOD", "NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP", "A0C5147050", "NS 4port FO Mod 10-1GigE"),
    ("RBIPSNS7500NBD", "Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS", "A092150134", "NS7500"),
    ("RBIPSNS7500NBD", "Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS", "A092150126", "NS7500"),
    ("RBIPSNS7500NBD", "Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS", "A092150136", "NS7500"),
    ("RBIAC4P1GMM50MOD", "NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP", "A0C5147052", "NS 4port FO Mod 10-1GigE"),
    ("RBIPSNS7500NBD", "Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS", "A092150117", "NS7500"),
    ("RBIPSNS7500NBD", "Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS", "A092150133", "NS7500"),
    ("RBIAC4P1GMM50MOD", "NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP", "A0C5147071", "NS 4port FO Mod 10-1GigE"),
    ("RBIPSNS7500NBD", "Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS", "A092150159", "NS7500"),
    ("RBIAC4P1GMM50MOD", "NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP", "A0C5147070", "NS 4port FO Mod 10-1GigE"),
    ("RBIAC4P1GMM50MOD", "NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP", "A0C5144013", "NS 4port FO Mod 10-1GigE"),
    ("RBIAC4P1GMM50MOD", "NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP", "A0C5147051", "NS 4port FO Mod 10-1GigE"),
    ("RBIPSNS7500NBD", "Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS", "A092150121", "NS7500"),
    ("RBIAC4P1GMM50MOD", "NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP", "A0C5145078", "NS 4port FO Mod 10-1GigE"),
    ("RBIAC4P1GMM50MOD", "NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP", "A0C5147069", "NS 4port FO Mod 10-1GigE"),
    ("RBIAC4P1GMM50MOD", "NS 4port FO Mod 10-1GigE 850nm 50um 1y RMA NEXT BUSINESS DAY SHIP", "A0C5147049", "NS 4port FO Mod 10-1GigE"),
    ("RBIPSNS7500NBD", "Net Sec IPS-NS7500 Appl 1Yr NBD BUSINESS", "A092150119", "NS7500"),
]

SITE = "DC CIBITUNG"
ID_LOKASI = 6
GRANT = "18003590-NAI"
START = "2026-01-14"
END = "2027-01-13"
JENIS = 8
PROJECT = 119
MERK = "Trellix"
STATUS = 1
STATUS_SERVER = "New Device"


def sql_quote(value):
    if value is None:
        return "NULL"
    return "'" + str(value).replace("'", "''") + "'"


def write_xlsx():
    wb = openpyxl.load_workbook(TEMPLATE)
    ws = wb.active
    if ws.max_row > 1:
        ws.delete_rows(2, ws.max_row - 1)

    ws.append([
        "[SAMPLE - AKAN DI-SKIP] SN12345678",
        "SIK-999",
        "SRV-HOST-01",
        "GTI - Ragunan",
        "RCK-01-A",
        4,
        "Enterprise",
        "2024-01-01",
        "2027-01-01",
        "2024-01-15",
    ])

    for sku, desc, serial, _device_type in ROWS:
        ws.append([
            serial,
            GRANT,
            "",
            SITE,
            "",
            "",
            f"{sku} - {desc}",
            START,
            END,
            START,
        ])

    for cell in ws[1]:
        cell.font = Font(bold=True, color="FFFFFF")
        cell.fill = PatternFill("solid", fgColor="366092")

    for col in range(1, ws.max_column + 1):
        letter = get_column_letter(col)
        max_len = max(len(str(ws.cell(row, col).value or "")) for row in range(1, ws.max_row + 1))
        ws.column_dimensions[letter].width = min(max(max_len + 2, 12), 55)

    wb.save(OUT_XLSX)


def write_sql():
    columns = [
        "id_project",
        "jenis",
        "merk",
        "type",
        "serialnumber",
        "sik",
        "hostname",
        "status",
        "status_server",
        "site",
        "id_lokasi",
        "lantai",
        "start_warranty",
        "end_warranty",
        "keterangan",
        "type_lisensi",
        "tgl_online",
        "created_at",
        "updated_at",
    ]
    lines = [
        "-- Insert perangkat dari Grant Number 18003590-NAI untuk id_project = 119",
        "-- Asumsi: jenis=8 (Firewall), lokasi=DC CIBITUNG/id_lokasi=6, merk=Trellix.",
        "-- Jalankan di database yang sudah memiliki m_project.id_project = 119.",
        "START TRANSACTION;",
        "",
    ]

    for sku, desc, serial, device_type in ROWS:
        values = [
            PROJECT,
            JENIS,
            MERK,
            device_type,
            serial,
            GRANT,
            None,
            STATUS,
            STATUS_SERVER,
            SITE,
            ID_LOKASI,
            None,
            START,
            END,
            f"Grant Number {GRANT}; {sku} - {desc}",
            f"{sku} - {desc}",
            START,
            "NOW()",
            "NOW()",
        ]
        select_values = [
            str(value) if isinstance(value, int) or value == "NOW()" else sql_quote(value)
            for value in values
        ]
        lines.append(f"INSERT INTO m_perangkat ({', '.join(columns)})")
        lines.append("SELECT " + ", ".join(select_values))
        lines.append(
            f"WHERE NOT EXISTS (SELECT 1 FROM m_perangkat WHERE serialnumber = {sql_quote(serial)});"
        )
        lines.append("")

    lines.append("COMMIT;")
    lines.append("")
    OUT_SQL.write_text("\n".join(lines), encoding="utf-8")


if __name__ == "__main__":
    write_xlsx()
    write_sql()
    print(OUT_XLSX)
    print(OUT_SQL)
    print(len(ROWS))

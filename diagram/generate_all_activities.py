import os
import shutil
import xml.etree.ElementTree as ET
import xml.dom.minidom as minidom

class DrawioDiagramBuilder:
    ST_SWIMLANE = "swimlane;startSize=30;html=1;whiteSpace=wrap;collapsible=0;connectable=0;container=1;pointerEvents=0;fillColor=none;strokeColor=#000000;fontStyle=1;align=center;fontSize=13;fontColor=#000000;strokeWidth=1.5;"
    ST_START = "ellipse;html=1;fillColor=#000000;strokeColor=none;aspect=fixed;"
    ST_END_OUTER = "ellipse;html=1;fillColor=none;strokeColor=#000000;strokeWidth=2;aspect=fixed;"
    ST_END_INNER = "ellipse;html=1;fillColor=#000000;strokeColor=none;aspect=fixed;"
    ST_ACTION = "rounded=1;whiteSpace=wrap;html=1;arcSize=20;fillColor=#FFFFFF;strokeColor=#000000;fontSize=12;fontColor=#000000;align=center;verticalAlign=middle;strokeWidth=1.5;"
    ST_RHOMBUS = "rhombus;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;fontSize=12;fontColor=#000000;align=center;verticalAlign=middle;strokeWidth=1.5;"
    
    EDGE_ORTHO = "edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#000000;strokeWidth=1.5;fontSize=12;labelBackgroundColor=#FFFFFF;fontColor=#000000;"

    def __init__(self, name, page_id, width=900, height=900):
        self.name = name
        self.page_id = page_id
        self.width = width
        self.height = height
        self.nodes = []
        self.edges = []
        self.counter = 0

    def _next_id(self, prefix="elem"):
        self.counter += 1
        return f"{prefix}_{self.page_id}_{self.counter}"

    def add_node(self, nid, label, style, x, y, w, h, parent="1"):
        self.nodes.append({
            "id": nid, "value": label, "style": style,
            "x": x, "y": y, "w": w, "h": h, "parent": parent
        })

    def link_nodes(self, src, dst, label="", style="", points=None, exit_pt=None, entry_pt=None):
        edge_style = style or self.EDGE_ORTHO
        if exit_pt:
            edge_style += f"exitX={exit_pt[0]};exitY={exit_pt[1]};exitDx=0;exitDy=0;"
        if entry_pt:
            edge_style += f"entryX={entry_pt[0]};entryY={entry_pt[1]};entryDx=0;entryDy=0;"

        self.edges.append({
            "id": self._next_id("edge"), "value": label, "style": edge_style,
            "source": src, "target": dst, "points": points or []
        })

    def save(self, filepath):
        mxfile = ET.Element("mxfile", host="Electron", version="21.6.8",
                            modified="2026-06-15T10:00:00.000Z",
                            agent="Mozilla/5.0", type="device")
        diagram = ET.SubElement(mxfile, "diagram", id=self.page_id, name=self.name)
        model = ET.SubElement(diagram, "mxGraphModel",
                              dx="1000", dy="1000", grid="1", gridSize="10",
                              guides="1", tooltips="1", connect="1", arrows="1",
                              fold="1", page="1", pageScale="1",
                              pageWidth=str(self.width), pageHeight=str(self.height),
                              math="0", shadow="0")
        root = ET.SubElement(model, "root")
        
        ET.SubElement(root, "mxCell", id="0")
        ET.SubElement(root, "mxCell", id="1", parent="0")

        # Nodes
        for n in self.nodes:
            cell = ET.SubElement(root, "mxCell", id=n["id"], value=n["value"],
                                 style=n["style"], vertex="1", parent=n["parent"])
            geom = ET.SubElement(cell, "mxGeometry",
                                 x=str(n["x"]), y=str(n["y"]),
                                 width=str(n["w"]), height=str(n["h"]))
            geom.set("as", "geometry")

        # Edges
        for e in self.edges:
            cell = ET.SubElement(root, "mxCell", id=e["id"], value=e["value"],
                                 style=e["style"], edge="1", parent="1",
                                 source=e["source"], target=e["target"])
            geom = ET.SubElement(cell, "mxGeometry", relative="1")
            geom.set("as", "geometry")
            if e["points"]:
                arr = ET.SubElement(geom, "Array")
                arr.set("as", "points")
                for (px, py) in e["points"]:
                    ET.SubElement(arr, "mxPoint", x=str(px), y=str(py))

        # Add transparent Frame node for headless renderer cropping bounding box
        frame_cell = ET.SubElement(root, "mxCell", id="frame", value="",
                                   style="fillColor=none;strokeColor=none;connectable=0;allowArrows=0;",
                                   vertex="1", parent="1")
        geom_frame = ET.SubElement(frame_cell, "mxGeometry", x="0", y="0",
                                   width=str(self.width), height=str(self.height))
        geom_frame.set("as", "geometry")

        raw = ET.tostring(mxfile, encoding="utf-8")
        dom = minidom.parseString(raw)
        pretty_xml = dom.toprettyxml(indent="    ")
        pretty_xml = "\n".join([line for line in pretty_xml.split("\n") if line.strip()])
        if pretty_xml.startswith("<?xml"):
            pretty_xml = pretty_xml.split("\n", 1)[1]
        out_content = '<?xml version="1.0" encoding="UTF-8"?>\n' + pretty_xml

        os.makedirs(os.path.dirname(filepath), exist_ok=True)
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(out_content)


def build_cascading_crud(b, entity_name):
    # Swimlanes
    b.add_node("lane_left", "Admin", b.ST_SWIMLANE, 60, 40, 400, 990)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 460, 40, 400, 990)
    
    # Vertices
    b.add_node("start", "", b.ST_START, 295, 60, 30, 30)
    b.add_node("a_pilih_menu", f"Pilih menu {entity_name}", b.ST_ACTION, 220, 110, 180, 55)
    b.add_node("s_tampil_data", f"Tampil data {entity_name}", b.ST_ACTION, 570, 170, 180, 55)
    
    # Tambah Row
    b.add_node("d_tambah", "Tambah", b.ST_RHOMBUS, 80, 250, 100, 75)
    b.add_node("s_form_tambah", "Tampil form tambah", b.ST_ACTION, 570, 260, 180, 55)
    b.add_node("a_tambah_data", "Tambah data", b.ST_ACTION, 220, 335, 180, 55)
    b.add_node("d_simpan_tambah", "Simpan", b.ST_RHOMBUS, 260, 410, 100, 75)
    b.add_node("s_data_disimpan_tambah", "Data disimpan", b.ST_ACTION, 570, 420, 180, 55)
    
    # Ubah Row
    b.add_node("d_ubah", "Ubah", b.ST_RHOMBUS, 80, 505, 100, 75)
    b.add_node("a_pilih_ubah", "Pilih data yang di ubah" if entity_name == "pengguna" else "Pilih data yang diubah", b.ST_ACTION, 220, 515, 180, 55)
    b.add_node("s_form_ubah", "Tampil form ubah", b.ST_ACTION, 570, 515, 180, 55)
    b.add_node("a_ubah_data", "Ubah data", b.ST_ACTION, 220, 590, 180, 55)
    b.add_node("d_simpan_ubah", "Simpan", b.ST_RHOMBUS, 260, 665, 100, 75)
    b.add_node("s_data_disimpan_ubah", "Data disimpan", b.ST_ACTION, 570, 675, 180, 55)
    
    # Hapus Row
    b.add_node("d_hapus", "Hapus", b.ST_RHOMBUS, 80, 760, 100, 75)
    b.add_node("a_pilih_hapus", "Pilih data yang akan dihapus", b.ST_ACTION, 220, 770, 180, 55)
    b.add_node("d_yakin_hapus", "yakin hapus", b.ST_RHOMBUS, 260, 845, 100, 75)
    b.add_node("s_data_dihapus", "Data dihapus", b.ST_ACTION, 570, 855, 180, 55)
    
    # End node
    b.add_node("final", "", b.ST_END_OUTER, 295, 960, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 302, 967, 16, 16)
    
    # Edges
    b.link_nodes("start", "a_pilih_menu")
    b.link_nodes("a_pilih_menu", "s_tampil_data", exit_pt=(0.5, 1), entry_pt=(0, 0.5), points=[(310, 197.5)])
    b.link_nodes("s_tampil_data", "d_tambah", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(130, 197.5)])
    
    # Tambah Flow Connections
    b.link_nodes("d_tambah", "s_form_tambah", label="Y", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("d_tambah", "d_ubah", label="T", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link_nodes("s_form_tambah", "a_tambah_data", exit_pt=(0.5, 1), entry_pt=(1, 0.5), points=[(660, 362.5)])
    b.link_nodes("a_tambah_data", "d_simpan_tambah")
    b.link_nodes("d_simpan_tambah", "s_data_disimpan_tambah", label="Y", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("d_simpan_tambah", "s_tampil_data", label="T", exit_pt=(0.5, 1), entry_pt=(1, 0.5), points=[(310, 495), (880, 495), (880, 197.5)])
    b.link_nodes("s_data_disimpan_tambah", "s_tampil_data", exit_pt=(1, 0.5), entry_pt=(1, 0.5), points=[(880, 447.5), (880, 197.5)])
    
    # Ubah Flow Connections
    b.link_nodes("d_ubah", "a_pilih_ubah", label="Y", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("d_ubah", "d_hapus", label="T", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link_nodes("a_pilih_ubah", "s_form_ubah")
    b.link_nodes("s_form_ubah", "a_ubah_data", exit_pt=(0.5, 1), entry_pt=(1, 0.5), points=[(660, 617.5)])
    b.link_nodes("a_ubah_data", "d_simpan_ubah")
    b.link_nodes("d_simpan_ubah", "s_data_disimpan_ubah", label="Y", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("d_simpan_ubah", "s_tampil_data", label="T", exit_pt=(0.5, 1), entry_pt=(1, 0.5), points=[(310, 750), (880, 750), (880, 197.5)])
    b.link_nodes("s_data_disimpan_ubah", "s_tampil_data", exit_pt=(1, 0.5), entry_pt=(1, 0.5), points=[(880, 702.5), (880, 197.5)])
    
    # Hapus Flow Connections
    b.link_nodes("d_hapus", "a_pilih_hapus", label="Y", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("d_hapus", "final", label="T", exit_pt=(0.5, 1), entry_pt=(0, 0.5), points=[(130, 975)])
    b.link_nodes("a_pilih_hapus", "d_yakin_hapus")
    b.link_nodes("d_yakin_hapus", "s_data_dihapus", label="Y", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("d_yakin_hapus", "s_tampil_data", label="T", exit_pt=(0.5, 1), entry_pt=(1, 0.5), points=[(310, 930), (880, 930), (880, 197.5)])
    b.link_nodes("s_data_dihapus", "s_tampil_data", exit_pt=(1, 0.5), entry_pt=(1, 0.5), points=[(880, 882.5), (880, 197.5)])


def generate_activities(dest_dir):
    # Clear directory first to keep it clean (exactly 11 files)
    if os.path.exists(dest_dir):
        shutil.rmtree(dest_dir)
    os.makedirs(dest_dir)

    print("Generating 11 Activity Diagrams...")

    # ================== ADMIN DIAGRAMS (6 File) ==================

    # 1. Admin Login
    b = DrawioDiagramBuilder("Activity Login Admin", "act-adm-login", 900, 750)
    b.add_node("lane_left", "Admin", b.ST_SWIMLANE, 60, 40, 360, 660)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 660)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("masuk_web", "masuk Halaman web", b.ST_ACTION, 150, 140, 180, 55)
    b.add_node("tampil_form", "tampil form Login", b.ST_ACTION, 570, 140, 180, 55)
    b.add_node("input_kred", "memasukan username\ndan password", b.ST_ACTION, 150, 230, 180, 55)
    b.add_node("klik_login", "klik Button Login", b.ST_ACTION, 150, 320, 180, 55)
    b.add_node("validasi", "validasi", b.ST_RHOMBUS, 520, 317.5, 100, 60)
    b.add_node("tampil_gagal", "tampil pesan gagal", b.ST_ACTION, 680, 320, 140, 55)
    b.add_node("tampil_sukses", "tampil pesan\nberhasil login", b.ST_ACTION, 570, 420, 180, 55)
    b.add_node("tampil_dashboard", "tampil Halaman\nDashboard", b.ST_ACTION, 570, 510, 180, 55)
    b.add_node("final", "", b.ST_END_OUTER, 645, 600, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 652, 607, 16, 16)
    
    b.link_nodes("start", "masuk_web")
    b.link_nodes("masuk_web", "tampil_form", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("tampil_form", "input_kred", exit_pt=(0, 0.5), entry_pt=(1, 0.5),
                 points=[(450, 167.5), (450, 257.5)])
    b.link_nodes("input_kred", "klik_login")
    b.link_nodes("klik_login", "validasi", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("validasi", "tampil_gagal", label="T", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("tampil_gagal", "tampil_form", exit_pt=(0.5, 0), entry_pt=(1, 0.5),
                 points=[(750, 167.5)])
    b.link_nodes("validasi", "tampil_sukses", label="Y", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link_nodes("tampil_sukses", "tampil_dashboard")
    b.link_nodes("tampil_dashboard", "final")
    b.save(os.path.join(dest_dir, "admin_login.drawio"))

    # 2. Admin Kelola Produk (CRUD)
    b = DrawioDiagramBuilder("Activity Kelola Produk", "act-adm-kelola-prod", 950, 1050)
    build_cascading_crud(b, "produk")
    b.save(os.path.join(dest_dir, "admin_kelola_produk.drawio"))

    # 3. Admin Kelola Kategori (CRUD)
    b = DrawioDiagramBuilder("Activity Kelola Kategori", "act-adm-kelola-kat", 950, 1050)
    build_cascading_crud(b, "kategori")
    b.save(os.path.join(dest_dir, "admin_kelola_kategori.drawio"))

    # 4. Admin Kelola Pesanan
    b = DrawioDiagramBuilder("Activity Kelola Pesanan", "act-adm-kelola-pes", 900, 750)
    b.add_node("lane_left", "Admin", b.ST_SWIMLANE, 60, 40, 360, 660)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 660)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Membuka Halaman Kelola Pesanan", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Menampilkan Rincian Pesanan Masuk", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("a2", "Memeriksa Bukti Pembayaran Transaksi", b.ST_ACTION, 140, 230, 200, 55)
    b.add_node("dec", "Pembayaran\nValid?", b.ST_RHOMBUS, 180, 310, 120, 75)
    b.add_node("s2_ok", "Mengubah Status ke 'Processed'/'Shipped'\ndan Update Stok Produk", b.ST_ACTION, 560, 320, 200, 55)
    b.add_node("s2_fail", "Mengubah Status ke 'Pending' / Menolak\ndan Memberi Alasan Tolak", b.ST_ACTION, 560, 420, 200, 55)
    b.add_node("s3_noti", "Mengirim Notifikasi Status ke Dashboard User", b.ST_ACTION, 560, 510, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 522.5, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 529.5, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "a2")
    b.link_nodes("a2", "dec")
    b.link_nodes("dec", "s2_ok", label="Ya")
    b.link_nodes("dec", "s2_fail", label="Tidak", exit_pt=(0.5, 1), entry_pt=(0, 0.5), points=[(240, 447.5)])
    b.link_nodes("s2_ok", "s3_noti")
    b.link_nodes("s2_fail", "s3_noti")
    b.link_nodes("s3_noti", "final")
    b.save(os.path.join(dest_dir, "admin_kelola_pesanan.drawio"))

    # 5. Admin Kelola Artikel Blog (CRUD)
    b = DrawioDiagramBuilder("Activity Kelola Artikel", "act-adm-kelola-art", 950, 1050)
    build_cascading_crud(b, "artikel")
    b.save(os.path.join(dest_dir, "admin_kelola_artikel.drawio"))

    # 5b. Admin Kelola Pengguna (CRUD)
    b = DrawioDiagramBuilder("Activity Kelola Pengguna", "act-adm-kelola-pengguna", 950, 1050)
    build_cascading_crud(b, "pengguna")
    b.save(os.path.join(dest_dir, "admin_kelola_pengguna.drawio"))


    # ================== PENGGUNA DIAGRAMS (5 File) ==================

    # 6. Registrasi Akun Pelanggan
    b = DrawioDiagramBuilder("Activity Registrasi Pelanggan", "act-usr-reg", 900, 750)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 660)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 660)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Membuka Form Registrasi\ndan Mengisi Data Diri Lengkap", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Memvalidasi Format Username, Sandi,\ndan Ketersediaan Username di DB", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("dec", "Valid & Unik?", b.ST_RHOMBUS, 600, 230, 120, 75)
    b.add_node("s2_err", "Menampilkan Error Validasi\n(Username sudah terdaftar/Data kurang)", b.ST_ACTION, 560, 340, 200, 55)
    b.add_node("s2_ok", "Menyimpan Data Pengguna Baru\ndan Melakukan Hash Password", b.ST_ACTION, 560, 430, 200, 55)
    b.add_node("s3", "Mengarahkan Pelanggan ke Halaman Login", b.ST_ACTION, 560, 520, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 532.5, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 539.5, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec")
    b.link_nodes("dec", "s2_ok", label="Ya")
    b.link_nodes("dec", "s2_err", label="Tidak")
    b.link_nodes("s2_err", "a1", points=[(780, 367.5), (780, 110), (240, 110)])
    b.link_nodes("s2_ok", "s3")
    b.link_nodes("s3", "final")
    b.save(os.path.join(dest_dir, "user_registrasi.drawio"))

    # 7. Login Pelanggan
    b = DrawioDiagramBuilder("Activity Login Pelanggan", "act-usr-login", 900, 650)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 560)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 560)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Mengisi Username & Password pada Form Login", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Memvalidasi Kredensial Pelanggan", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("dec", "Kredensial\nValid?", b.ST_RHOMBUS, 600, 230, 120, 75)
    b.add_node("s2_err", "Menampilkan Error Kredensial Salah", b.ST_ACTION, 560, 340, 200, 55)
    b.add_node("s2_ok", "Mengatur Session Pengguna\ndan Redirect ke Halaman Beranda/Katalog", b.ST_ACTION, 560, 430, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 442.5, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 449.5, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec")
    b.link_nodes("dec", "s2_ok", label="Ya")
    b.link_nodes("dec", "s2_err", label="Tidak")
    b.link_nodes("s2_err", "a1", points=[(780, 367.5), (780, 110), (240, 110)])
    b.link_nodes("s2_ok", "final")
    b.save(os.path.join(dest_dir, "user_login.drawio"))

    # 8. Pencarian & Filter Katalog Produk
    b = DrawioDiagramBuilder("Activity Cari & Filter Katalog", "act-usr-katalog", 900, 700)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 610)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 610)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Mengakses Halaman Katalog & Mengetik\nKeyword Pencarian / Memilih Filter Kategori", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Memproses Query Pencarian di DB\n(Mencari Kategori/Nama Cocok)", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("dec", "Produk Ditemukan?", b.ST_RHOMBUS, 600, 230, 120, 75)
    b.add_node("s2_err", "Menampilkan Keterangan\n'Produk Tidak Ditemukan'", b.ST_ACTION, 560, 340, 200, 55)
    b.add_node("s2_ok", "Menampilkan Grid Produk Sesuai Hasil Filter", b.ST_ACTION, 560, 430, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 442.5, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 449.5, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec")
    b.link_nodes("dec", "s2_ok", label="Ya")
    b.link_nodes("dec", "s2_err", label="Tidak")
    b.link_nodes("s2_err", "final", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(660, 410), (240, 410)])
    b.link_nodes("s2_ok", "final")
    b.save(os.path.join(dest_dir, "user_katalog_search.drawio"))

    # 9. Melakukan Checkout Pemesanan (Pemesanan Madu)
    b = DrawioDiagramBuilder("Activity Checkout Pesanan", "act-usr-checkout", 900, 750)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 660)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 660)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Klik Tombol Checkout pada Keranjang", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Memeriksa Apakah User Sudah Login", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("dec_login", "Sudah Login?", b.ST_RHOMBUS, 600, 220, 120, 75)
    b.add_node("s2_login", "Mengarahkan User ke Halaman Login", b.ST_ACTION, 560, 320, 200, 55)
    b.add_node("a2_form", "Mengisi Form Checkout (Alamat,\nKurir Pengiriman, WhatsApp)", b.ST_ACTION, 140, 420, 200, 55)
    b.add_node("s3_order", "Menyimpan Data Pesanan, Total Harga,\ndan Membersihkan Item Keranjang", b.ST_ACTION, 560, 420, 200, 55)
    b.add_node("s4_invoice", "Menampilkan Halaman Pembayaran dengan\nNominal Unik Transfer Bank", b.ST_ACTION, 560, 520, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 532.5, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 539.5, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "dec_login")
    b.link_nodes("dec_login", "a2_form", label="Ya", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(240, 257.5)])
    b.link_nodes("dec_login", "s2_login", label="Tidak")
    b.link_nodes("s2_login", "a2_form", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(660, 400), (240, 400)])
    b.link_nodes("a2_form", "s3_order")
    b.link_nodes("s3_order", "s4_invoice")
    b.link_nodes("s4_invoice", "final")
    b.save(os.path.join(dest_dir, "user_checkout.drawio"))

    # 10. Upload Bukti Pembayaran
    b = DrawioDiagramBuilder("Activity Upload Bukti Pembayaran", "act-usr-upload", 900, 750)
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 360, 660)
    b.add_node("lane_right", "Sistem", b.ST_SWIMLANE, 480, 40, 360, 660)
    b.add_node("start", "", b.ST_START, 225, 80, 30, 30)
    b.add_node("a1", "Klik Kirim Bukti Pembayaran pada Riwayat", b.ST_ACTION, 140, 140, 200, 55)
    b.add_node("s1", "Menampilkan Halaman Form Upload Bukti", b.ST_ACTION, 560, 140, 200, 55)
    b.add_node("a2", "Memilih File Struk dan Klik Unggah", b.ST_ACTION, 140, 230, 200, 55)
    b.add_node("s2", "Memvalidasi Ekstensi dan Ukuran File", b.ST_ACTION, 560, 230, 200, 55)
    b.add_node("dec", "File Valid?", b.ST_RHOMBUS, 600, 310, 120, 75)
    b.add_node("s3_err", "Menampilkan Pesan Error Validasi File", b.ST_ACTION, 560, 410, 200, 55)
    b.add_node("s3_ok", "Menyimpan Gambar ke Server & Update\nStatus Pesanan ke 'Paid / Pending Review'", b.ST_ACTION, 560, 490, 200, 55)
    b.add_node("s4", "Menampilkan Notifikasi Sukses Upload", b.ST_ACTION, 560, 570, 200, 55)
    b.add_node("final", "", b.ST_END_OUTER, 225, 582.5, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 232, 589.5, 16, 16)
    
    b.link_nodes("start", "a1")
    b.link_nodes("a1", "s1")
    b.link_nodes("s1", "a2")
    b.link_nodes("a2", "s2")
    b.link_nodes("s2", "dec")
    b.link_nodes("dec", "s3_ok", label="Ya")
    b.link_nodes("dec", "s3_err", label="Tidak")
    b.link_nodes("s3_err", "a2", points=[(780, 437.5), (780, 210), (240, 210)])
    b.link_nodes("s3_ok", "s4")
    b.link_nodes("s4", "final")
    b.save(os.path.join(dest_dir, "user_upload_bukti.drawio"))

    print("Successfully generated exactly 11 activity diagrams!")


if __name__ == "__main__":
    import sys
    dest = os.path.join(os.path.dirname(os.path.abspath(__file__)), "activity")
    if len(sys.argv) > 1:
        dest = sys.argv[1]
    generate_activities(dest)

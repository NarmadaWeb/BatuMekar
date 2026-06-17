import os
import xml.etree.ElementTree as ET
import xml.dom.minidom as minidom

class DrawioBuilder:
    # Style constants matching kangkungku program flowcharts
    ST_ENTRY = "shape=offPageConnector;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=2;fontStyle=1;fontSize=11;fontColor=#000000;align=center;"
    ST_EXIT = "shape=offPageConnector;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=2;fontStyle=1;fontSize=11;fontColor=#000000;align=center;"
    ST_PROC = "rounded=0;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;"
    ST_PROC_RECT = "rounded=0;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;"
    ST_INPUT = "shape=parallelogram;perimeter=parallelogramPerimeter;whiteSpace=wrap;html=1;fixedSize=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;"
    ST_OUTPUT = "shape=parallelogram;perimeter=parallelogramPerimeter;whiteSpace=wrap;html=1;fixedSize=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;"
    ST_DEC = "strokeWidth=2;html=1;shape=mxgraph.flowchart.decision;whiteSpace=wrap;fillColor=#FFFFFF;strokeColor=#000000;fontColor=#000000;fontSize=12;fontStyle=1;"
    ST_SWIM = "swimlane;whiteSpace=wrap;html=1;fillColor=none;strokeColor=#000000;strokeWidth=2;fontStyle=1;fontSize=14;fontColor=#000000;align=center;"
    ST_CYL = "shape=cylinder3;whiteSpace=wrap;html=1;boundedLbl=1;backgroundOutline=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;size=10;"
    ST_DOC = "shape=document;whiteSpace=wrap;html=1;boundedLbl=1;backgroundOutline=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;size=0.2;"
    ST_TRAP = "verticalLabelPosition=middle;verticalAlign=middle;html=1;shape=trapezoid;perimeter=trapezoidPerimeter;whiteSpace=wrap;size=0.23;arcSize=10;flipV=1;labelPosition=center;align=center;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;"

    EDGE_BASE = ("edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;"
                 "jettySize=auto;html=1;strokeColor=#000000;strokeWidth=1.5;"
                 "fontColor=#000000;fontSize=11;fontStyle=1;labelBackgroundColor=#FFFFFF;")

    def __init__(self, name, page_id, page_w=850, page_h=1100):
        self.name = name
        self.page_id = page_id
        self.page_w = page_w
        self.page_h = page_h
        self.counter = 0
        self.nodes = []
        self.edges = []

    def _next_id(self, prefix):
        self.counter += 1
        return f"{prefix}{self.counter}"

    def add(self, nid, label, kind, x, y, w=None, h=None, parent="1"):
        if kind == "entry":
            w = w or 80
            h = h or 80
            style = self.ST_ENTRY
        elif kind == "exit":
            w = w or 80
            h = h or 80
            style = self.ST_EXIT
        elif kind == "process":
            w = w or 160
            h = h or 55
            style = self.ST_PROC
        elif kind == "process_rect":
            w = w or 160
            h = h or 55
            style = self.ST_PROC_RECT
        elif kind == "input":
            w = w or 175
            h = h or 55
            style = self.ST_INPUT
        elif kind == "output":
            w = w or 175
            h = h or 55
            style = self.ST_OUTPUT
        elif kind == "decision":
            w = w or 110
            h = h or 80
            style = self.ST_DEC
        elif kind == "swimlane":
            w = w or 400
            h = h or 1000
            style = self.ST_SWIM
        elif kind == "cylinder":
            w = w or 140
            h = h or 60
            style = self.ST_CYL
        elif kind == "document":
            w = w or 190
            h = h or 70
            style = self.ST_DOC
        elif kind == "trapezoid":
            w = w or 180
            h = h or 60
            style = self.ST_TRAP
        else:
            w = w or 150
            h = h or 50
            style = self.ST_PROC
        self.nodes.append({
            "id": nid, "value": label, "style": style,
            "x": x, "y": y, "w": w, "h": h, "parent": parent
        })

    def link(self, src, dst, label="", exit_pt=None, entry_pt=None, points=None):
        eid = self._next_id("e")
        style = self.EDGE_BASE
        if exit_pt:
            style += f"exitX={exit_pt[0]};exitY={exit_pt[1]};exitDx=0;exitDy=0;"
        if entry_pt:
            style += f"entryX={entry_pt[0]};entryY={entry_pt[1]};entryDx=0;entryDy=0;"
        
        self.edges.append({
            "id": eid, "value": label, "style": style,
            "source": src, "target": dst, "points": points or [],
        })

    def save(self, filepath):
        mxfile = ET.Element("mxfile", host="Electron", version="21.6.8",
                            modified="2026-06-16T12:00:00.000Z",
                            agent="Mozilla/5.0", type="device")
        diagram = ET.SubElement(mxfile, "diagram", id=self.page_id, name=self.name)
        model = ET.SubElement(diagram, "mxGraphModel", 
                               dx="1000", dy="1000", grid="1", gridSize="10",
                               guides="1", tooltips="1", connect="1", arrows="1",
                               fold="1", page="1", pageScale="1",
                               pageWidth=str(self.page_w), pageHeight=str(self.page_h),
                               math="0", shadow="0")
        root = ET.SubElement(model, "root")
        
        ET.SubElement(root, "mxCell", id="0")
        ET.SubElement(root, "mxCell", id="1", parent="0")

        # Build Vertices
        for n in self.nodes:
            cell = ET.SubElement(root, "mxCell", id=n["id"], value=n["value"],
                                 style=n["style"], vertex="1", parent=n["parent"])
            geom = ET.SubElement(cell, "mxGeometry",
                                 x=str(n["x"]), y=str(n["y"]),
                                 width=str(n["w"]), height=str(n["h"]))
            geom.set("as", "geometry")

        # Build Edges
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
                                   width=str(self.page_w), height=str(self.page_h))
        geom_frame.set("as", "geometry")

        # Format XML (Pretty Print)
        raw = ET.tostring(mxfile, encoding="utf-8")
        dom = minidom.parseString(raw)
        pretty_xml = dom.toprettyxml(indent="  ")
        if pretty_xml.startswith("<?xml"):
            pretty_xml = pretty_xml.split("\n", 1)[1]
        out_content = '<?xml version="1.0" encoding="UTF-8"?>\n' + pretty_xml

        os.makedirs(os.path.dirname(filepath), exist_ok=True)
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(out_content)
        print(f"Generated Program Flowchart Draw.io XML at: {filepath}")


def build_pelanggan_registrasi(dest_dir):
    b = DrawioBuilder("Registrasi Pelanggan", "page-reg", 580, 950)
    
    b.add("entry", "Halaman Registrasi", "entry", 250, 40)
    b.add("input_data", "Input Nama, Username,\nPassword, Telepon, Alamat", "input", 202.5, 150)
    b.add("validate", "Validasi format input\ndan kelengkapan data", "process", 210, 240)
    b.add("dec_valid", "Format valid?", "decision", 235, 330)
    
    b.add("show_err_format", "Tampilkan error\nformat tidak valid", "document", 40, 342.5)
    
    b.add("check_username", "Cek username di database", "process_rect", 210, 450)
    b.add("dec_exist", "Username sudah\nterdaftar?", "decision", 235, 540)
    
    b.add("show_err_exist", "Tampilkan error\nusername sudah digunakan", "document", 40, 552.5)
    
    b.add("save_db", "Hash password & simpan\nke tabel pengguna", "process_rect", 210, 660)
    b.add("success_msg", "Tampilkan pesan sukses\ndan redirect ke login", "document", 210, 750)
    b.add("exit", "Halaman Login", "exit", 250, 850)
 
    # Connections
    b.link("entry", "input_data")
    b.link("input_data", "validate")
    b.link("validate", "dec_valid")
    
    b.link("dec_valid", "check_username", label="Ya")
    b.link("dec_valid", "show_err_format", label="Tidak", exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    
    # Format error loops back via x=20
    b.link("show_err_format", "input_data", exit_pt=(0, 0.5), entry_pt=(0, 0.5), points=[(20, 370), (20, 177.5)])
    
    b.link("check_username", "dec_exist")
    b.link("dec_exist", "save_db", label="Tidak")
    b.link("dec_exist", "show_err_exist", label="Ya", exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    
    # Exist error loops back via x=10 to avoid overlap with format error line at x=20
    b.link("show_err_exist", "input_data", exit_pt=(0, 0.5), entry_pt=(0, 0.5), points=[(10, 580), (10, 177.5)])
    
    b.link("save_db", "success_msg")
    b.link("success_msg", "exit")
    
    b.save(os.path.join(dest_dir, "pelanggan", "03_pelanggan_registrasi.drawio"))
 
 
def build_pelanggan_login(dest_dir):
    b = DrawioBuilder("Login Pelanggan", "page-login", 580, 850)
    
    b.add("entry", "Halaman Login", "entry", 250, 40)
    b.add("input_data", "Input Username & Password", "input", 202.5, 150)
    b.add("query_db", "Query username & verify\npassword hash di DB", "process_rect", 210, 240)
    b.add("dec_found", "Akun ditemukan & Match?", "decision", 235, 330)
    
    b.add("show_err", "Tampilkan error\nusername/password salah", "document", 40, 342.5)
    
    b.add("dec_role", "Apakah peran\nadalah Admin?", "decision", 235, 450)
    b.add("redir_admin", "Redirect ke Halaman\nDashboard Admin", "process", 380, 570)
    b.add("redir_user", "Redirect ke Halaman\nUtama Pelanggan", "process", 210, 570)
    b.add("exit", "Halaman Beranda", "exit", 250, 730)
 
    # Connections
    b.link("entry", "input_data")
    b.link("input_data", "query_db")
    b.link("query_db", "dec_found")
    
    b.link("dec_found", "dec_role", label="Ya")
    b.link("dec_found", "show_err", label="Tidak", exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    b.link("show_err", "input_data", exit_pt=(0, 0.5), entry_pt=(0, 0.5), points=[(20, 370), (20, 177.5)])
    
    b.link("dec_role", "redir_user", label="Tidak")
    b.link("dec_role", "redir_admin", label="Ya", exit_pt=(1, 0.5), entry_pt=(0.5, 0), points=[(460, 490)])
    
    b.link("redir_user", "exit")
    b.link("redir_admin", "exit", exit_pt=(0.5, 1), entry_pt=(1, 0.5), points=[(460, 770)])
    
    b.save(os.path.join(dest_dir, "pelanggan", "04_pelanggan_login.drawio"))


def build_katalog_pencarian(dest_dir):
    b = DrawioBuilder("Katalog & Pencarian", "page-katalog", 580, 800)
    
    b.add("entry", "Halaman Katalog", "entry", 250, 40)
    b.add("open_katalog", "Buka Halaman Katalog", "process", 210, 150)
    b.add("dec_search", "Cari produk / filter?", "decision", 235, 240)
    
    b.add("query_all", "Query semua produk\naktif dari DB", "process_rect", 60, 360)
    b.add("query_filter", "Query produk filter\nkeyword/kategori", "process_rect", 360, 360)
    
    b.add("dec_found", "Produk ditemukan?", "decision", 235, 470)
    
    b.add("show_empty", "Tampilkan pesan\n'Produk Tidak Ditemukan'", "document", 60, 580)
    b.add("show_list", "Tampilkan daftar\nproduk madu", "document", 360, 580)
    b.add("exit", "Daftar Produk", "exit", 250, 700)

    # Connections
    b.link("entry", "open_katalog")
    b.link("open_katalog", "dec_search")
    
    b.link("dec_search", "query_filter", label="Ya", exit_pt=(1, 0.5), entry_pt=(0.5, 0), points=[(440, 280)])
    b.link("dec_search", "query_all", label="Tidak", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(140, 280)])
    
    b.link("query_all", "dec_found", exit_pt=(0.5, 1), entry_pt=(0, 0.5), points=[(140, 510)])
    b.link("query_filter", "dec_found", exit_pt=(0.5, 1), entry_pt=(1, 0.5), points=[(440, 510)])
    
    b.link("dec_found", "show_list", label="Ya", exit_pt=(1, 0.5), entry_pt=(0.5, 0), points=[(440, 510)])
    b.link("dec_found", "show_empty", label="Tidak", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(140, 510)])
    
    b.link("show_empty", "exit", exit_pt=(0.5, 1), entry_pt=(0, 0.5), points=[(140, 740)])
    b.link("show_list", "exit", exit_pt=(0.5, 1), entry_pt=(1, 0.5), points=[(440, 740)])
    
    b.save(os.path.join(dest_dir, "pelanggan", "05_katalog_pencarian.drawio"))


def build_tambah_keranjang(dest_dir):
    b = DrawioBuilder("Tambah Keranjang", "page-cart", 580, 850)
    
    b.add("entry", "Halaman Detail Produk", "entry", 250, 40)
    b.add("click_add", "Klik 'Tambah ke Keranjang'", "trapezoid", 200, 150)
    b.add("check_stock", "Cek stok di database", "process_rect", 210, 240)
    b.add("dec_stock", "Apakah stok > 0?", "decision", 235, 330)
    
    b.add("show_empty", "Tampilkan error\n'Stok Tidak Cukup'", "document", 40, 342.5)
    
    b.add("dec_in_cart", "Item sudah ada\ndi keranjang?", "decision", 235, 450)
    
    b.add("add_qty", "Kuantitas item\ndi keranjang + 1", "process", 100, 560)
    b.add("insert_new", "Masukkan item baru\nke keranjang session", "process", 320, 560)
    
    b.add("update_total", "Hitung kembali subtotal\ndan total belanja", "process", 210, 670)
    b.add("exit", "Halaman Keranjang", "exit", 250, 760)

    # Connections
    b.link("entry", "click_add")
    b.link("click_add", "check_stock")
    b.link("check_stock", "dec_stock")
    
    b.link("dec_stock", "dec_in_cart", label="Ya")
    b.link("dec_stock", "show_empty", label="Tidak", exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    
    # Error line goes all the way around to avoid overlap
    b.link("show_empty", "exit", exit_pt=(0, 0.5), entry_pt=(0, 0.5), points=[(20, 370), (20, 800)])
    
    b.link("dec_in_cart", "add_qty", label="Ya", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(180, 490)])
    b.link("dec_in_cart", "insert_new", label="Tidak", exit_pt=(1, 0.5), entry_pt=(0.5, 0), points=[(400, 490)])
    
    b.link("add_qty", "update_total", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(180, 640), (290, 640)])
    b.link("insert_new", "update_total", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(400, 640), (290, 640)])
    
    b.link("update_total", "exit")
    
    b.save(os.path.join(dest_dir, "pelanggan", "06_tambah_keranjang.drawio"))


def build_checkout_pesanan(dest_dir):
    b = DrawioBuilder("Checkout Pesanan", "page-checkout", 580, 850)
    
    b.add("entry", "Halaman Keranjang", "entry", 250, 40)
    b.add("open_page", "Buka Halaman Checkout", "process", 210, 150)
    b.add("input_data", "Input Nama Penerima,\nTelepon, Alamat Lengkap", "input", 202.5, 240)
    b.add("validate", "Validasi kelengkapan data", "process", 210, 330)
    b.add("dec_valid", "Data Lengkap?", "decision", 235, 420)
    
    b.add("show_err", "Tampilkan error\nformulir belum lengkap", "document", 40, 432.5)
    
    b.add("save_order", "Simpan data ke tabel pesanan\nstatus = 'Belum Bayar'", "process_rect", 210, 540)
    b.add("clear_cart", "Kosongkan keranjang belanja", "process", 210, 630)
    b.add("redir_pay", "Redirect ke Halaman Pembayaran", "process", 210, 720)
    b.add("exit", "Halaman Pembayaran", "exit", 250, 800)

    # Connections
    b.link("entry", "open_page")
    b.link("open_page", "input_data")
    b.link("input_data", "validate")
    b.link("validate", "dec_valid")
    
    b.link("dec_valid", "save_order", label="Ya")
    b.link("dec_valid", "show_err", label="Tidak", exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    b.link("show_err", "input_data", exit_pt=(0, 0.5), entry_pt=(0, 0.5), points=[(20, 460), (20, 267.5)])
    
    b.link("save_order", "clear_cart")
    b.link("clear_cart", "redir_pay")
    b.link("redir_pay", "exit")
    
    b.save(os.path.join(dest_dir, "pelanggan", "07_checkout_pesanan.drawio"))


def build_upload_bukti_bayar(dest_dir):
    b = DrawioBuilder("Upload Bukti", "page-upload", 580, 850)
    
    b.add("entry", "Halaman Pembayaran", "entry", 250, 40)
    b.add("open_pay", "Pilih pesanan di Halaman\nPembayaran & Klik Upload", "trapezoid", 200, 150)
    b.add("upload_file", "Unggah berkas bukti transfer", "input", 202.5, 240)
    b.add("validate_file", "Validasi format (JPG/PNG)\ndan ukuran (< 2MB)", "process", 210, 330)
    b.add("dec_valid", "Berkas Valid?", "decision", 235, 420)
    
    b.add("show_err", "Tampilkan error\nberkas tidak valid", "document", 40, 432.5)
    
    b.add("save_file", "Simpan berkas gambar ke\nfolder assets/uploads/", "process_rect", 210, 540)
    b.add("update_db", "Update field bukti_pembayaran\ndan status = 'Sudah Bayar'", "process_rect", 210, 630)
    b.add("show_success", "Tampilkan konfirmasi sukses\npembayaran dalam verifikasi", "document", 210, 720)
    b.add("exit", "Halaman Riwayat Pesanan", "exit", 250, 810)

    # Connections
    b.link("entry", "open_pay")
    b.link("open_pay", "upload_file")
    b.link("upload_file", "validate_file")
    b.link("validate_file", "dec_valid")
    
    b.link("dec_valid", "save_file", label="Ya")
    b.link("dec_valid", "show_err", label="Tidak", exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    b.link("show_err", "upload_file", exit_pt=(0, 0.5), entry_pt=(0, 0.5), points=[(20, 460), (20, 267.5)])
    
    b.link("save_file", "update_db")
    b.link("update_db", "show_success")
    b.link("show_success", "exit")
    
    b.save(os.path.join(dest_dir, "pelanggan", "08_upload_bukti_bayar.drawio"))


def build_admin_kelola_produk(dest_dir):
    b = DrawioBuilder("Kelola Produk Admin", "page-admprod", 650, 900)
    
    b.add("entry", "Dashboard Admin", "entry", 285, 40)
    b.add("select_action", "Pilih Aksi\n(Tambah / Edit / Hapus)", "input", 237.5, 150)
    b.add("dec_action", "Jenis Aksi?", "decision", 270, 240)
    
    b.add("input_add", "Input nama, deskripsi,\nharga, kategori, stok,\ndan unggah gambar produk", "input", 40, 360)
    b.add("input_edit", "Ubah data produk terkait\ndan unggah gambar baru (opsional)", "input", 237.5, 360)
    b.add("confirm_delete", "Konfirmasi penghapusan produk", "input", 435, 360)
    
    b.add("execute_db", "Eksekusi SQL Query\n(INSERT / UPDATE / DELETE)", "process_rect", 245, 500)
    b.add("update_list", "Tampilkan daftar produk terupdate", "document", 245, 590)
    b.add("exit", "Halaman Daftar Produk", "exit", 285, 700)

    # Connections
    b.link("entry", "select_action")
    b.link("select_action", "dec_action")
    
    b.link("dec_action", "input_add", label="Tambah", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(127.5, 280)])
    b.link("dec_action", "input_edit", label="Edit", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link("dec_action", "confirm_delete", label="Hapus", exit_pt=(1, 0.5), entry_pt=(0.5, 0), points=[(522.5, 280)])
    
    # Merge all 3 execute_db inputs at (325, 460) to avoid overlapping
    b.link("input_add", "execute_db", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(127.5, 460), (325, 460)])
    b.link("input_edit", "execute_db", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link("confirm_delete", "execute_db", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(522.5, 460), (325, 460)])
    
    b.link("execute_db", "update_list")
    b.link("update_list", "exit")
    
    b.save(os.path.join(dest_dir, "admin", "09_admin_kelola_produk.drawio"))


def build_admin_verifikasi_pembayaran(dest_dir):
    b = DrawioBuilder("Verifikasi Pembayaran", "page-admver", 580, 950)
    
    b.add("entry", "Halaman Pesanan Admin", "entry", 250, 40)
    b.add("view_orders", "Buka Halaman Pesanan Masuk", "process", 210, 150)
    b.add("check_proof", "Periksa gambar bukti transfer bank", "trapezoid", 200, 240)
    b.add("dec_valid", "Bukti valid & nominal sesuai?", "decision", 235, 330)
    
    b.add("reject_payment", "Tolak pembayaran & ubah status\npesanan kembali ke 'Belum Bayar'", "process_rect", 40, 450)
    b.add("approve_payment", "Setujui pembayaran & ubah status\npesanan menjadi 'Di Proses'", "process_rect", 300, 450)
    b.add("deduct_stock", "Kurangi stok produk secara\notomatis di database", "process_rect", 300, 560)
    b.add("send_package", "Kemas produk madu & kirim ke alamat", "process", 300, 670)
    b.add("update_done", "Ubah status pesanan menjadi 'Selesai'", "process_rect", 300, 760)
    b.add("exit", "Halaman Detail Pesanan", "exit", 250, 860)

    # Connections
    b.link("entry", "view_orders")
    b.link("view_orders", "check_proof")
    b.link("check_proof", "dec_valid")
    
    b.link("dec_valid", "approve_payment", label="Ya", exit_pt=(1, 0.5), entry_pt=(0.5, 0), points=[(380, 370)])
    b.link("dec_valid", "reject_payment", label="Tidak", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(120, 370)])
    
    # Reject goes to left of exit, Approve path goes to right of exit, ensuring clean, non-overlapping routing
    b.link("reject_payment", "exit", exit_pt=(0.5, 1), entry_pt=(0, 0.5), points=[(120, 900)])
    b.link("approve_payment", "deduct_stock")
    b.link("deduct_stock", "send_package")
    b.link("send_package", "update_done")
    b.link("update_done", "exit", exit_pt=(0.5, 1), entry_pt=(1, 0.5), points=[(380, 900)])
    
    b.save(os.path.join(dest_dir, "admin", "10_admin_verifikasi_pembayaran.drawio"))


def build_admin_kelola_kategori(dest_dir):
    b = DrawioBuilder("Kelola Kategori Admin", "page-admcat", 650, 900)
    
    b.add("entry", "Dashboard Admin", "entry", 285, 40)
    b.add("select_action", "Pilih Aksi\n(Tambah / Edit / Hapus)", "input", 237.5, 150)
    b.add("dec_action", "Jenis Aksi?", "decision", 270, 240)
    
    b.add("input_add", "Input nama kategori baru\ndan deskripsi kategori", "input", 40, 360)
    b.add("input_edit", "Ubah nama kategori terkait\ndan deskripsi kategori", "input", 237.5, 360)
    b.add("confirm_delete", "Konfirmasi penghapusan kategori", "input", 435, 360)
    
    b.add("execute_db", "Eksekusi SQL Query\n(INSERT / UPDATE / DELETE)", "process_rect", 245, 500)
    b.add("update_list", "Tampilkan daftar kategori terupdate", "document", 245, 590)
    b.add("exit", "Halaman Daftar Kategori", "exit", 285, 700)

    # Connections
    b.link("entry", "select_action")
    b.link("select_action", "dec_action")
    
    b.link("dec_action", "input_add", label="Tambah", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(127.5, 280)])
    b.link("dec_action", "input_edit", label="Edit", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link("dec_action", "confirm_delete", label="Hapus", exit_pt=(1, 0.5), entry_pt=(0.5, 0), points=[(522.5, 280)])
    
    # Merge all 3 execute_db inputs at (325, 460) to avoid overlapping
    b.link("input_add", "execute_db", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(127.5, 460), (325, 460)])
    b.link("input_edit", "execute_db", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link("confirm_delete", "execute_db", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(522.5, 460), (325, 460)])
    
    b.link("execute_db", "update_list")
    b.link("update_list", "exit")
    
    b.save(os.path.join(dest_dir, "admin", "11_admin_kelola_kategori.drawio"))


def build_admin_kelola_artikel(dest_dir):
    b = DrawioBuilder("Kelola Artikel Admin", "page-admart", 650, 900)
    
    b.add("entry", "Dashboard Admin", "entry", 285, 40)
    b.add("select_action", "Pilih Aksi\n(Tambah / Edit / Hapus)", "input", 237.5, 150)
    b.add("dec_action", "Jenis Aksi?", "decision", 270, 240)
    
    b.add("input_add", "Input judul, kutipan, konten,\npenulis, dan unggah gambar artikel", "input", 40, 360)
    b.add("input_edit", "Ubah data artikel terkait\ndan unggah gambar baru (opsional)", "input", 237.5, 360)
    b.add("confirm_delete", "Konfirmasi penghapusan artikel", "input", 435, 360)
    
    b.add("execute_db", "Eksekusi SQL Query\n(INSERT / UPDATE / DELETE)", "process_rect", 245, 500)
    b.add("update_list", "Tampilkan daftar artikel terupdate", "document", 245, 590)
    b.add("exit", "Halaman Daftar Artikel", "exit", 285, 700)

    # Connections
    b.link("entry", "select_action")
    b.link("select_action", "dec_action")
    
    b.link("dec_action", "input_add", label="Tambah", exit_pt=(0, 0.5), entry_pt=(0.5, 0), points=[(127.5, 280)])
    b.link("dec_action", "input_edit", label="Edit", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link("dec_action", "confirm_delete", label="Hapus", exit_pt=(1, 0.5), entry_pt=(0.5, 0), points=[(522.5, 280)])
    
    # Merge all 3 execute_db inputs at (325, 460) to avoid overlapping
    b.link("input_add", "execute_db", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(127.5, 460), (325, 460)])
    b.link("input_edit", "execute_db", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link("confirm_delete", "execute_db", exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(522.5, 460), (325, 460)])
    
    b.link("execute_db", "update_list")
    b.link("update_list", "exit")
    
    b.save(os.path.join(dest_dir, "admin", "12_admin_kelola_artikel.drawio"))


def build_all():
    dest = os.path.join(os.path.dirname(os.path.abspath(__file__)), "flowchart")
    print(f"Generating all modular program flowcharts inside {dest}...")
    
    build_pelanggan_registrasi(dest)
    build_pelanggan_login(dest)
    build_katalog_pencarian(dest)
    build_tambah_keranjang(dest)
    build_checkout_pesanan(dest)
    build_upload_bukti_bayar(dest)
    
    build_admin_kelola_produk(dest)
    build_admin_verifikasi_pembayaran(dest)
    build_admin_kelola_kategori(dest)
    build_admin_kelola_artikel(dest)
    
    print("All 10 modular program flowcharts generated successfully!")

if __name__ == "__main__":
    build_all()

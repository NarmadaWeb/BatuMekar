import os
import xml.etree.ElementTree as ET
import xml.dom.minidom as minidom

class DrawioDiagramBuilder:
    """Builder for academic diagrams (Use Case, ERD, Activity, Architecture) in Draw.io format."""
    
    # Styles
    ST_FRAME_DASHED = "rounded=0;whiteSpace=wrap;html=1;fillColor=none;strokeColor=#000000;strokeWidth=1.5;dashed=1;fontSize=14;fontStyle=1;fontColor=#000000;align=center;verticalAlign=top;"
    ST_FRAME_SOLID = "rounded=0;whiteSpace=wrap;html=1;fillColor=none;strokeColor=#000000;strokeWidth=1.5;fontSize=14;fontStyle=1;fontColor=#000000;align=center;verticalAlign=top;"
    ST_ACTOR = "shape=umlActor;verticalLabelPosition=bottom;verticalAlign=top;html=1;outlineConnect=0;strokeColor=#000000;fillColor=none;strokeWidth=1.5;align=center;fontColor=#000000;fontFamily=Helvetica;fontSize=13;fontStyle=1;"
    ST_USECASE = "ellipse;whiteSpace=wrap;html=1;strokeColor=#000000;fillColor=#FFFFFF;strokeWidth=1.5;align=center;fontColor=#000000;fontFamily=Helvetica;fontSize=12;"
    ST_TABLE = "rounded=0;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;align=left;verticalAlign=top;spacingLeft=8;spacingRight=8;spacingTop=6;spacingBottom=6;fontColor=#000000;fontSize=12;fontFamily=Helvetica;"
    ST_SWIMLANE = "swimlane;startSize=30;html=1;whiteSpace=wrap;collapsible=0;connectable=0;container=1;pointerEvents=0;fillColor=none;strokeColor=#000000;fontStyle=1;align=center;fontSize=13;fontColor=#000000;strokeWidth=1.5;"
    ST_START = "ellipse;html=1;fillColor=#000000;strokeColor=none;aspect=fixed;"
    ST_END_OUTER = "ellipse;html=1;fillColor=none;strokeColor=#000000;strokeWidth=2;aspect=fixed;"
    ST_END_INNER = "ellipse;html=1;fillColor=#000000;strokeColor=none;aspect=fixed;"
    ST_ACTION = "rounded=1;whiteSpace=wrap;html=1;arcSize=20;fillColor=#FFFFFF;strokeColor=#000000;fontSize=12;fontColor=#000000;align=center;verticalAlign=middle;strokeWidth=1.5;"
    ST_RHOMBUS = "rhombus;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;fontSize=12;fontColor=#000000;align=center;verticalAlign=middle;strokeWidth=1.5;"
    ST_CYLINDER = "shape=cylinder3;whiteSpace=wrap;html=1;boundedLbl=1;backgroundOutline=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;size=10;"
    ST_BOX = "rounded=0;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;align=center;verticalAlign=middle;"
    ST_TEXT = "text;html=1;align=center;verticalAlign=middle;whiteSpace=wrap;rounded=0;fontColor=#000000;fontSize=12;"

    # Connectors
    EDGE_ASSOC = "endArrow=none;html=1;rounded=0;strokeColor=#000000;strokeWidth=1.2;"
    EDGE_INCLUDE = "endArrow=open;endSize=12;dashed=1;html=1;rounded=0;strokeColor=#000000;strokeWidth=1.2;"
    EDGE_ORTHO = "edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#000000;strokeWidth=1.5;fontSize=12;labelBackgroundColor=#FFFFFF;fontColor=#000000;"
    EDGE_ERD = "edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#000000;strokeWidth=1.2;fontColor=#000000;fontSize=12;startArrow=ERone;endArrow=ERmany;startSize=10;endSize=10;"

    def __init__(self, name, page_id, width=1100, height=900):
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
        edge_style = style
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


def generate_usecase(dest_dir):
    print("Generating Use Case Diagram...")
    b = DrawioDiagramBuilder("Use Case Diagram", "uc-batumekar", width=1200, height=950)
    
    # Background frame / System boundary
    b.add_node("frame", "Sistem E-Commerce Madu Hutan BatuMekar", b.ST_FRAME_DASHED, 220, 30, 750, 880)
    
    # Actors
    b.add_node("act_admin", "Admin", b.ST_ACTOR, 80, 420, 40, 60)
    b.add_node("act_user", "Pelanggan", b.ST_ACTOR, 1070, 420, 40, 60)

    # Use Cases
    # Auth and Shared
    b.add_node("uc_login", "Login / Masuk", b.ST_USECASE, 510, 410, 150, 50)
    b.add_node("uc_logout", "Logout / Keluar", b.ST_USECASE, 510, 490, 150, 50)
    b.add_node("uc_register", "Daftar Akun Baru", b.ST_USECASE, 740, 90, 170, 50)

    # Pelanggan Use Cases
    b.add_node("uc_katalog", "Lihat Katalog Produk", b.ST_USECASE, 740, 160, 170, 50)
    b.add_node("uc_detail", "Lihat Detail Produk", b.ST_USECASE, 740, 230, 170, 50)
    b.add_node("uc_keranjang", "Kelola Keranjang Belanja", b.ST_USECASE, 740, 300, 170, 50)
    b.add_node("uc_checkout", "Melakukan Checkout", b.ST_USECASE, 740, 380, 170, 50)
    b.add_node("uc_pembayaran", "Konfirmasi Pembayaran", b.ST_USECASE, 740, 460, 170, 50)
    b.add_node("uc_riwayat", "Lihat Riwayat Pesanan", b.ST_USECASE, 740, 540, 170, 50)
    b.add_node("uc_profil", "Kelola Profil Akun", b.ST_USECASE, 740, 620, 170, 50)
    b.add_node("uc_kalender", "Lihat Kalender Panen", b.ST_USECASE, 740, 700, 170, 50)
    b.add_node("uc_keberlanjutan", "Lihat Edukasi Lestari", b.ST_USECASE, 740, 780, 170, 50)

    # Admin Use Cases
    b.add_node("uc_dash_admin", "Lihat Dashboard Admin", b.ST_USECASE, 280, 120, 170, 50)
    b.add_node("uc_kelola_prod", "Kelola Data Produk (CRUD)", b.ST_USECASE, 280, 200, 170, 50)
    b.add_node("uc_kelola_kat", "Kelola Data Kategori (CRUD)", b.ST_USECASE, 280, 280, 170, 50)
    b.add_node("uc_kelola_pes", "Kelola Data Pesanan (CRUD)", b.ST_USECASE, 280, 360, 170, 50)
    b.add_node("uc_kelola_art", "Kelola Artikel Blog (CRUD)", b.ST_USECASE, 280, 440, 170, 50)
    b.add_node("uc_kelola_pengguna", "Kelola Data Pengguna (CRUD)", b.ST_USECASE, 280, 520, 170, 50)
    
    # Links Actor -> Use Case
    # Admin links
    b.link_nodes("act_admin", "uc_login", style=b.EDGE_ASSOC)
    b.link_nodes("act_admin", "uc_logout", style=b.EDGE_ASSOC)
    b.link_nodes("act_admin", "uc_dash_admin", style=b.EDGE_ASSOC)
    b.link_nodes("act_admin", "uc_kelola_prod", style=b.EDGE_ASSOC)
    b.link_nodes("act_admin", "uc_kelola_kat", style=b.EDGE_ASSOC)
    b.link_nodes("act_admin", "uc_kelola_pes", style=b.EDGE_ASSOC)
    b.link_nodes("act_admin", "uc_kelola_art", style=b.EDGE_ASSOC)
    b.link_nodes("act_admin", "uc_kelola_pengguna", style=b.EDGE_ASSOC)

    # Pelanggan links
    b.link_nodes("act_user", "uc_register", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_login", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_logout", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_katalog", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_detail", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_keranjang", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_checkout", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_pembayaran", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_riwayat", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_profil", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_kalender", style=b.EDGE_ASSOC)
    b.link_nodes("act_user", "uc_keberlanjutan", style=b.EDGE_ASSOC)

    # Includes
    incl_label = "&lt;&lt;include&gt;&gt;"
    b.link_nodes("uc_checkout", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)
    b.link_nodes("uc_pembayaran", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)
    b.link_nodes("uc_riwayat", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)
    b.link_nodes("uc_profil", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)
    b.link_nodes("uc_dash_admin", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)
    b.link_nodes("uc_kelola_prod", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)
    b.link_nodes("uc_kelola_kat", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)
    b.link_nodes("uc_kelola_pes", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)
    b.link_nodes("uc_kelola_art", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)
    b.link_nodes("uc_kelola_pengguna", "uc_login", label=incl_label, style=b.EDGE_INCLUDE)

    b.save(os.path.join(dest_dir, "usecase", "usecase.drawio"))


def generate_erd(dest_dir):
    print("Generating ERD Diagram...")
    b = DrawioDiagramBuilder("ERD", "erd-batumekar", width=1200, height=950)
    
    # Table 1: pengguna
    t_pengguna = (
        '<div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000000; padding-bottom: 4px; margin-bottom: 6px; font-size: 13px;">pengguna</div>'
        '<b><u>pengguna_id</u></b> : INT AUTO_INCREMENT<br/>'
        'nama : VARCHAR(35)<br/>'
        'username : VARCHAR(30) UNIQUE<br/>'
        'password : VARCHAR(64)<br/>'
        'peran : VARCHAR(10)<br/>'
        'telepon : VARCHAR(15)<br/>'
        'alamat : TEXT<br/>'
        'foto_profil : VARCHAR(50)<br/>'
        'dibuat_pada : TIMESTAMP'
    )
    b.add_node("tbl_pengguna", t_pengguna, b.ST_TABLE, 80, 60, 240, 190)

    # Table 2: kategori
    t_kategori = (
        '<div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000000; padding-bottom: 4px; margin-bottom: 6px; font-size: 13px;">kategori</div>'
        '<b><u>kategori_id</u></b> : INT AUTO_INCREMENT<br/>'
        'nama_kategori : VARCHAR(30)<br/>'
        'deskripsi : TEXT<br/>'
        'dibuat_pada : TIMESTAMP'
    )
    b.add_node("tbl_kategori", t_kategori, b.ST_TABLE, 480, 60, 220, 110)

    # Table 3: produk
    t_produk = (
        '<div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000000; padding-bottom: 4px; margin-bottom: 6px; font-size: 13px;">produk</div>'
        '<b><u>produk_id</u></b> : INT AUTO_INCREMENT<br/>'
        'nama : VARCHAR(35)<br/>'
        'deskripsi : TEXT<br/>'
        'harga : DECIMAL(10, 2)<br/>'
        'gambar : VARCHAR(50)<br/>'
        'kategori : VARCHAR(30)<br/>'
        'rating : DECIMAL(3, 1)<br/>'
        'jumlah_ulasan : INT<br/>'
        'stok : INT<br/>'
        'unggulan : TINYINT<br/>'
        'dibuat_pada : TIMESTAMP'
    )
    b.add_node("tbl_produk", t_produk, b.ST_TABLE, 850, 60, 250, 220)

    # Table 4: pesanan
    t_pesanan = (
        '<div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000000; padding-bottom: 4px; margin-bottom: 6px; font-size: 13px;">pesanan</div>'
        '<b><u>pesanan_id</u></b> : INT AUTO_INCREMENT<br/>'
        '<i>pengguna_id</i> : INT (FK)<br/>'
        'total_harga : DECIMAL(10, 2)<br/>'
        'status : VARCHAR(15)<br/>'
        'metode_pengiriman : VARCHAR(20)<br/>'
        'metode_pembayaran : VARCHAR(20)<br/>'
        'alamat_pengiriman : TEXT<br/>'
        'bukti_pembayaran : VARCHAR(50)<br/>'
        'dibuat_pada : TIMESTAMP'
    )
    b.add_node("tbl_pesanan", t_pesanan, b.ST_TABLE, 80, 360, 260, 200)

    # Table 5: detail_pesanan
    t_detail = (
        '<div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000000; padding-bottom: 4px; margin-bottom: 6px; font-size: 13px;">detail_pesanan</div>'
        '<b><u>detail_pesanan_id</u></b> : INT AUTO_INCREMENT<br/>'
        '<i>pesanan_id</i> : INT (FK)<br/>'
        '<i>produk_id</i> : INT (FK)<br/>'
        'jumlah : INT<br/>'
        'harga : DECIMAL(10, 2)'
    )
    b.add_node("tbl_detail", t_detail, b.ST_TABLE, 850, 360, 250, 130)

    # Table 6: pembayaran
    t_pembayaran = (
        '<div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000000; padding-bottom: 4px; margin-bottom: 6px; font-size: 13px;">pembayaran</div>'
        '<b><u>pembayaran_id</u></b> : INT AUTO_INCREMENT<br/>'
        '<i>pesanan_id</i> : INT (FK)<br/>'
        '<i>pengguna_id</i> : INT (FK)<br/>'
        'transaksi_id : VARCHAR(50)<br/>'
        'tanggal_pembayaran : TIMESTAMP'
    )
    b.add_node("tbl_pembayaran", t_pembayaran, b.ST_TABLE, 480, 360, 220, 130)

    # Table 7: notifikasi
    t_notif = (
        '<div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000000; padding-bottom: 4px; margin-bottom: 6px; font-size: 13px;">notifikasi</div>'
        '<b><u>notifikasi_id</u></b> : INT AUTO_INCREMENT<br/>'
        '<i>pesanan_id</i> : INT (FK)<br/>'
        'judul : VARCHAR(100)<br/>'
        'pesan : TEXT<br/>'
        'dibaca : TINYINT<br/>'
        'dibuat_pada : TIMESTAMP'
    )
    b.add_node("tbl_notif", t_notif, b.ST_TABLE, 80, 680, 240, 150)

    # Table 8: artikel
    t_artikel = (
        '<div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000000; padding-bottom: 4px; margin-bottom: 6px; font-size: 13px;">artikel</div>'
        '<b><u>artikel_id</u></b> : INT AUTO_INCREMENT<br/>'
        'judul : VARCHAR(100)<br/>'
        'kutipan : VARCHAR(255)<br/>'
        'konten : TEXT<br/>'
        'kategori : VARCHAR(30)<br/>'
        'penulis : VARCHAR(35)<br/>'
        'gambar : VARCHAR(50)<br/>'
        'dibuat_pada : TIMESTAMP'
    )
    b.add_node("tbl_artikel", t_artikel, b.ST_TABLE, 470, 680, 240, 170)

    # Table 9: faq
    t_faq = (
        '<div style="text-align: center; font-weight: bold; border-bottom: 1px solid #000000; padding-bottom: 4px; margin-bottom: 6px; font-size: 13px;">faq</div>'
        '<b><u>faq_id</u></b> : INT AUTO_INCREMENT<br/>'
        'kategori : VARCHAR(30)<br/>'
        'pertanyaan : VARCHAR(100)<br/>'
        'jawaban : TEXT'
    )
    b.add_node("tbl_faq", t_faq, b.ST_TABLE, 850, 680, 250, 110)

    # Links ERD (Crow's Foot style)
    # pengguna -> pesanan (1 to N)
    b.link_nodes("tbl_pengguna", "tbl_pesanan", style=b.EDGE_ERD, exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    # pengguna -> pembayaran (1 to N)
    b.link_nodes("tbl_pengguna", "tbl_pembayaran", style=b.EDGE_ERD, exit_pt=(1, 0.5), entry_pt=(0.25, 0), points=[(380, 155), (380, 310), (535, 310)])
    # pesanan -> pembayaran (1 to 1 / N)
    b.link_nodes("tbl_pesanan", "tbl_pembayaran", style=b.EDGE_ERD, exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    # pesanan -> detail_pesanan (1 to N)
    b.link_nodes("tbl_pesanan", "tbl_detail", style=b.EDGE_ERD, exit_pt=(0.75, 1), entry_pt=(0.25, 1), points=[(275, 600), (912, 600)])
    # produk -> detail_pesanan (1 to N)
    b.link_nodes("tbl_produk", "tbl_detail", style=b.EDGE_ERD, exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    # pesanan -> notifikasi (1 to N)
    b.link_nodes("tbl_pesanan", "tbl_notif", style=b.EDGE_ERD, exit_pt=(0.5, 1), entry_pt=(0.5, 0))

    b.save(os.path.join(dest_dir, "erd", "erd.drawio"))


def generate_activity(dest_dir):
    print("Generating Activity Diagram...")
    b = DrawioDiagramBuilder("Activity Pemesanan", "act-pemesanan", width=950, height=1050)
    
    # Swimlanes
    b.add_node("lane_left", "Pelanggan", b.ST_SWIMLANE, 60, 40, 380, 960)
    b.add_node("lane_right", "Sistem & Admin BatuMekar", b.ST_SWIMLANE, 500, 40, 380, 960)

    # Start node
    b.add_node("start", "", b.ST_START, 235, 80, 30, 30)

    # Actions
    b.add_node("a1", "Memilih Madu & Klik Tambah ke Keranjang", b.ST_ACTION, 150, 150, 200, 55)
    b.add_node("s1", "Menyimpan Madu ke Keranjang & Menampilkan Keranjang", b.ST_ACTION, 590, 150, 200, 55)
    
    b.add_node("a2", "Klik Checkout & Mengisi Data Penerima & Alamat", b.ST_ACTION, 150, 240, 200, 55)
    b.add_node("s2", "Menghitung Total Harga + Ongkos Kirim + Kode Unik", b.ST_ACTION, 590, 240, 200, 55)
    b.add_node("s3", "Menyimpan Data Pesanan Baru ('Pending') & Menampilkan Detail Tagihan", b.ST_ACTION, 590, 330, 200, 55)
    
    b.add_node("a3", "Mentransfer Nominal ke Rekening & Mengunggah Bukti Pembayaran", b.ST_ACTION, 150, 420, 200, 55)
    b.add_node("s4", "Menyimpan Bukti & Memperbarui Status Pesanan Menjadi 'Sudah Bayar'", b.ST_ACTION, 590, 420, 200, 55)
    b.add_node("s5", "Menampilkan Notifikasi Transaksi Baru di Dashboard Admin", b.ST_ACTION, 590, 510, 200, 55)
    
    b.add_node("s6", "Admin Memverifikasi Bukti Transfer Bank", b.ST_ACTION, 590, 600, 200, 55)
    b.add_node("dec_valid", "Bukti Transfer\nValid?", b.ST_RHOMBUS, 630, 680, 120, 75)
    
    b.add_node("s7_proses", "Memproses Pesanan & Mengirim Madu via Kurir Ekspedisi", b.ST_ACTION, 590, 790, 200, 55)
    b.add_node("s8_tolak", "Menolak Pembayaran & Mengirim Notifikasi Gagal", b.ST_ACTION, 450, 690, 120, 55) # Cross lane center
    
    b.add_node("a4", "Menerima Paket Madu & Menandai Selesai", b.ST_ACTION, 150, 790, 200, 55)
    
    # End node
    b.add_node("final", "", b.ST_END_OUTER, 235, 890, 30, 30)
    b.add_node("final_in", "", b.ST_END_INNER, 242, 897, 16, 16)

    # Connections
    b.link_nodes("start", "a1", style=b.EDGE_ORTHO, exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link_nodes("a1", "s1", style=b.EDGE_ORTHO, exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("s1", "a2", style=b.EDGE_ORTHO, exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(690, 215), (690, 225), (250, 225)])
    b.link_nodes("a2", "s2", style=b.EDGE_ORTHO, exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("s2", "s3", style=b.EDGE_ORTHO, exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link_nodes("s3", "a3", style=b.EDGE_ORTHO, exit_pt=(0.5, 1), entry_pt=(0.5, 0), points=[(690, 395), (690, 405), (250, 405)])
    b.link_nodes("a3", "s4", style=b.EDGE_ORTHO, exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link_nodes("s4", "s5", style=b.EDGE_ORTHO, exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link_nodes("s5", "s6", style=b.EDGE_ORTHO, exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link_nodes("s6", "dec_valid", style=b.EDGE_ORTHO, exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    
    b.link_nodes("dec_valid", "s7_proses", label="Ya", style=b.EDGE_ORTHO, exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link_nodes("dec_valid", "s8_tolak", label="Tidak", style=b.EDGE_ORTHO, exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    b.link_nodes("s8_tolak", "a3", style=b.EDGE_ORTHO, exit_pt=(0.5, 0), entry_pt=(0.5, 1), points=[(510, 500), (250, 500)])
    
    b.link_nodes("s7_proses", "a4", style=b.EDGE_ORTHO, exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    b.link_nodes("a4", "final", style=b.EDGE_ORTHO, exit_pt=(0.5, 1), entry_pt=(0.5, 0))

    b.save(os.path.join(dest_dir, "activity", "alur_pemesanan.drawio"))


def generate_architecture(dest_dir):
    print("Generating Program Architecture Diagram...")
    b = DrawioDiagramBuilder("Arsitektur Program", "arch-batumekar", width=1100, height=800)
    
    # 3 Layers Frames
    b.add_node("layer_client", "Client-Side (User Interface)", b.ST_FRAME_SOLID, 50, 80, 280, 600)
    b.add_node("layer_server", "Server-Side (Application Logic)", b.ST_FRAME_SOLID, 410, 80, 280, 600)
    b.add_node("layer_db", "Database Engine", b.ST_FRAME_SOLID, 770, 80, 280, 600)

    # Client Elements
    b.add_node("c1", "Web Browser Interface\n(Chrome, Firefox, Safari)", b.ST_BOX, 70, 140, 240, 80)
    b.add_node("c2", "HTML5 & CSS3 Styling\n(Vanilla CSS UI / Font Google)", b.ST_BOX, 70, 240, 240, 60)
    b.add_node("c3", "JavaScript & Fetch API\n(Interaktivitas & Kirim Data)", b.ST_BOX, 70, 320, 240, 60)
    b.add_node("c4", "Session Storage & Cookies\n(Pengingat Login / Keranjang)", b.ST_BOX, 70, 400, 240, 60)

    # Server Elements
    b.add_node("s1", "PHP Core Controller Scripts\n(index.php, katalog.php, dll.)", b.ST_BOX, 430, 140, 240, 80)
    b.add_node("s2", "Session Handler & Auth Guard\n(Proses Login / Cek Peran)", b.ST_BOX, 430, 240, 240, 60)
    b.add_node("s3", "File Upload Handler\n(Unggah Bukti Transfer)", b.ST_BOX, 430, 320, 240, 60)
    b.add_node("s4", "PDO Database Abstraction\n(config/db.php Helper)", b.ST_BOX, 430, 400, 240, 60)
    b.add_node("s5", "Includes Component Engine\n(header.php & footer.php)", b.ST_BOX, 430, 480, 240, 60)

    # Database Elements
    b.add_node("d1", "SQLite Database Engine\n(database.sqlite File)", b.ST_CYLINDER, 790, 140, 240, 80)
    b.add_node("d2", "MySQL Server Support\n(Koneksi opsional untuk produksi)", b.ST_CYLINDER, 790, 240, 240, 80)
    b.add_node("d3", "Database Tables\n(pengguna, produk, pesanan, dll.)", b.ST_BOX, 790, 350, 240, 150)

    # Connections
    # Client <-> Server
    b.link_nodes("c1", "s1", label="HTTP GET/POST Request", style=b.EDGE_ORTHO, exit_pt=(1, 0.25), entry_pt=(0, 0.25))
    b.link_nodes("s1", "c1", label="HTML / JSON Response", style=b.EDGE_ORTHO, exit_pt=(0, 0.75), entry_pt=(1, 0.75))
    
    # Server <-> Database
    b.link_nodes("s4", "d1", label="PDO SQLite Conn", style=b.EDGE_ORTHO, exit_pt=(1, 0.25), entry_pt=(0, 0.5), points=[(720, 415), (720, 180)])
    b.link_nodes("s4", "d3", label="Execute SQL Queries", style=b.EDGE_ORTHO, exit_pt=(1, 0.75), entry_pt=(0, 0.5))

    b.save(os.path.join(dest_dir, "arsitektur", "arsitektur.drawio"))


def generate_all():
    dest = os.path.dirname(os.path.abspath(__file__))
    print(f"Generating all system diagrams inside {dest}...")
    generate_usecase(dest)
    generate_erd(dest)
    generate_activity(dest)
    generate_architecture(dest)
    print("Done! Generated 4 system diagrams successfully!")


if __name__ == "__main__":
    generate_all()

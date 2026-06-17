import os
import xml.etree.ElementTree as ET
import xml.dom.minidom as minidom

class DrawioMockupBuilder:
    """Builder for academic website wireframes/mockups in Draw.io format."""
    
    # Styles
    ST_FRAME = "rounded=0;whiteSpace=wrap;html=1;fillColor=#ffffff;strokeColor=#000000;strokeWidth=2;fontColor=#000000;align=center;verticalAlign=top;pointerEvents=0;"
    ST_LINE = "line;strokeWidth=1;html=1;strokeColor=#000000;align=center;verticalAlign=middle;"
    ST_TEXT = "text;html=1;align=left;verticalAlign=middle;whiteSpace=wrap;rounded=0;fontColor=#000000;fontSize=12;"
    ST_TEXT_CENTER = "text;html=1;align=center;verticalAlign=middle;whiteSpace=wrap;rounded=0;fontColor=#000000;fontSize=12;"
    ST_TEXT_RIGHT = "text;html=1;align=right;verticalAlign=middle;whiteSpace=wrap;rounded=0;fontColor=#000000;fontSize=12;"
    ST_TEXT_BOLD = "text;html=1;align=left;verticalAlign=middle;whiteSpace=wrap;rounded=0;fontColor=#000000;fontSize=12;fontStyle=1;"
    ST_TEXT_BOLD_CENTER = "text;html=1;align=center;verticalAlign=middle;whiteSpace=wrap;rounded=0;fontColor=#000000;fontSize=12;fontStyle=1;"
    ST_BUTTON = "rounded=0;whiteSpace=wrap;html=1;fillColor=#ffffff;strokeColor=#000000;strokeWidth=1;fontColor=#000000;fontSize=12;align=center;verticalAlign=middle;"
    ST_BUTTON_BOLD = "rounded=0;whiteSpace=wrap;html=1;fillColor=#ffffff;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;align=center;verticalAlign=middle;"
    ST_INPUT = "rounded=0;whiteSpace=wrap;html=1;fillColor=#ffffff;strokeColor=#000000;strokeWidth=1;fontColor=#000000;fontSize=12;align=left;verticalAlign=middle;spacingLeft=10;"
    ST_BOX = "rounded=0;whiteSpace=wrap;html=1;fillColor=#ffffff;strokeColor=#000000;strokeWidth=1;fontColor=#000000;fontSize=12;align=center;verticalAlign=middle;"
    ST_CARD = "rounded=0;whiteSpace=wrap;html=1;fillColor=#ffffff;strokeColor=#000000;strokeWidth=1;fontColor=#000000;fontSize=12;align=left;verticalAlign=top;spacingLeft=8;spacingRight=8;spacingTop=6;spacingBottom=6;"

    def __init__(self, page_name, page_id, width=1100, height=800):
        self.name = page_name
        self.page_id = page_id
        self.width = width
        self.height = height
        self.nodes = []
        self.counter = 0

    def _next_id(self, prefix="elem"):
        self.counter += 1
        return f"{prefix}_{self.page_id}_{self.counter}"

    def add_node(self, nid, label, style, x, y, w, h):
        self.nodes.append({
            "id": nid,
            "value": label,
            "style": style,
            "x": x, "y": y, "w": w, "h": h
        })

    def add_text(self, label, x, y, w, h, bold=False, align="left"):
        nid = self._next_id()
        if align == "center":
            style = self.ST_TEXT_BOLD_CENTER if bold else self.ST_TEXT_CENTER
        elif align == "right":
            style = self.ST_TEXT_BOLD if bold else self.ST_TEXT_RIGHT
        else:
            style = self.ST_TEXT_BOLD if bold else self.ST_TEXT
        self.add_node(nid, label, style, x, y, w, h)
        return nid

    def add_button(self, label, x, y, w, h, bold=False):
        nid = self._next_id()
        style = self.ST_BUTTON_BOLD if bold else self.ST_BUTTON
        self.add_node(nid, label, style, x, y, w, h)
        return nid

    def add_input(self, placeholder, x, y, w, h):
        nid = self._next_id()
        self.add_node(nid, placeholder, self.ST_INPUT, x, y, w, h)
        return nid

    def add_box(self, label, x, y, w, h):
        nid = self._next_id()
        self.add_node(nid, label, self.ST_BOX, x, y, w, h)
        return nid

    def add_card(self, label, x, y, w, h):
        nid = self._next_id()
        self.add_node(nid, label, self.ST_CARD, x, y, w, h)
        return nid

    def add_line(self, x, y, w, h):
        nid = self._next_id()
        self.add_node(nid, "", self.ST_LINE, x, y, w, h)
        return nid

    def add_header(self, active_tab="Beranda", is_admin=False):
        # Background web frame
        self.add_node("web_frame", "", self.ST_FRAME, 0, 0, self.width, self.height)
        
        # Bottom Line Header
        self.add_line(0, 60, self.width, 1)

        if is_admin:
            # Admin Header
            self.add_text("Panel Admin BatuMekar", 50, 15, 250, 30, bold=True)
            self.add_button("Lihat Toko", 850, 15, 100, 30)
            self.add_button("Keluar", 960, 15, 90, 30, bold=True)
        else:
            # Customer Header
            self.add_text("Madu BatuMekar", 50, 15, 150, 30, bold=True)
            
            # Navigation Tabs
            tabs = [
                ("Beranda", 250),
                ("Katalog", 330),
                ("Kalender Panen", 410),
                ("Tentang Desa", 520),
                ("Blog", 620),
                ("Hubungi Kami", 680),
                ("Bantuan", 785)
            ]
            
            for tab, x_pos in tabs:
                if tab == active_tab:
                    self.add_button(tab, x_pos, 15, len(tab)*8 + 20, 30, bold=True)
                else:
                    self.add_text(tab, x_pos, 15, len(tab)*8 + 20, 30, align="center")

            # Cart and User Account Section
            self.add_button("[Icon] Keranjang (2)", 880, 15, 110, 30)
            self.add_button("[Icon] Akun", 1000, 15, 70, 30)

    def add_footer(self, is_admin=False):
        # Footer Line
        self.add_line(0, self.height - 60, self.width, 1)
        
        # Footer Text
        copyright_txt = "© 2026 Kelompok Tani Desa BatuMekar - Madu BatuMekar. All Rights Reserved."
        academic_txt = "Teknologi Informasi - Universitas Mataram"
        
        self.add_text(copyright_txt, 50, self.height - 50, 600, 30)
        self.add_text(academic_txt, 850, self.height - 50, 200, 30, align="right")

    def add_user_sidebar(self, active_item="Dashboard"):
        items = ["Dashboard Saya", "Riwayat Pesanan", "Pengaturan Profil", "Keluar"]
        y_pos = 120
        self.add_card("MENU AKUN", 50, 100, 200, 200)
        for item in items:
            if item == active_item:
                self.add_button(item, 60, y_pos, 180, 30, bold=True)
            else:
                self.add_text(item, 60, y_pos, 180, 30)
            y_pos += 35

    def add_admin_sidebar(self, active_item="Dashboard"):
        items = ["Dashboard", "Kelola Produk", "Kelola Kategori", "Kelola Pesanan", "Kelola Artikel"]
        y_pos = 120
        self.add_card("MENU ADMIN", 50, 100, 200, 240)
        for item in items:
            if item == active_item:
                self.add_button(item, 60, y_pos, 180, 30, bold=True)
            else:
                self.add_text(item, 60, y_pos, 180, 30)
            y_pos += 35

    def save(self, filepath):
        mxfile = ET.Element("mxfile", host="65bd71144e")
        diagram = ET.SubElement(mxfile, "diagram", id=self.page_id, name=self.name)
        model = ET.SubElement(diagram, "mxGraphModel", 
                              dx="1200", dy="800", grid="1", gridSize="10",
                              guides="1", tooltips="1", connect="1", arrows="1",
                              fold="1", page="1", pageScale="1",
                              pageWidth=str(self.width), pageHeight=str(self.height),
                              math="0", shadow="0")
        root = ET.SubElement(model, "root")
        
        ET.SubElement(root, "mxCell", id="0")
        ET.SubElement(root, "mxCell", id="1", parent="0")

        for n in self.nodes:
            # Set parent to "1" or make parent a parent node
            parent_id = "1"
            cell = ET.SubElement(root, "mxCell", id=n["id"], value=n["value"],
                                 style=n["style"], vertex="1", parent=parent_id)
            geom = ET.SubElement(cell, "mxGeometry",
                                 x=str(n["x"]), y=str(n["y"]),
                                 width=str(n["w"]), height=str(n["h"]))
            geom.set("as", "geometry")

        # Formatting Pretty XML
        raw = ET.tostring(mxfile, encoding="utf-8")
        dom = minidom.parseString(raw)
        pretty_xml = dom.toprettyxml(indent="    ")
        
        # Remove empty lines in pretty printing
        pretty_xml = "\n".join([line for line in pretty_xml.split("\n") if line.strip()])
        
        if pretty_xml.startswith("<?xml"):
            pretty_xml = pretty_xml.split("\n", 1)[1]
            
        out_content = '<?xml version="1.0" encoding="UTF-8"?>\n' + pretty_xml

        # Ensure directory exists
        os.makedirs(os.path.dirname(filepath), exist_ok=True)
        
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(out_content)


def generate_all_mockups(dest_dir):
    print(f"Generating all mockups inside {dest_dir}...")

    # 1. Beranda
    b = DrawioMockupBuilder("1_beranda", "page_beranda")
    b.add_header("Beranda")
    # Hero
    b.add_text("Madu Murni dari Hutan Desa BatuMekar", 50, 100, 500, 80, bold=True)
    b.add_text("Merasakan kemurnian alam dari setiap tetes madu yang dipanen secara tradisional oleh kelompok tani hutan desa kami. 100% Asli tanpa campuran pemanis.", 50, 190, 500, 60)
    b.add_button("Belanja Sekarang", 50, 270, 150, 40, bold=True)
    b.add_box("[ Gambar Hero - Lebah & Hutan Desa BatuMekar ]", 600, 100, 450, 220)
    # Features
    b.add_box("[Icon: Alami]\n100% Alami\nMurni tanpa campuran kimia", 50, 360, 300, 100)
    b.add_box("[Icon: Manis]\nTanpa Pemanis\nRasa manis murni nektar", 400, 360, 300, 100)
    b.add_box("[Icon: Eco]\nPanen Lestari\nMenjaga kelestarian lebah", 750, 360, 300, 100)
    # Featured Products Title
    b.add_text("Koleksi Madu Terbaik Kami", 50, 490, 400, 30, bold=True)
    # Cards
    b.add_card("Madu Multiflora 350ml\nRp 85.000\n[Gambar Madu]\nNektar bunga liar.", 50, 530, 220, 190)
    b.add_button("Beli", 190, 680, 60, 25)
    b.add_card("Madu Kaliandra 350ml\nRp 95.000\n[Gambar Madu]\nKuning terang khas.", 290, 530, 220, 190)
    b.add_button("Beli", 430, 680, 60, 25)
    b.add_card("Madu Kelengkeng 500ml\nRp 110.000\n[Gambar Madu]\nAroma buah kelengkeng.", 530, 530, 220, 190)
    b.add_button("Beli", 670, 680, 60, 25)
    b.add_card("Madu Hutan Liar 500ml\nRp 120.000\n[Gambar Madu]\nMurni lebah Odeng.", 770, 530, 220, 190)
    b.add_button("Beli", 910, 680, 60, 25)
    b.add_footer()
    b.save(os.path.join(dest_dir, "1_beranda.drawio"))

    # 2. Katalog
    b = DrawioMockupBuilder("2_katalog", "page_katalog")
    b.add_header("Katalog")
    # Title
    b.add_text("Katalog Madu BatuMekar", 50, 80, 400, 30, bold=True)
    # Filter Sidebar
    b.add_card("FILTER PRODUK\n\n[ Cari Produk... ]\n\nKategori:\n[ ] Semua\n[x] Madu Hutan\n[ ] Madu Budidaya\n[ ] Paket Reseller\n\nHarga:\nMin: Rp 0\nMax: Rp 200.000", 50, 130, 200, 400)
    # Products Grid
    # Row 1
    b.add_card("Madu Hutan Liar 500ml\nRp 120.000\n[Gambar Madu]\nMurni dari hutan BatuMekar.", 280, 130, 230, 250)
    b.add_button("Detail", 290, 340, 95, 30)
    b.add_button("+ Keranjang", 400, 340, 100, 30, bold=True)

    b.add_card("Madu Trigona 350ml\nRp 135.000\n[Gambar Madu]\nMadu klanceng asam manis.", 540, 130, 230, 250)
    b.add_button("Detail", 550, 340, 95, 30)
    b.add_button("+ Keranjang", 660, 340, 100, 30, bold=True)

    b.add_card("Paket Madu Trio\nRp 250.000\n[Gambar Paket]\nMadu Multiflora + Kaliandra + Liar", 800, 130, 230, 250)
    b.add_button("Detail", 810, 340, 95, 30)
    b.add_button("+ Keranjang", 920, 340, 100, 30, bold=True)
    
    # Row 2
    b.add_card("Madu Kaliandra 350ml\nRp 95.000\n[Gambar Madu]\nKuning terang khas.", 280, 400, 230, 250)
    b.add_button("Detail", 290, 610, 95, 30)
    b.add_button("+ Keranjang", 400, 610, 100, 30, bold=True)

    b.add_card("Madu Multiflora 350ml\nRp 85.000\n[Gambar Madu]\nNektar bunga liar hutan.", 540, 400, 230, 250)
    b.add_button("Detail", 550, 610, 95, 30)
    b.add_button("+ Keranjang", 660, 610, 100, 30, bold=True)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "2_katalog.drawio"))

    # 3. Detail Produk
    b = DrawioMockupBuilder("3_detail_produk", "page_detail_produk")
    b.add_header("Katalog")
    b.add_text("Beranda > Katalog > Madu Hutan Liar 500ml", 50, 80, 400, 20)
    b.add_box("[ Foto Produk Madu Hutan Liar 500ml ]", 50, 120, 400, 400)
    b.add_text("Madu Hutan Liar BatuMekar 500ml", 480, 120, 570, 40, bold=True)
    b.add_text("Rp 120.000", 480, 170, 200, 30, bold=True)
    b.add_text("Ulasan: ★★★★★ (4.9 dari 28 ulasan)", 480, 215, 300, 20)
    b.add_text("Madu Hutan Liar merupakan madu alami yang diperoleh dari lebah Apis Dorsata yang mengambil nektar dari aneka tanaman di hutan Desa BatuMekar secara alami tanpa pestisida. Sangat berkhasiat menjaga imunitas dan menyembuhkan penyakit.", 480, 250, 570, 100)
    b.add_text("Stok: Tersedia (24 pcs)\nBerat: 750 gram", 480, 370, 200, 40)
    b.add_text("Jumlah:", 480, 430, 60, 30)
    b.add_button("-", 550, 430, 30, 30)
    b.add_box("1", 585, 430, 40, 30)
    b.add_button("+", 630, 430, 30, 30)
    b.add_button("Tambah ke Keranjang", 480, 485, 180, 40, bold=True)
    b.add_button("Beli Sekarang", 680, 485, 150, 40)
    
    b.add_card("DESKRIPSI LENGKAP & ULASAN PELANGGAN\n- Kandungan: Madu Asli 100% tanpa pemanis buatan\n- Ulasan Siti: 'Madunya legit, kerasa banget alaminya. Repurchase!'\n- Ulasan Budi: 'Mantap, kiriman cepat dan kemasan bubble wrap tebal.'", 50, 550, 1000, 120)
    b.add_footer()
    b.save(os.path.join(dest_dir, "3_detail_produk.drawio"))

    # 4. Keranjang
    b = DrawioMockupBuilder("4_keranjang", "page_keranjang")
    b.add_header("Katalog")
    b.add_text("Keranjang Belanja Anda", 50, 80, 400, 30, bold=True)
    # Table headers
    b.add_card("Daftar Belanjaan", 50, 120, 700, 450)
    b.add_text("PRODUK", 70, 140, 200, 20, bold=True)
    b.add_text("HARGA", 320, 140, 100, 20, bold=True)
    b.add_text("JUMLAH", 450, 140, 100, 20, bold=True)
    b.add_text("SUBTOTAL", 580, 140, 100, 20, bold=True)
    b.add_line(50, 170, 700, 1)
    
    # Row 1
    b.add_text("[Gambar] Madu Hutan Liar 500ml", 70, 190, 230, 30)
    b.add_text("Rp 120.000", 320, 190, 100, 30)
    b.add_box("1", 450, 190, 40, 30)
    b.add_text("Rp 120.000", 580, 190, 100, 30)
    b.add_button("Hapus", 680, 190, 50, 25)
    b.add_line(50, 240, 700, 1)
    
    # Row 2
    b.add_text("[Gambar] Madu Multiflora 350ml", 70, 260, 230, 30)
    b.add_text("Rp 85.000", 320, 260, 100, 30)
    b.add_box("2", 450, 260, 40, 30)
    b.add_text("Rp 170.000", 580, 260, 100, 30)
    b.add_button("Hapus", 680, 260, 50, 25)
    b.add_line(50, 310, 700, 1)
    
    b.add_button("<- Kembali Belanja", 70, 510, 150, 35)
    b.add_button("Perbarui Keranjang", 570, 510, 150, 35)

    # Summary
    b.add_card("RINGKASAN BELANJA", 780, 120, 270, 250)
    b.add_text("Total Item:", 800, 150, 100, 20)
    b.add_text("3 Item", 930, 150, 100, 20, align="right")
    b.add_text("Subtotal:", 800, 180, 100, 20)
    b.add_text("Rp 290.000", 930, 180, 100, 20, align="right")
    b.add_line(780, 220, 270, 1)
    b.add_text("Grand Total:", 800, 240, 100, 20, bold=True)
    b.add_text("Rp 290.000", 930, 240, 100, 20, bold=True, align="right")
    b.add_button("Lanjut ke Checkout", 800, 290, 230, 40, bold=True)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "4_keranjang.drawio"))

    # 5. Checkout
    b = DrawioMockupBuilder("5_checkout", "page_checkout")
    b.add_header("Katalog")
    b.add_text("Formulir Checkout Pemesanan", 50, 80, 400, 30, bold=True)
    
    # Form Details
    b.add_card("DATA DIRI & ALAMAT PENGIRIMAN", 50, 120, 600, 550)
    b.add_text("Nama Lengkap *", 70, 150, 150, 20, bold=True)
    b.add_input("Indra Pratama", 70, 175, 560, 35)
    
    b.add_text("No. WhatsApp / HP *", 70, 225, 150, 20, bold=True)
    b.add_input("081234567890", 70, 250, 560, 35)
    
    b.add_text("Alamat Lengkap Pengiriman *", 70, 300, 200, 20, bold=True)
    b.add_input("Jl. Raya Narmada, Gg. Damai No. 12, Lombok Barat", 70, 325, 560, 60)
    
    b.add_text("Pilih Layanan Ekspedisi *", 70, 400, 200, 20, bold=True)
    b.add_input("JNE Express - Layanan Reguler", 70, 425, 270, 35)
    b.add_text("Pilih Kota/Kecamatan *", 360, 400, 200, 20, bold=True)
    b.add_input("Lingsar, Lombok Barat", 360, 425, 270, 35)
    
    b.add_text("Catatan Pesanan (Opsional)", 70, 480, 200, 20)
    b.add_input("Mohon packing bubble wrap tebal.", 70, 505, 560, 50)
    
    # Order Summary Sidebar
    b.add_card("RINGKASAN ORDER", 680, 120, 370, 450)
    b.add_text("Pesanan Anda:", 700, 140, 200, 20, bold=True)
    b.add_text("- Madu Hutan Liar (1x)", 700, 170, 200, 20)
    b.add_text("Rp 120.000", 930, 170, 100, 20, align="right")
    b.add_text("- Madu Multiflora (2x)", 700, 195, 200, 20)
    b.add_text("Rp 170.000", 930, 195, 100, 20, align="right")
    b.add_line(680, 230, 370, 1)
    
    b.add_text("Subtotal:", 700, 250, 100, 20)
    b.add_text("Rp 290.000", 930, 250, 100, 20, align="right")
    b.add_text("Ongkos Kirim (JNE):", 700, 280, 150, 20)
    b.add_text("Rp 15.000", 930, 280, 100, 20, align="right")
    b.add_text("Kode Unik Transfer:", 700, 310, 150, 20)
    b.add_text("Rp 245", 930, 310, 100, 20, align="right")
    b.add_line(680, 340, 370, 1)
    
    b.add_text("Total Pembayaran:", 700, 360, 150, 20, bold=True)
    b.add_text("Rp 305.245", 930, 360, 100, 20, bold=True, align="right")
    
    b.add_text("Metode: Transfer Bank Manual (BCA/Mandiri)", 700, 400, 330, 20)
    b.add_button("Buat Pesanan & Bayar", 700, 480, 330, 45, bold=True)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "5_checkout.drawio"))

    # 6. Pembayaran
    b = DrawioMockupBuilder("6_pembayaran", "page_pembayaran")
    b.add_header("Katalog")
    b.add_text("Menunggu Pembayaran", 50, 80, 400, 30, bold=True)
    
    b.add_card("INFORMASI PESANAN", 50, 120, 1000, 80)
    b.add_text("Pesanan Anda dengan nomor invoice #INV-2026061501 berhasil dibuat.\nHarap segera lakukan transfer pembayaran agar pesanan dapat diproses oleh Admin kami.", 70, 135, 960, 50)

    # Bank Transfer Panel
    b.add_card("REKENING TUJUAN TRANSFER", 50, 220, 600, 350)
    b.add_text("Silakan transfer sejumlah nominal berikut (Harus Sama):", 70, 240, 500, 20)
    b.add_text("Rp 305.245", 70, 270, 300, 40, bold=True)
    b.add_text("Batas waktu pembayaran: 24 Jam dari sekarang.", 70, 315, 400, 20)
    
    b.add_text("Bank BCA", 70, 360, 150, 20, bold=True)
    b.add_text("No. Rek: 890-123-4567\na/n: Batu Mekar Madu", 70, 385, 200, 40)
    
    b.add_text("Bank Mandiri", 320, 360, 150, 20, bold=True)
    b.add_text("No. Rek: 123-000-987654\na/n: Batu Mekar Madu", 320, 385, 200, 40)
    
    # Summary Info
    b.add_card("RINGKASAN DETAIL TAGIHAN", 680, 220, 370, 250)
    b.add_text("Penerima:", 700, 240, 100, 20)
    b.add_text("Indra Pratama (081234567890)", 810, 240, 220, 20)
    b.add_text("Alamat:", 700, 270, 100, 20)
    b.add_text("Jl. Raya Narmada, Lombok Barat, NTB", 810, 270, 220, 40)
    b.add_text("Ekspedisi:", 700, 320, 100, 20)
    b.add_text("JNE Reguler (1-2 hari)", 810, 320, 220, 20)
    b.add_line(680, 360, 370, 1)
    b.add_text("Total Transfer:", 700, 380, 100, 20, bold=True)
    b.add_text("Rp 305.245", 930, 380, 100, 20, bold=True, align="right")

    b.add_button("Unggah Bukti Transfer Sekarang", 680, 490, 370, 45, bold=True)
    b.add_button("<- Kembali ke Dashboard Saya", 680, 550, 370, 35)

    b.add_footer()
    b.save(os.path.join(dest_dir, "6_pembayaran.drawio"))

    # 7. Upload Bukti
    b = DrawioMockupBuilder("7_upload_bukti", "page_upload_bukti")
    b.add_header("Katalog")
    b.add_text("Konfirmasi & Upload Bukti Pembayaran", 50, 80, 400, 30, bold=True)
    
    b.add_card("FORMULIR KONFIRMASI PEMBAYARAN", 250, 120, 600, 480)
    b.add_text("Nomor Invoice / Kode Pesanan *", 270, 150, 250, 20, bold=True)
    b.add_input("INV-2026061501", 270, 175, 560, 35)
    
    b.add_text("Nama Pemilik Rekening Pengirim *", 270, 225, 250, 20, bold=True)
    b.add_input("Indra Pratama", 270, 250, 560, 35)
    
    b.add_text("Transfer Ke Bank *", 270, 300, 250, 20, bold=True)
    b.add_input("Bank BCA (890-123-4567 a/n Batu Mekar Madu)", 270, 325, 560, 35)
    
    b.add_text("Pilih Foto Bukti Transfer * (Maks. 2MB, format .jpg/.png)", 270, 375, 450, 20, bold=True)
    b.add_button("Pilih File... (bukti_tf_indra.png)", 270, 400, 560, 35)
    
    b.add_button("Kirim Konfirmasi Pembayaran", 270, 490, 560, 45, bold=True)
    b.add_text("Proses verifikasi manual oleh admin memakan waktu 1-3 jam.", 270, 550, 560, 20, align="center")

    b.add_footer()
    b.save(os.path.join(dest_dir, "7_upload_bukti.drawio"))

    # 8. Tentang Desa
    b = DrawioMockupBuilder("8_tentang_desa", "page_tentang_desa")
    b.add_header("Tentang Desa")
    b.add_text("Profil Desa BatuMekar & Kelompok Madu BatuMekar", 50, 80, 600, 30, bold=True)
    
    b.add_text("Desa BatuMekar berlokasi di wilayah perbukitan asri Kecamatan Lingsar, Lombok Barat. Dikelilingi hutan alam sekunder yang kaya akan keanekaragaman flora, khususnya pohon kaliandra, kelengkeng, dan aneka tanaman liar lainnya.\n\nKelompok Tani BatuMekar menginisiasi panen madu hutan lestari guna memberdayakan ekonomi warga desa tanpa merusak habitat lebah hutan (Apis Dorsata). Kami menggunakan sistem panen tradisional ramah lingkungan yang menjaga populasi lebah tetap berkelanjutan.", 50, 130, 550, 260)
    
    b.add_box("[ Gambar Lanskap Desa BatuMekar & Sarang Lebah ]", 630, 130, 420, 240)
    
    # Vision & Mission
    b.add_card("VISI & MISI BATUMEKAR", 50, 410, 1000, 150)
    b.add_text("Visi kami adalah menjadi penyedia madu hutan asli berkualitas premium dan menjadi model pelopor pelestarian hutan lebah lokal NTB.\n\nMisi:\n1. Menyediakan madu murni berkualitas tinggi tanpa proses kimiawi.\n2. Memberdayakan kelompok tani lokal Desa BatuMekar secara berkelanjutan.\n3. Mengedukasi masyarakat mengenai pentingnya melestarikan hutan lindung.", 70, 430, 960, 110)
    
    # Harvesters Profile
    b.add_text("Anggota Inti Kelompok Tani BatuMekar", 50, 580, 400, 20, bold=True)
    b.add_card("[Foto Wayan]\nPak Wayan\nKepala Pemanen Hutan", 50, 610, 310, 100)
    b.add_card("[Foto Ketut]\nIbu Ketut\nQC & Pengemasan", 395, 610, 310, 100)
    b.add_card("[Foto Nyoman]\nPak Nyoman\nLogistik & Penjualan", 740, 610, 310, 100)

    b.add_footer()
    b.save(os.path.join(dest_dir, "8_tentang_desa.drawio"))

    # 9. Blog
    b = DrawioMockupBuilder("9_blog", "page_blog")
    b.add_header("Blog")
    b.add_text("Blog & Kabar Panen BatuMekar", 50, 80, 400, 30, bold=True)
    
    # Blog list
    b.add_card("Panen Raya Madu Kaliandra Musim Ini Berlimpah - 10 Juni 2026\n\nMusim kemarau tahun ini membawa berkah tersendiri bagi kelompok tani BatuMekar. Bunga kaliandra bermekaran sempurna di bukit BatuMekar, menghasilkan madu dengan aroma harum khas...", 50, 120, 700, 130)
    b.add_button("Baca Selengkapnya", 580, 210, 150, 30)

    b.add_card("5 Manfaat Utama Madu Hutan Liar Bagi Imunitas Tubuh - 28 Mei 2026\n\nMadu hutan liar mengandung antioksidan fenolik yang jauh lebih tinggi dibanding madu ternakan biasa. Kandungan ini efektif melawan radikal bebas dan memperkuat sistem imun tubuh...", 50, 270, 700, 130)
    b.add_button("Baca Selengkapnya", 580, 360, 150, 30)

    b.add_card("Bagaimana Membedakan Madu Asli dan Madu Campuran? - 15 Mei 2026\n\nBanyak beredar madu sirup atau campuran gula di pasaran. Kelompok Tani BatuMekar membagikan tips sederhana menguji keaslian madu menggunakan air hangat dan uji bakar kertas...", 50, 420, 700, 130)
    b.add_button("Baca Selengkapnya", 580, 510, 150, 30)
    
    # Sidebar
    b.add_card("KATEGORI BLOG\n[x] Berita Desa (4)\n[ ] Tips & Trik (6)\n[ ] Manfaat Kesehatan (12)\n[ ] Pengumuman (2)", 780, 120, 270, 150)
    
    b.add_card("PRODUK TERPOPULER\n1. Madu Hutan Liar 500ml\n2. Madu Kaliandra 350ml\n3. Madu Trigona 350ml", 780, 290, 270, 120)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "9_blog.drawio"))

    # 10. Kalender Panen
    b = DrawioMockupBuilder("10_kalender_panen", "page_kalender_panen")
    b.add_header("Kalender Panen")
    b.add_text("Kalender Panen Madu Desa BatuMekar", 50, 80, 500, 30, bold=True)
    b.add_text("Siklus panen madu hutan alami di desa kami mengikuti masa berbunga nektar pohon musiman.", 50, 110, 800, 20)
    
    # Calendar Table
    b.add_card("Tabel Siklus Panen", 50, 140, 1000, 350)
    b.add_text("BULAN PANEN", 70, 160, 150, 20, bold=True)
    b.add_text("JENIS MADU", 250, 160, 150, 20, bold=True)
    b.add_text("NEKTAR FLORA", 450, 160, 200, 20, bold=True)
    b.add_text("ESTIMASI KUANTITAS", 700, 160, 150, 20, bold=True)
    b.add_text("STATUS", 900, 160, 100, 20, bold=True)
    b.add_line(50, 190, 1000, 1)

    b.add_text("Januari - Februari", 70, 210, 150, 25)
    b.add_text("Madu Kaliandra", 250, 210, 150, 25)
    b.add_text("Bunga Kaliandra Liar", 450, 210, 200, 25)
    b.add_text("Sekitar 120 Kg", 700, 210, 150, 25)
    b.add_text("Selesai Panen", 900, 210, 100, 25)
    b.add_line(50, 245, 1000, 1)

    b.add_text("Maret - Mei", 70, 260, 150, 25)
    b.add_text("Madu Multiflora", 250, 260, 150, 25)
    b.add_text("Bunga Hutan Heterogen", 450, 260, 200, 25)
    b.add_text("Sekitar 350 Kg", 700, 260, 150, 25)
    b.add_text("Selesai Panen", 900, 260, 100, 25)
    b.add_line(50, 295, 1000, 1)

    b.add_text("Juni - Agustus", 70, 310, 150, 25)
    b.add_text("Madu Hutan Liar (Odeng)", 250, 310, 180, 25)
    b.add_text("Pohon Sialang Rimba", 450, 310, 200, 25)
    b.add_text("Sekitar 500 Kg", 700, 310, 150, 25)
    b.add_button("Mulai Panen", 900, 310, 90, 25, bold=True)
    b.add_line(50, 345, 1000, 1)

    b.add_text("September - November", 70, 360, 150, 25)
    b.add_text("Madu Kelengkeng", 250, 360, 150, 25)
    b.add_text("Bunga Kelengkeng Desa", 450, 360, 200, 25)
    b.add_text("Sekitar 200 Kg", 700, 360, 150, 25)
    b.add_text("Mendatang", 900, 360, 100, 25)
    b.add_line(50, 395, 1000, 1)

    b.add_card("INFORMASI PEMESANAN PANEN RAYA\nPelanggan dapat melakukan pre-order madu segar langsung sesaat setelah masa panen selesai untuk jaminan kesegaran maksimal. Hubungi WhatsApp admin kelompok tani kami.", 50, 510, 1000, 100)
    b.add_footer()
    b.save(os.path.join(dest_dir, "10_kalender_panen.drawio"))

    # 11. Keberlanjutan
    b = DrawioMockupBuilder("11_keberlanjutan", "page_keberlanjutan")
    b.add_header("Kalender Panen")  # or Keberlanjutan
    b.add_text("Panen Madu Berkelanjutan (Sustainable Harvesting)", 50, 80, 600, 30, bold=True)
    
    b.add_text("1. Sistem Panen Tiris Lestari\nKami berkomitmen menjaga populasi lebah madu liar Apis Dorsata. Kelompok pemanen hanya mengiris sebagian sarang madu dewasa (kepala madu) dan menyisakan sarang yang berisi anak/telur lebah agar koloni lebah tidak meninggalkan pohon sialang.\n\n2. Tanpa Pengasapan Api Berlebih\nKami mengganti asap api tradisional dengan semprotan asap dingin/herbal alami yang tidak membunuh lebah dan tidak meninggalkan residu karbon hangus pada madu hasil panen.", 50, 130, 550, 250)
    
    b.add_box("[ Skema Gambar Panen Tiris Lestari vs Panen Habis ]", 630, 130, 420, 240)
    
    b.add_card("DAMPAK BAGI LINGKUNGAN DESA BATUMEKAR\n- Ekosistem Hutan Terjaga: Lebah lebah hutan membantu penyerbukan 80% pohon kayu rimba di pegunungan Lingsar Lombok.\n- Keadilan Ekonomi: 100% keuntungan madu disalurkan ke kelompok tani dan kas kesejahteraan warga Desa BatuMekar.", 50, 400, 1000, 150)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "11_keberlanjutan.drawio"))

    # 12. Kontak
    b = DrawioMockupBuilder("12_kontak", "page_kontak")
    b.add_header("Hubungi Kami")
    b.add_text("Hubungi Kelompok Tani BatuMekar", 50, 80, 500, 30, bold=True)
    
    # Left Form
    b.add_card("KIRIM PESAN LANGSUNG", 50, 120, 550, 450)
    b.add_text("Nama Anda *", 70, 140, 100, 20, bold=True)
    b.add_input("Nama Lengkap", 70, 165, 510, 35)
    
    b.add_text("Email / WhatsApp *", 70, 215, 150, 20, bold=True)
    b.add_input("example@mail.com atau 0812...", 70, 240, 510, 35)
    
    b.add_text("Pesan Anda *", 70, 290, 100, 20, bold=True)
    b.add_input("Tulis pertanyaan Anda di sini...", 70, 315, 510, 120)
    
    b.add_button("Kirim Pesan Sekarang", 70, 460, 200, 40, bold=True)

    # Right Info
    b.add_card("INFORMASI KONTOR & LOKASI DESA", 630, 120, 420, 450)
    b.add_text("Dusun Mekar Sari, Desa BatuMekar,\nKecamatan Lingsar, Kabupaten Lombok Barat,\nNusa Tenggara Barat (NTB).\n\nHP/WA: +62 812-3456-7890\nEmail: admin@batumekardu.com", 650, 140, 380, 100)
    b.add_box("[ Google Maps - Peta Lokasi Desa BatuMekar ]", 650, 260, 380, 280)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "12_kontak.drawio"))

    # 13. Bantuan
    b = DrawioMockupBuilder("13_bantuan", "page_bantuan")
    b.add_header("Bantuan")
    b.add_text("Pusat Bantuan & Tanya Jawab (FAQ)", 50, 80, 500, 30, bold=True)
    
    # Accordion FAQ
    b.add_card("PERTANYAAN YANG SERING DIAJUKAN", 50, 120, 1000, 480)
    
    b.add_text("Q: Bagaimana cara memesan madu via online di website?", 70, 145, 960, 20, bold=True)
    b.add_text("A: Cari produk di menu Katalog, klik '+ Keranjang', setelah selesai klik ikon keranjang belanja lalu klik 'Lanjut ke Checkout'. Isi alamat lengkap pengiriman, kirim order, lakukan transfer bank sesuai nominal, dan unggah foto struk transfer di halaman Konfirmasi Pembayaran.", 70, 170, 960, 50)
    b.add_line(50, 230, 1000, 1)

    b.add_text("Q: Apakah madu BatuMekar terjamin keasliannya?", 70, 245, 960, 20, bold=True)
    b.add_text("A: Benar-benar murni 100% tanpa bahan campuran sirup/gula dan tanpa melalui proses pemanasan pasteurisasi tinggi (raw honey) sehingga kandungan enzim lebah alami masih terjaga secara utuh.", 70, 270, 960, 40)
    b.add_line(50, 320, 1000, 1)

    b.add_text("Q: Bisakah kirim ke luar daerah NTB / luar Lombok?", 70, 335, 960, 20, bold=True)
    b.add_text("A: Tentu. Kami bekerjasama dengan kurir JNE, J&T, dan POS Indonesia untuk pengiriman paket botol kaca/plastik khusus madu secara aman ke seluruh pulau di Indonesia.", 70, 360, 960, 40)
    b.add_line(50, 410, 1000, 1)

    b.add_text("Q: Bagaimana mendaftar sebagai reseller?", 70, 425, 960, 20, bold=True)
    b.add_text("A: Daftar akun baru di halaman register, pilih tipe pendaftaran 'Reseller', lalu hubungi admin via WhatsApp untuk validasi dokumen usaha reseller agar harga produk terpotong diskon otomatis.", 70, 450, 960, 40)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "13_bantuan.drawio"))

    # 14. Login
    b = DrawioMockupBuilder("14_login", "page_login")
    b.add_header("Bantuan")  # none/login
    b.add_text("Masuk ke Akun Anda", 50, 80, 400, 30, bold=True)
    
    b.add_card("LOGIN PENGGUNA", 350, 150, 400, 380)
    b.add_text("Username *", 380, 180, 200, 20, bold=True)
    b.add_input("Masukkan username...", 380, 205, 340, 35)
    
    b.add_text("Password *", 380, 255, 200, 20, bold=True)
    b.add_input("••••••••••••", 380, 280, 340, 35)
    
    b.add_text("[x] Ingat Saya di perangkat ini", 380, 330, 200, 20)
    b.add_text("Lupa Password?", 620, 330, 100, 20, align="right")
    
    b.add_button("Masuk Sekarang", 380, 375, 340, 40, bold=True)
    
    b.add_text("Belum punya akun BatuMekar? Daftar di sini", 380, 440, 340, 20, align="center")
    b.add_button("Daftar Akun Baru", 450, 470, 200, 30)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "14_login.drawio"))

    # 15. Register
    b = DrawioMockupBuilder("15_register", "page_register")
    b.add_header("Bantuan")
    b.add_text("Registrasi Akun Baru", 50, 80, 400, 30, bold=True)
    
    b.add_card("BUAT AKUN BATUMEKAR", 300, 110, 500, 470)
    b.add_text("Nama Lengkap Anda *", 330, 130, 200, 20, bold=True)
    b.add_input("Indra Pratama", 330, 150, 440, 30)

    b.add_text("Nomor WhatsApp *", 330, 190, 200, 20, bold=True)
    b.add_input("081234567890", 330, 210, 440, 30)

    b.add_text("Pilih Username *", 330, 250, 200, 20, bold=True)
    b.add_input("indra12", 330, 270, 440, 30)

    b.add_text("Password Akun *", 330, 310, 200, 20, bold=True)
    b.add_input("••••••••••••", 330, 330, 440, 30)

    b.add_text("Ulangi Password *", 330, 370, 200, 20, bold=True)
    b.add_input("••••••••••••", 330, 390, 440, 30)
    
    b.add_text("Daftar Sebagai:  [x] Pelanggan Umum    [ ] Reseller Madu", 330, 435, 440, 20, bold=True)
    
    b.add_button("Daftar Sekarang", 330, 475, 440, 40, bold=True)
    b.add_text("Sudah punya akun? Login di sini", 330, 535, 440, 20, align="center")

    b.add_footer()
    b.save(os.path.join(dest_dir, "15_register.drawio"))

    # 16. User Dashboard
    b = DrawioMockupBuilder("16_user_dashboard", "page_user_dashboard")
    b.add_header("Akun")
    b.add_user_sidebar("Dashboard Saya")
    
    # Main Content
    b.add_card("DASHBOARD PELANGGAN", 280, 100, 770, 500)
    b.add_text("Selamat Datang, Indra Pratama!", 300, 120, 400, 30, bold=True)
    b.add_text("Tipe Akun: Pelanggan Umum | Terdaftar sejak: Juni 2026", 300, 150, 500, 20)
    
    # Statistics cards
    b.add_card("TOTAL TRANSAKSI\n\n4 Pesanan", 300, 190, 220, 100)
    b.add_card("BELUM DIBAYAR\n\n1 Invoice", 545, 190, 220, 100)
    b.add_card("TOTAL BELANJA\n\nRp 845.000", 790, 190, 220, 100)
    
    # Recent Info
    b.add_text("Aktivitas Terakhir Anda:", 300, 310, 300, 20, bold=True)
    b.add_card("INV-2026061501 | 15 Jun 2026 | Rp 305.245 | Menunggu Pembayaran\nINV-2026061011 | 10 Jun 2026 | Rp 170.000 | Selesai Kirim\nINV-2026052004 | 20 Mei 2026 | Rp 370.000 | Selesai Kirim", 300, 340, 710, 150)
    
    b.add_button("Lihat Semua Pesanan Saya", 300, 510, 200, 35)
    b.add_footer()
    b.save(os.path.join(dest_dir, "16_user_dashboard.drawio"))

    # 17. User Orders
    b = DrawioMockupBuilder("17_user_orders", "page_user_orders")
    b.add_header("Akun")
    b.add_user_sidebar("Riwayat Pesanan")
    
    # Main Panel
    b.add_card("RIWAYAT PEMBELIAN", 280, 100, 770, 500)
    b.add_text("Daftar Invoice Belanja Anda", 300, 120, 400, 30, bold=True)
    
    # Table list
    b.add_text("NO INVOICE", 300, 160, 120, 20, bold=True)
    b.add_text("TANGGAL", 430, 160, 100, 20, bold=True)
    b.add_text("GRAND TOTAL", 550, 160, 120, 20, bold=True)
    b.add_text("STATUS", 690, 160, 150, 20, bold=True)
    b.add_text("AKSI", 880, 160, 150, 20, bold=True)
    b.add_line(280, 190, 770, 1)

    b.add_text("INV-2026061501", 300, 200, 120, 25)
    b.add_text("15 Jun 2026", 430, 200, 100, 25)
    b.add_text("Rp 305.245", 550, 200, 120, 25)
    b.add_text("Belum Bayar", 690, 200, 150, 25)
    b.add_button("Upload Bukti", 880, 200, 100, 25, bold=True)
    b.add_line(280, 235, 770, 1)

    b.add_text("INV-2026061011", 300, 250, 120, 25)
    b.add_text("10 Jun 2026", 430, 250, 100, 25)
    b.add_text("Rp 170.000", 550, 250, 120, 25)
    b.add_text("Selesai", 690, 250, 150, 25)
    b.add_button("Detail", 880, 250, 60, 25)
    b.add_line(280, 285, 770, 1)

    b.add_text("INV-2026052004", 300, 300, 120, 25)
    b.add_text("20 Mei 2026", 430, 300, 100, 25)
    b.add_text("Rp 370.000", 550, 300, 120, 25)
    b.add_text("Di Proses", 690, 300, 150, 25)
    b.add_button("Detail", 880, 300, 60, 25)
    b.add_line(280, 335, 770, 1)

    b.add_footer()
    b.save(os.path.join(dest_dir, "17_user_orders.drawio"))

    # 18. User Profile
    b = DrawioMockupBuilder("18_user_profile", "page_user_profile")
    b.add_header("Akun")
    b.add_user_sidebar("Pengaturan Profil")
    
    # Main Panel
    b.add_card("PENGATURAN PROFIL", 280, 100, 770, 500)
    b.add_text("Ubah Biodata & Keamanan Akun", 300, 120, 400, 30, bold=True)
    
    # Form fields
    b.add_text("Nama Lengkap", 300, 160, 150, 20, bold=True)
    b.add_input("Indra Pratama", 300, 185, 340, 30)

    b.add_text("Username (Tidak dapat diubah)", 670, 160, 200, 20, bold=True)
    b.add_input("indra12", 670, 185, 340, 30)

    b.add_text("Nomor WhatsApp", 300, 230, 150, 20, bold=True)
    b.add_input("081234567890", 300, 255, 340, 30)
    
    b.add_line(300, 310, 710, 1)
    
    b.add_text("Password Baru (Kosongkan jika tidak ingin diubah)", 300, 330, 350, 20, bold=True)
    b.add_input("••••••••••••", 300, 355, 340, 30)

    b.add_text("Ulangi Password Baru", 670, 330, 200, 20, bold=True)
    b.add_input("••••••••••••", 670, 355, 340, 30)
    
    b.add_button("Simpan Perubahan Data", 300, 420, 220, 40, bold=True)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "18_user_profile.drawio"))

    # 19. Admin Dashboard
    b = DrawioMockupBuilder("19_admin_dashboard", "page_admin_dashboard")
    b.add_header("Dashboard", is_admin=True)
    b.add_admin_sidebar("Dashboard")
    
    # Main Dashboard Panel
    b.add_card("DASHBOARD ADMINISTRATOR", 280, 100, 770, 500)
    b.add_text("Overview Penjualan & Aktivitas", 300, 120, 400, 30, bold=True)
    
    # Stat boxes
    b.add_card("TOTAL PENDAPATAN\n\nRp 12.450.000", 300, 160, 220, 100)
    b.add_card("PESANAN MASUK\n\n3 Baru / Pending", 545, 160, 220, 100)
    b.add_card("JUMLAH PRODUK\n\n12 Madu Aktif", 790, 160, 220, 100)
    
    # Recent orders table in dashboard
    b.add_text("Pesanan Terbaru Masuk:", 300, 280, 300, 20, bold=True)
    b.add_card("Invoice | Pelanggan | Grand Total | Status | Aksi\n#INV-001 | Indra | Rp 305.245 | Belum Bayar | [Detail]\n#INV-002 | Siti | Rp 170.000 | Sudah Bayar | [Proses]\n#INV-003 | Budi | Rp 370.000 | Di Proses | [Selesai]", 300, 310, 710, 180)
    
    b.add_footer()
    b.save(os.path.join(dest_dir, "19_admin_dashboard.drawio"))

    # 20. Admin Produk
    b = DrawioMockupBuilder("20_admin_produk", "page_admin_produk")
    b.add_header("Kelola Produk", is_admin=True)
    b.add_admin_sidebar("Kelola Produk")
    
    # Product Table
    b.add_card("KELOLA PRODUK MADU", 280, 100, 770, 500)
    b.add_text("Daftar Inventori Madu", 300, 120, 300, 30, bold=True)
    b.add_button("+ Tambah Produk Baru", 850, 120, 180, 30, bold=True)
    
    # Table headers
    b.add_text("FOTO", 300, 165, 80, 20, bold=True)
    b.add_text("NAMA PRODUK", 400, 165, 200, 20, bold=True)
    b.add_text("KATEGORI", 620, 165, 120, 20, bold=True)
    b.add_text("HARGA", 760, 165, 100, 20, bold=True)
    b.add_text("STOK", 880, 165, 50, 20, bold=True)
    b.add_text("AKSI", 950, 165, 90, 20, bold=True)
    b.add_line(280, 195, 770, 1)

    # Row 1
    b.add_text("[Foto]", 300, 205, 80, 20)
    b.add_text("Madu Hutan Liar 500ml", 400, 205, 200, 20)
    b.add_text("Madu Hutan", 620, 205, 120, 20)
    b.add_text("Rp 120.000", 760, 205, 100, 20)
    b.add_text("24", 880, 205, 50, 20)
    b.add_button("Edit", 940, 205, 45, 25)
    b.add_button("Del", 990, 205, 45, 25)
    b.add_line(280, 240, 770, 1)

    # Row 2
    b.add_text("[Foto]", 300, 250, 80, 20)
    b.add_text("Madu Multiflora 350ml", 400, 250, 200, 20)
    b.add_text("Madu Budidaya", 620, 250, 120, 20)
    b.add_text("Rp 85.000", 760, 250, 100, 20)
    b.add_text("50", 880, 250, 50, 20)
    b.add_button("Edit", 940, 250, 45, 25)
    b.add_button("Del", 990, 250, 45, 25)
    b.add_line(280, 285, 770, 1)

    # Row 3
    b.add_text("[Foto]", 300, 295, 80, 20)
    b.add_text("Madu Kaliandra 350ml", 400, 295, 200, 20)
    b.add_text("Madu Budidaya", 620, 295, 120, 20)
    b.add_text("Rp 95.000", 760, 295, 100, 20)
    b.add_text("12", 880, 295, 50, 20)
    b.add_button("Edit", 940, 295, 45, 25)
    b.add_button("Del", 990, 295, 45, 25)
    b.add_line(280, 330, 770, 1)

    b.add_footer()
    b.save(os.path.join(dest_dir, "20_admin_produk.drawio"))

    # 21. Admin Kategori
    b = DrawioMockupBuilder("21_admin_kategori", "page_admin_kategori")
    b.add_header("Kelola Kategori", is_admin=True)
    b.add_admin_sidebar("Kelola Kategori")
    
    # Left Form
    b.add_card("TAMBAH KATEGORI BARU", 280, 100, 320, 300)
    b.add_text("Nama Kategori *", 300, 130, 150, 20, bold=True)
    b.add_input("Madu Hutan", 300, 155, 280, 30)
    b.add_text("Deskripsi Singkat", 300, 200, 150, 20, bold=True)
    b.add_input("Madu yang dipanen liar dari hutan rimba", 300, 225, 280, 50)
    b.add_button("Tambah Kategori", 300, 290, 150, 35, bold=True)

    # Right Table
    b.add_card("DAFTAR KATEGORI PRODUK", 620, 100, 430, 500)
    b.add_text("KATEGORI", 640, 130, 150, 20, bold=True)
    b.add_text("JML PRODUK", 800, 130, 100, 20, bold=True)
    b.add_text("AKSI", 920, 130, 100, 20, bold=True)
    b.add_line(620, 160, 430, 1)

    b.add_text("Madu Hutan", 640, 175, 150, 25)
    b.add_text("3 Produk", 800, 175, 100, 25)
    b.add_button("Edit", 910, 175, 50, 25)
    b.add_button("Del", 970, 175, 50, 25)
    b.add_line(620, 210, 430, 1)

    b.add_text("Madu Budidaya", 640, 220, 150, 25)
    b.add_text("6 Produk", 800, 220, 100, 25)
    b.add_button("Edit", 910, 220, 50, 25)
    b.add_button("Del", 970, 220, 50, 25)
    b.add_line(620, 255, 430, 1)

    b.add_text("Paket Reseller", 640, 265, 150, 25)
    b.add_text("3 Produk", 800, 265, 100, 25)
    b.add_button("Edit", 910, 265, 50, 25)
    b.add_button("Del", 970, 265, 50, 25)
    b.add_line(620, 300, 430, 1)

    b.add_footer()
    b.save(os.path.join(dest_dir, "21_admin_kategori.drawio"))

    # 22. Admin Pesanan
    b = DrawioMockupBuilder("22_admin_pesanan", "page_admin_pesanan")
    b.add_header("Kelola Pesanan", is_admin=True)
    b.add_admin_sidebar("Kelola Pesanan")
    
    # Table content
    b.add_card("KELOLA PESANAN PELANGGAN", 280, 100, 770, 500)
    b.add_text("Daftar Transaksi Toko", 300, 120, 400, 30, bold=True)
    
    # Table headers
    b.add_text("INVOICE", 300, 160, 100, 20, bold=True)
    b.add_text("PELANGGAN", 390, 160, 130, 20, bold=True)
    b.add_text("TOTAL", 510, 160, 100, 20, bold=True)
    b.add_text("STATUS", 620, 160, 120, 20, bold=True)
    b.add_text("TINDAKAN / AKSI", 760, 160, 280, 20, bold=True)
    b.add_line(280, 190, 770, 1)

    # Row 1
    b.add_text("INV-001", 300, 200, 100, 25)
    b.add_text("Indra Pratama", 390, 200, 130, 25)
    b.add_text("Rp 305.245", 510, 200, 100, 25)
    b.add_text("Belum Bayar", 620, 200, 120, 25)
    b.add_button("Detail", 760, 200, 60, 25)
    b.add_line(280, 235, 770, 1)

    # Row 2
    b.add_text("INV-002", 300, 250, 100, 25)
    b.add_text("Siti Aminah", 390, 250, 130, 25)
    b.add_text("Rp 170.000", 510, 250, 100, 25)
    b.add_text("Sudah Bayar", 620, 250, 120, 25)
    b.add_button("Proses Pesanan", 760, 250, 110, 25, bold=True)
    b.add_button("Detail", 880, 250, 60, 25)
    b.add_line(280, 285, 770, 1)

    # Row 3
    b.add_text("INV-003", 300, 300, 100, 25)
    b.add_text("Budi Santoso", 390, 300, 130, 25)
    b.add_text("Rp 370.000", 510, 300, 100, 25)
    b.add_text("Di Proses", 620, 300, 120, 25)
    b.add_button("Selesai Pesanan", 760, 300, 110, 25, bold=True)
    b.add_button("Detail", 880, 300, 60, 25)
    b.add_line(280, 335, 770, 1)

    # Row 4
    b.add_text("INV-004", 300, 350, 100, 25)
    b.add_text("Wayan Sudarma", 390, 350, 130, 25)
    b.add_text("Rp 95.000", 510, 350, 100, 25)
    b.add_text("Selesai", 620, 350, 120, 25)
    b.add_button("Detail", 760, 350, 60, 25)
    b.add_line(280, 385, 770, 1)

    b.add_footer()
    b.save(os.path.join(dest_dir, "22_admin_pesanan.drawio"))

    # 23. Admin Artikel
    b = DrawioMockupBuilder("23_admin_artikel", "page_admin_artikel")
    b.add_header("Kelola Artikel", is_admin=True)
    b.add_admin_sidebar("Kelola Artikel")
    
    # Table content
    b.add_card("KELOLA ARTIKEL BLOG", 280, 100, 770, 500)
    b.add_text("Daftar Artikel & Berita", 300, 120, 400, 30, bold=True)
    b.add_button("+ Tambah Artikel Baru", 850, 120, 180, 30, bold=True)
    
    # Headers
    b.add_text("JUDUL ARTIKEL", 300, 160, 250, 20, bold=True)
    b.add_text("PENULIS", 570, 160, 100, 20, bold=True)
    b.add_text("KATEGORI", 690, 160, 120, 20, bold=True)
    b.add_text("TANGGAL", 830, 160, 100, 20, bold=True)
    b.add_text("AKSI", 950, 160, 90, 20, bold=True)
    b.add_line(280, 190, 770, 1)

    # Row 1
    b.add_text("Panen Raya Madu Kaliandra Musim Ini Berlimpah", 300, 200, 250, 25)
    b.add_text("Admin", 570, 200, 100, 25)
    b.add_text("Berita Desa", 690, 200, 120, 25)
    b.add_text("10 Jun 2026", 830, 200, 100, 25)
    b.add_button("Edit", 940, 200, 45, 25)
    b.add_button("Del", 990, 200, 45, 25)
    b.add_line(280, 235, 770, 1)

    # Row 2
    b.add_text("5 Manfaat Utama Madu Hutan Liar Bagi Imunitas", 300, 250, 250, 25)
    b.add_text("Admin", 570, 250, 100, 25)
    b.add_text("Kesehatan", 690, 250, 120, 25)
    b.add_text("28 Mei 2026", 830, 250, 100, 25)
    b.add_button("Edit", 940, 250, 45, 25)
    b.add_button("Del", 990, 250, 45, 25)
    b.add_line(280, 285, 770, 1)

    b.add_footer()
    b.save(os.path.join(dest_dir, "23_admin_artikel.drawio"))

    print("Successfully generated 23 .drawio wireframe files!")


if __name__ == "__main__":
    dest = os.path.join(os.path.dirname(os.path.abspath(__file__)), "desain")
    generate_all_mockups(dest)

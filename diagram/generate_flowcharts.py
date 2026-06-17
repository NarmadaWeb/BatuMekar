import os
import xml.etree.ElementTree as ET
import xml.dom.minidom as minidom

class DrawioBuilder:
    # Style constants matching kangkungku
    ST_START = "ellipse;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=2;fontColor=#000000;fontSize=12;fontStyle=1;align=center;"
    ST_END = "ellipse;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=2;fontColor=#000000;fontSize=12;fontStyle=1;align=center;"
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

    def __init__(self, name, page_id, page_w=880, page_h=1100):
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
        if kind == "start":
            w = w or 100
            h = h or 40
            style = self.ST_START
        elif kind == "end":
            w = w or 100
            h = h or 40
            style = self.ST_END
        elif kind == "process":
            w = w or 180
            h = h or 50
            style = self.ST_PROC
        elif kind == "process_rect":
            w = w or 180
            h = h or 50
            style = self.ST_PROC_RECT
        elif kind == "input":
            w = w or 180
            h = h or 50
            style = self.ST_INPUT
        elif kind == "output":
            w = w or 180
            h = h or 50
            style = self.ST_OUTPUT
        elif kind == "decision":
            w = w or 160
            h = h or 70
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
            w = w or 180
            h = h or 70
            style = self.ST_DOC
        elif kind == "trapezoid":
            w = w or 180
            h = h or 50
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
        print(f"Generated Draw.io XML at: {filepath}")


def build_sistem_lama(dest_dir):
    b = DrawioBuilder("Sistem Lama", "page-lama", 880, 1150)
    # Add Swimlanes
    b.add("lane_pelanggan", "PELANGGAN", "swimlane", 40, 20, 400, 1100)
    b.add("lane_penjual", "PENJUAL", "swimlane", 440, 20, 400, 1100)

    # Pelanggan lane nodes
    b.add("mulai", "Mulai", "start", 150, 30, parent="lane_pelanggan")
    b.add("kios", "Mendatangi tempat penjualan madu\nBatuMekar atau via WhatsApp", "trapezoid", 110, 100, parent="lane_pelanggan")
    b.add("tanya", "Menanyakan ketersediaan madu", "trapezoid", 110, 180, parent="lane_pelanggan")
    b.add("pesan", "Memesan madu", "trapezoid", 110, 260, parent="lane_pelanggan")
    
    b.add("stok_kosong", "Mendapatkan info stok kosong", "input", 110, 350, parent="lane_pelanggan")
    b.add("terima_madu", "Menerima madu", "trapezoid", 110, 540, parent="lane_pelanggan")
    b.add("bayar", "Melakukan pembayaran", "trapezoid", 110, 620, parent="lane_pelanggan")
    b.add("terima_nota", "Terima nota pembayaran", "trapezoid", 110, 790, parent="lane_pelanggan")
    b.add("nota_pelanggan", "Nota pembayaran", "document", 110, 870, parent="lane_pelanggan")
    b.add("selesai", "Selesai", "end", 150, 980, parent="lane_pelanggan")

    # Penjual lane nodes
    b.add("terima_pesan", "Menerima pesanan", "trapezoid", 110, 260, parent="lane_penjual")
    b.add("cek_stok", "Cek stok", "decision", 120, 340, parent="lane_penjual")
    b.add("catat_pesan", "Mencatat pesanan", "process", 110, 460, parent="lane_penjual")
    b.add("serah_madu", "Menyerahkan madu ke pelanggan", "trapezoid", 110, 540, parent="lane_penjual")
    b.add("terima_bayar", "Menerima pembayaran", "trapezoid", 110, 620, parent="lane_penjual")
    b.add("buat_nota", "Buat nota pembayaran", "trapezoid", 110, 700, parent="lane_penjual")
    b.add("nota_penjual", "Nota pembayaran", "document", 110, 780, parent="lane_penjual")

    # Links
    b.link("mulai", "kios")
    b.link("kios", "tanya")
    b.link("tanya", "pesan")
    b.link("pesan", "terima_pesan", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link("terima_pesan", "cek_stok")
    b.link("cek_stok", "stok_kosong", label="T", exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    b.link("cek_stok", "catat_pesan", label="Y", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link("stok_kosong", "selesai", exit_pt=(0, 0.5), entry_pt=(0, 0.5),
           points=[(90, 395), (90, 1020)])
    b.link("catat_pesan", "serah_madu")
    b.link("serah_madu", "terima_madu", exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    b.link("terima_madu", "bayar")
    b.link("bayar", "terima_bayar", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link("terima_bayar", "buat_nota")
    b.link("buat_nota", "nota_penjual")
    b.link("nota_penjual", "terima_nota", exit_pt=(0, 0.5), entry_pt=(1, 0.5))
    b.link("terima_nota", "nota_pelanggan")
    b.link("nota_pelanggan", "selesai")

    b.save(os.path.join(dest_dir, "01_sistem_lama.drawio"))


def build_sistem_baru(dest_dir):
    b = DrawioBuilder("Sistem Baru", "page-baru", 1000, 1150)
    # Add Swimlanes
    b.add("lane_pelanggan", "PELANGGAN", "swimlane", 40, 20, 300, 1100)
    b.add("lane_sistem", "SISTEM", "swimlane", 340, 20, 300, 1100)
    b.add("lane_admin", "ADMINISTRATOR", "swimlane", 640, 20, 300, 1100)

    # Pelanggan lane nodes
    b.add("mulai", "Mulai", "start", 100, 30, parent="lane_pelanggan")
    b.add("akses", "Mengakses website\nMadu BatuMekar", "trapezoid", 60, 100, parent="lane_pelanggan")
    b.add("pilih", "Memilih jenis produk madu\ndan kuantitas", "input", 60, 220, parent="lane_pelanggan")
    b.add("checkout", "Checkout pesanan &\nmengisi alamat", "input", 60, 340, parent="lane_pelanggan")
    b.add("upload_bukti", "Transfer pembayaran &\nunggah bukti transfer", "input", 60, 520, parent="lane_pelanggan")
    b.add("terima_produk", "Menerima produk madu\n& status 'Selesai'", "document", 60, 870, parent="lane_pelanggan")
    b.add("selesai", "Selesai", "end", 100, 980, parent="lane_pelanggan")

    # Sistem lane nodes
    b.add("db", "Database (MySQL)", "cylinder", 80, 100, parent="lane_sistem")
    b.add("simpan_checkout", "Menyimpan transaksi dengan\nstatus 'Belum Bayar' ke DB", "process", 60, 340, parent="lane_sistem")
    b.add("simpan_bukti", "Menyimpan bukti transfer\ndan update status ke DB", "process", 60, 520, parent="lane_sistem")
    b.add("proses_selesai", "Ubah status menjadi 'Selesai'\ndan kurangi stok di DB", "process", 60, 790, parent="lane_sistem")

    # Admin lane nodes
    b.add("notif_admin", "Menerima notifikasi\npembayaran masuk", "process", 60, 520, parent="lane_admin")
    b.add("verifikasi", "Memvalidasi bukti transfer\npembayaran", "process", 60, 610, parent="lane_admin")
    b.add("valid_dec", "Bukti valid?", "decision", 70, 690, parent="lane_admin")

    # Links
    b.link("mulai", "akses")
    b.link("akses", "pilih")
    b.link("pilih", "checkout")
    b.link("checkout", "simpan_checkout", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link("simpan_checkout", "upload_bukti", exit_pt=(0.5, 1), entry_pt=(0.5, 0),
           points=[(490, 470), (190, 470)])
    b.link("upload_bukti", "simpan_bukti", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link("simpan_bukti", "notif_admin", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link("notif_admin", "verifikasi")
    b.link("verifikasi", "valid_dec")
    b.link("valid_dec", "proses_selesai", label="Ya", exit_pt=(0, 0.5), entry_pt=(0.5, 0),
           points=[(490, 745)])
    b.link("valid_dec", "upload_bukti", label="Tidak", exit_pt=(1, 0.5), entry_pt=(1, 0.5),
           points=[(920, 745), (920, 490), (310, 490), (310, 565)])
    b.link("proses_selesai", "terima_produk", exit_pt=(0, 0.5), entry_pt=(1, 0.5),
           points=[(310, 835), (310, 905)])
    b.link("terima_produk", "selesai")

    # DB links
    b.link("akses", "db", exit_pt=(1, 0.5), entry_pt=(0, 0.5))
    b.link("simpan_checkout", "db", exit_pt=(1, 0.5), entry_pt=(1, 0.5),
           points=[(610, 385), (610, 150)])
    b.link("proses_selesai", "db", exit_pt=(1, 0.5), entry_pt=(1, 0.5),
           points=[(610, 835), (610, 150)])

    b.save(os.path.join(dest_dir, "02_sistem_baru.drawio"))


if __name__ == "__main__":
    import sys
    dest = os.path.join(os.path.dirname(os.path.abspath(__file__)), "flowchart")
    if len(sys.argv) > 1:
        dest = sys.argv[1]
    
    os.makedirs(dest, exist_ok=True)
    build_sistem_lama(dest)
    build_sistem_baru(dest)
    print("Successfully updated both system flowcharts to match kangkungku styles!")

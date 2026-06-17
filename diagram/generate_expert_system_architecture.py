import os
import xml.etree.ElementTree as ET
import xml.dom.minidom as minidom

class DrawioDiagramBuilder:
    """Builder for program architecture diagrams in Draw.io format."""
    
    ST_ROUNDED_BOX = "rounded=1;whiteSpace=wrap;html=1;arcSize=10;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;align=center;verticalAlign=middle;fontFamily=Helvetica;"
    EDGE_ORTHO = "edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#000000;strokeWidth=1.5;endArrow=classic;endSize=8;"

    def __init__(self, name, page_id, width=1200, height=800):
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

    def add_node(self, nid, label, x, y, w=120, h=50):
        self.nodes.append({
            "id": nid, "value": label, "style": self.ST_ROUNDED_BOX,
            "x": x, "y": y, "w": w, "h": h, "parent": "1"
        })

    def link_nodes(self, src, dst, points=None, exit_pt=None, entry_pt=None):
        edge_style = self.EDGE_ORTHO
        if exit_pt:
            edge_style += f"exitX={exit_pt[0]};exitY={exit_pt[1]};exitDx=0;exitDy=0;"
        if entry_pt:
            edge_style += f"entryX={entry_pt[0]};entryY={entry_pt[1]};entryDx=0;entryDy=0;"

        self.edges.append({
            "id": self._next_id("edge"), "value": "", "style": edge_style,
            "source": src, "target": dst, "points": points or []
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
        print(f"Generated Draw.io XML at: {filepath}")


def generate_admin_architecture(dest_dir):
    print("Generating Admin Program Architecture Diagram...")
    b = DrawioDiagramBuilder("Arsitektur Program Admin", "arch-admin", width=1200, height=700)
    
    # Coordinates mapping
    # 8 columns at bottom level (X coordinates)
    col_w = 110
    col_h = 50
    gap_x = 30
    
    # Compute coordinates
    cols_x = [50 + i * (col_w + gap_x) for i in range(8)]
    col_centers_x = [x + col_w / 2 for x in cols_x]
    
    # Level 1: Login
    b.add_node("login", "Login", x=545, y=50, w=110, h=50)
    
    # Level 2: Dashboard Admin
    b.add_node("dash_admin", "Dashboard Admin", x=545, y=150, w=110, h=50)
    
    # Level 3: Columns
    b.add_node("dash", "Dashboard", x=cols_x[0], y=270, w=col_w, h=col_h)
    b.add_node("dt_penyakit", "Data Penyakit", x=cols_x[1], y=270, w=col_w, h=col_h)
    b.add_node("dt_gejala", "Data Gejala", x=cols_x[2], y=270, w=col_w, h=col_h)
    b.add_node("dt_aturan", "Data Aturan", x=cols_x[3], y=270, w=col_w, h=col_h)
    b.add_node("dt_users", "Data Users", x=cols_x[4], y=270, w=col_w, h=col_h)
    b.add_node("lap_diagnosa", "Laporan Diagnosa", x=cols_x[5], y=270, w=col_w, h=col_h)
    b.add_node("pengaturan", "Pengaturan", x=cols_x[6], y=270, w=col_w, h=col_h)
    b.add_node("logout", "Log out", x=cols_x[7], y=270, w=col_w, h=col_h)
    
    # Level 4: Profil under Pengaturan
    b.add_node("profil", "Profil", x=cols_x[6], y=390, w=col_w, h=col_h)
    
    # Level 5: Ubah Password under Profil
    b.add_node("ubah_pass", "Ubah Password", x=cols_x[6], y=510, w=col_w, h=col_h)
    
    # Connections
    # Login -> Dashboard Admin
    b.link_nodes("login", "dash_admin")
    
    # Dashboard Admin -> Columns (with orthogonal points)
    # The split point Y coordinate is 220
    split_y = 220
    b.link_nodes("dash_admin", "dash", points=[(600, split_y), (col_centers_x[0], split_y)])
    b.link_nodes("dash_admin", "dt_penyakit", points=[(600, split_y), (col_centers_x[1], split_y)])
    b.link_nodes("dash_admin", "dt_gejala", points=[(600, split_y), (col_centers_x[2], split_y)])
    b.link_nodes("dash_admin", "dt_aturan", points=[(600, split_y), (col_centers_x[3], split_y)])
    b.link_nodes("dash_admin", "dt_users", points=[(600, split_y), (col_centers_x[4], split_y)])
    b.link_nodes("dash_admin", "lap_diagnosa", points=[(600, split_y), (col_centers_x[5], split_y)])
    b.link_nodes("dash_admin", "pengaturan", points=[(600, split_y), (col_centers_x[6], split_y)])
    b.link_nodes("dash_admin", "logout", points=[(600, split_y), (col_centers_x[7], split_y)])
    
    # Pengaturan -> Profil
    b.link_nodes("pengaturan", "profil")
    
    # Profil -> Ubah Password
    b.link_nodes("profil", "ubah_pass")
    
    b.save(os.path.join(dest_dir, "arsitektur_program_admin.drawio"))


def generate_user_architecture(dest_dir):
    print("Generating User Program Architecture Diagram...")
    b = DrawioDiagramBuilder("Arsitektur Program Pengguna", "arch-user", width=1100, height=500)
    
    # Coordinates mapping
    # 7 columns at bottom level (X coordinates)
    col_w = 110
    col_h = 50
    gap_x = 35
    
    cols_x = [60 + i * (col_w + gap_x) for i in range(7)]
    col_centers_x = [x + col_w / 2 for x in cols_x]
    
    # Level 1: Halaman Utama
    b.add_node("hal_utama", "Halaman Utama", x=495, y=50, w=110, h=50)
    
    # Level 2: Login
    b.add_node("login", "Login", x=495, y=150, w=110, h=50)
    
    # Level 3: Columns
    b.add_node("beranda", "Beranda", x=cols_x[0], y=270, w=col_w, h=col_h)
    b.add_node("diagnosa", "Diagnosa", x=cols_x[1], y=270, w=col_w, h=col_h)
    b.add_node("hasil_diagnosa", "Hasil Diagnosa", x=cols_x[2], y=270, w=col_w, h=col_h)
    b.add_node("ensiklopedia", "Ensiklopedia", x=cols_x[3], y=270, w=col_w, h=col_h)
    b.add_node("panduan", "Panduan", x=cols_x[4], y=270, w=col_w, h=col_h)
    b.add_node("riwayat", "Riwayat Diagnosa", x=cols_x[5], y=270, w=col_w, h=col_h)
    b.add_node("register", "Register", x=cols_x[6], y=270, w=col_w, h=col_h)
    
    # Connections
    # Halaman Utama -> Login
    b.link_nodes("hal_utama", "login")
    
    # Login -> Columns (with orthogonal points)
    # The split point Y coordinate is 220
    split_y = 220
    b.link_nodes("login", "beranda", points=[(550, split_y), (col_centers_x[0], split_y)])
    b.link_nodes("login", "diagnosa", points=[(550, split_y), (col_centers_x[1], split_y)])
    b.link_nodes("login", "hasil_diagnosa", points=[(550, split_y), (col_centers_x[2], split_y)])
    b.link_nodes("login", "ensiklopedia", points=[(550, split_y), (col_centers_x[3], split_y)])
    b.link_nodes("login", "panduan", points=[(550, split_y), (col_centers_x[4], split_y)])
    b.link_nodes("login", "riwayat", points=[(550, split_y), (col_centers_x[5], split_y)])
    b.link_nodes("login", "register", points=[(550, split_y), (col_centers_x[6], split_y)])
    
    b.save(os.path.join(dest_dir, "arsitektur_program_pengguna.drawio"))


if __name__ == "__main__":
    dest = os.path.join(os.path.dirname(os.path.abspath(__file__)), "arsitektur")
    generate_admin_architecture(dest)
    generate_user_architecture(dest)

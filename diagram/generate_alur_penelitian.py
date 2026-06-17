import os
import xml.etree.ElementTree as ET
import xml.dom.minidom as minidom

class DrawioBuilder:
    # Style constants
    ST_START = "rounded=1;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;align=center;"
    ST_END = "rounded=1;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;align=center;"
    ST_PROC = "rounded=0;whiteSpace=wrap;html=1;fillColor=#FFFFFF;strokeColor=#000000;strokeWidth=1.5;fontColor=#000000;fontSize=12;fontStyle=1;"
    ST_DEC = "strokeWidth=2;html=1;shape=mxgraph.flowchart.decision;whiteSpace=wrap;fillColor=#FFFFFF;strokeColor=#000000;fontColor=#000000;fontSize=12;fontStyle=1;"

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
            w = w or 200
            h = h or 50
            style = self.ST_PROC
        elif kind == "decision":
            w = w or 160
            h = h or 70
            style = self.ST_DEC
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

def build_alur_penelitian():
    b = DrawioBuilder("Alur Penelitian", "page-alur", 400, 1000)
    
    # Add Nodes
    b.add("mulai", "Mulai", "start", 150, 30)
    b.add("identifikasi", "Identifikasi Masalah &\nStudi Pustaka", "process", 100, 100)
    b.add("pengumpulan", "Pengumpulan Data:\nObservasi, Wawancara &\nStudi Literatur", "process", 100, 180)
    b.add("analisis", "Analisis Kebutuhan\nSistem & Data Pakar", "process", 100, 260)
    b.add("desain", "Desain Sistem:\nFlowchart, UML, ERD &\nBasis Pengetahuan CF", "process", 100, 340)
    b.add("implementasi", "Implementasi Sistem:\nCoding FastAPI (Python)\n& Frontend (Web)", "process", 100, 420)
    b.add("pengujian", "Pengujian Sistem:\nBlack Box Testing &\nEvaluasi Akurasi", "process", 100, 500)
    b.add("validasi", "Sistem Valid &\nAkurat?", "decision", 120, 580)
    b.add("analisis_hasil", "Analisis Hasil &\nPembahasan", "process", 100, 690)
    b.add("kesimpulan", "Penyusunan Laporan,\nKesimpulan & Saran", "process", 100, 770)
    b.add("selesai", "Selesai", "end", 150, 850)
    
    # Add Edges
    b.link("mulai", "identifikasi")
    b.link("identifikasi", "pengumpulan")
    b.link("pengumpulan", "analisis")
    b.link("analisis", "desain")
    b.link("desain", "implementasi")
    b.link("implementasi", "pengujian")
    b.link("pengujian", "validasi")
    b.link("validasi", "analisis_hasil", label="Ya", exit_pt=(0.5, 1), entry_pt=(0.5, 0))
    b.link("validasi", "desain", label="Tidak", exit_pt=(0, 0.5), entry_pt=(0, 0.5), points=[(50, 615), (50, 365)])
    b.link("analisis_hasil", "kesimpulan")
    b.link("kesimpulan", "selesai")
    
    filepath = "diagram/flowchart/00_alur_penelitian.drawio"
    b.save(filepath)

if __name__ == "__main__":
    build_alur_penelitian()

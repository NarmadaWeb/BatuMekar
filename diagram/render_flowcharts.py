import os
import subprocess
import time

FILES_TO_RENDER = [
    "diagram/flowchart/01_sistem_lama.drawio",
    "diagram/flowchart/02_sistem_baru.drawio",
    "diagram/flowchart/pelanggan/03_pelanggan_registrasi.drawio",
    "diagram/flowchart/pelanggan/04_pelanggan_login.drawio",
    "diagram/flowchart/pelanggan/05_katalog_pencarian.drawio",
    "diagram/flowchart/pelanggan/06_tambah_keranjang.drawio",
    "diagram/flowchart/pelanggan/07_checkout_pesanan.drawio",
    "diagram/flowchart/pelanggan/08_upload_bukti_bayar.drawio",
    "diagram/flowchart/admin/09_admin_kelola_produk.drawio",
    "diagram/flowchart/admin/10_admin_verifikasi_pembayaran.drawio",
    "diagram/flowchart/admin/11_admin_kelola_kategori.drawio",
    "diagram/flowchart/admin/12_admin_kelola_artikel.drawio"
]

def main():
    print(f"Starting bulk render for {len(FILES_TO_RENDER)} BatuMekar flowchart files...")
    
    # Configure node environment
    env = os.environ.copy()
    env["NODE_PATH"] = "/var/home/indra12/skripsi/ikin-printing/node_modules"
    
    node_script = "/var/home/indra12/skripsi/tembakau/scratch/render_diagram.js"
    
    for filepath in FILES_TO_RENDER:
        abs_path = os.path.abspath(filepath)
        if not os.path.exists(abs_path):
            print(f"Warning: File {filepath} not found on disk, skipping.")
            continue
            
        print(f"\n--- Rendering {filepath} ---")
        # Ensure port 8089 is free
        subprocess.run(["fuser", "-k", "8089/tcp"], capture_output=True)
        time.sleep(0.5)
        cmd = ["node", node_script, abs_path]
        try:
            res = subprocess.run(cmd, env=env, capture_output=True, text=True, check=True)
            print(res.stdout)
        except subprocess.CalledProcessError as e:
            print(f"Error rendering {filepath}:")
            print("STDOUT:", e.stdout)
            print("STDERR:", e.stderr)
            
        # Small delay to prevent resource contention
        time.sleep(1)

    print("\nBulk rendering completed!")

if __name__ == "__main__":
    main()

import fs from "node:fs/promises";
import { FileBlob, PresentationFile } from "@oai/artifact-tool";

const source = "C:/Users/DUNCAN/Desktop/laravel-12/Sistem-Rekrutmen-Terintegrasi.pptx";
const output = "C:/Users/DUNCAN/Desktop/laravel-12/Sistem-Rekrutmen-Terintegrasi-Revisi.pptx";
const qaDir = "C:/Users/DUNCAN/Desktop/laravel-12/.codex-ppt-revision/final";

const replacements = new Map([
  ["MENGAPA DIBUAT", "PERMINTAAN TIM REKRUTMEN"],
  ["Proses rekrutmen mudah kehilangan kendali saat data tersebar", "Tim Rekrutmen membutuhkan proses yang lebih mudah dipantau"],
  ["Data terpisah", "Monitoring pelamar"],
  ["Pelamar, jadwal, hasil tes, interview, dan offering tersimpan di titik yang berbeda.", "Data pelamar harus dapat dipantau dari masuk hingga keputusan akhir dalam satu sistem."],
  ["Tahapan tidak konsisten", "Blast WhatsApp ringkas"],
  ["Kandidat dapat terlewat, diproses ganda, atau berpindah tahap tanpa validasi yang sama.", "Tim membutuhkan pengiriman undangan massal tanpa menghubungi kandidat satu per satu."],
  ["Keputusan lambat", "Pengisian mandiri"],
  ["HR membutuhkan waktu lebih lama untuk menyusun status kandidat dan menentukan prioritas.", "Kandidat perlu mengisi dan melengkapi data pribadinya sendiri melalui tautan yang aman."],
  ["Risiko akses", "Tindak lanjut terlihat"],
  ["Tanpa pembatasan perusahaan dan audit, data sensitif lebih sulit dipertanggungjawabkan.", "Status pengisian, jadwal, hasil seleksi, dan pekerjaan tertunda harus mudah diketahui."],
  ["Masalah utamanya bukan kekurangan data—melainkan tidak adanya satu alur kerja yang menyatukan data tersebut.", "Permintaan utamanya: pekerjaan administrasi lebih ringkas, sementara setiap kandidat tetap terlihat dan dapat ditindaklanjuti."],
  ["Mengubah aktivitas administratif menjadi pipeline keputusan", "Menghubungkan komunikasi, data kandidat, dan keputusan"],
  ["Status kandidat dicari secara manual", "Pesan kandidat dikirim satu per satu"],
  ["Jadwal dan hasil sulit ditelusuri", "Data kandidat dikumpulkan manual"],
  ["Laporan terlambat dan rawan berbeda", "Status kandidat sulit dipantau"],
  ["Satu profil kandidat sepanjang proses", "Blast WhatsApp dari daftar kandidat"],
  ["Tahapan tervalidasi dan dapat dipantau", "Kandidat mengisi data secara mandiri"],
  ["Insight siap digunakan untuk tindakan", "Progress dan tindak lanjut terpantau"],
  ["Satu kandidat bergerak melalui alur seleksi yang utuh", "Komunikasi dan data kandidat masuk ke satu alur kerja"],
  ["Permintaan\nkandidat", "Siapkan\nkandidat"],
  ["Data\npelamar", "Pilih\ntarget"],
  ["Test Zoom", "Blast WA"],
  ["Test MMPI", "Isi data"],
  ["Interview", "Pantau"],
  ["Review\nmanagement", "Proses\nseleksi"],
  ["Offering\nletter", "Keputusan\nakhir"],
  ["Setiap tahap memperbarui status kandidat dan menyiapkan konteks untuk keputusan berikutnya.", "Pesan dikirim melalui antrean; kandidat mengisi data sendiri; Tim Rekrutmen memantau kelengkapan dan melanjutkan seleksi."],
  ["Fitur dibangun mengikuti pekerjaan nyata tim rekrutmen", "Sistem menerjemahkan kebutuhan Tim Rekrutmen menjadi alur kerja"],
  ["Data & master posisi", "Monitoring kandidat"],
  ["Profil pelamar, perusahaan, posisi, serta spesifikasi kualifikasi manual per posisi.", "Data, kelengkapan formulir, tahapan seleksi, dan status akhir terlihat dalam satu tempat."],
  ["Penjadwalan seleksi", "Blast WhatsApp"],
  ["Pengelolaan Zoom, MMPI, interview, kehadiran, reschedule, dan dokumen kandidat.", "Kirim pesan massal kepada kandidat terpilih melalui queue agar layar tetap responsif."],
  ["Keputusan akhir", "Portal kandidat mandiri"],
  ["Review management dan offering letter terhubung dengan riwayat tahapan kandidat.", "Token dan tautan aman memungkinkan kandidat mengisi data serta mengunggah dokumen sendiri."],
  ["Komunikasi terantre", "Seleksi terintegrasi"],
  ["Pengiriman pesan dipindahkan ke queue agar proses layar tetap cepat dan kegagalan dapat dipantau.", "Jadwal tes, kehadiran, interview, review management, dan offering tercatat berurutan."],
  ["Laporan terkontrol", "Audit & kontrol akses"],
  ["Report, export, dan download melalui permission serta audit aktivitas.", "Akses lintas perusahaan, export, download, dan perubahan data dibatasi serta diaudit."],
  ["Dashboard insight", "Dashboard tindakan"],
  ["Pipeline, konversi, distribusi perusahaan, tren pelamar, dan action center.", "Pipeline, kandidat tertunda, tren, konversi, dan pekerjaan prioritas siap ditindaklanjuti."],
  ["Mengelola data, jadwal, hasil, komunikasi, dan tindak lanjut kandidat.", "Mengirim blast WA, memantau pengisian data, mengelola jadwal, hasil, dan tindak lanjut."],
  ["Mengisi data melalui token aman dan mengikuti informasi tahapan secara lebih jelas.", "Mengisi data pribadi dan mengunggah dokumen sendiri melalui tautan dengan token aman."],
  ["Sistem menciptakan standar kerja yang dapat berkembang", "Pekerjaan lebih ringkas tanpa kehilangan kendali"],
  ["Data dan status tersedia tanpa konsolidasi manual.", "Blast WA dan pengumpulan data tidak lagi dikerjakan satu per satu."],
  ["Tahapan dan aturan digunakan sama oleh seluruh pengguna.", "Setiap kandidat bergerak melalui tahapan dan aturan yang sama."],
  ["Dashboard dan audit memberi dasar evaluasi proses.", "Progress pengisian dan pipeline memberi dasar tindakan harian."],
  ["Satu sistem untuk\nmenjaga setiap kandidat\ntetap terlihat.", "Komunikasi lebih ringkas.\nData diisi mandiri.\nKandidat tetap terpantau."],
  ["Tujuan akhirnya bukan hanya mendigitalisasi formulir, tetapi membantu tim rekrutmen bekerja lebih cepat, membuat keputusan lebih baik, dan mempertanggungjawabkan setiap proses.", "Sistem ini menjawab kebutuhan Tim Rekrutmen: mengirim informasi secara massal, memantau data pelamar, memberi ruang kandidat mengisi data sendiri, dan menjaga seluruh proses seleksi dapat ditindaklanjuti."],
  ["Langkah berikutnya: gunakan data dashboard untuk evaluasi rutin dan peningkatan proses seleksi.", "Gunakan dashboard dan audit recruitment sebagai dasar evaluasi rutin, pembagian prioritas, dan peningkatan proses seleksi."],
]);

const presentation = await PresentationFile.importPptx(await FileBlob.load(source));
const snapshot = await presentation.inspect({ kind: "textbox", include: "id,slide,text", maxChars: 100000 });
for (const line of snapshot.ndjson.split("\n").filter(Boolean)) {
  const item = JSON.parse(line);
  if (replacements.has(item.text)) {
    presentation.resolve(item.id).text = replacements.get(item.text);
  }
}

await fs.mkdir(qaDir, { recursive: true });
for (const [index, slide] of presentation.slides.items.entries()) {
  const stem = `slide-${String(index + 1).padStart(2, "0")}`;
  const png = await presentation.export({ slide, format: "png", scale: 1 });
  await fs.writeFile(`${qaDir}/${stem}.png`, new Uint8Array(await png.arrayBuffer()));
  const layout = await slide.export({ format: "layout" });
  await fs.writeFile(`${qaDir}/${stem}.layout.json`, await layout.text());
}
const montage = await presentation.export({ format: "webp", montage: true, scale: 1 });
await fs.writeFile(`${qaDir}/montage.webp`, new Uint8Array(await montage.arrayBuffer()));
const finalInspect = await presentation.inspect({ kind: "slide,textbox,shape", include: "id,slide,name,bbox,text", maxChars: 100000 });
await fs.writeFile(`${output}.inspect.ndjson`, finalInspect.ndjson);
const pptx = await PresentationFile.exportPptx(presentation);
await pptx.save(output);

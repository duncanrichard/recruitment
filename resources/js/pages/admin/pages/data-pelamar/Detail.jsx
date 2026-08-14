import React, { useEffect, useMemo, useState } from "react";

export default function DetailDataPelamarPage({ id, onBack }) {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState("utama");

    const fetchDetail = async () => {
        if (!id) {
            setLoading(false);
            return;
        }

        setLoading(true);

        try {
            const response = await fetch(`/admin/data-pelamar/${id}/detail-data`, {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || "Data detail pelamar tidak ditemukan.");
                setData(null);
                return;
            }

            if (result.success) {
                setData(result.data || null);
            } else {
                alert(result.message || "Data detail pelamar tidak ditemukan.");
                setData(null);
            }
        } catch (error) {
            console.error("Gagal mengambil detail pelamar:", error);
            alert("Terjadi kesalahan saat mengambil detail pelamar.");
            setData(null);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchDetail();
    }, [id]);

    const rel = useMemo(() => {
        return {
            posisi: getRelation(data, ["posisi"]),
            perusahaan: getRelation(data, ["perusahaan"]),
            pendidikan: getRelation(data, ["pendidikan"]),
            agama: getRelation(data, ["agama"]),
            kewarganegaraan: getRelation(data, ["kewarganegaraan"]),
            statusPernikahan: getRelation(data, [
                "status_pernikahan",
                "statusPernikahan",
            ]),
            sumberInformasi: getRelation(data, [
                "sumber_informasi",
                "sumberInformasi",
            ]),
            riwayatKeluarga: getRelation(data, [
                "riwayat_keluarga",
                "riwayatKeluarga",
            ]),
            riwayatKesehatan: getRelation(data, [
                "riwayat_kesehatan",
                "riwayatKesehatan",
            ]),
            riwayatPekerjaan: getRelation(data, [
                "riwayat_pekerjaan",
                "riwayatPekerjaan",
            ]),
            kesiapanBekerja: getRelation(data, [
                "kesiapan_bekerja",
                "kesiapanBekerja",
            ]),
            saudaraKandung: getRelation(data, [
                "saudara_kandung",
                "saudaraKandung",
            ]),
            saudaraIpar: getRelation(data, ["saudara_ipar", "saudaraIpar"]),
        };
    }, [data]);

    if (loading) {
        return (
            <div className="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
                <p className="text-sm font-bold text-slate-500">
                    Memuat detail data pelamar...
                </p>
            </div>
        );
    }

    if (!data) {
        return (
            <div className="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
                <button
                    type="button"
                    onClick={onBack}
                    className="mb-5 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                >
                    ← Kembali
                </button>

                <p className="text-sm font-bold text-slate-500">
                    Data pelamar tidak ditemukan.
                </p>
            </div>
        );
    }

    const keluarga = normalizeObject(rel.riwayatKeluarga);
    const kesehatan = normalizeObject(rel.riwayatKesehatan);
    const kesiapan = normalizeObject(rel.kesiapanBekerja);

    const saudaraKandung = normalizeArray(rel.saudaraKandung);
    const saudaraIpar = normalizeArray(rel.saudaraIpar);
    const riwayatPekerjaan = normalizeArray(rel.riwayatPekerjaan);
    const dokumenInterview = normalizeDokumenInterview(data);

    const kontakDarurat = normalizeArrayFromJson(keluarga.kontak_darurat);
    const hubunganKerabatInstansi = normalizeArrayFromJson(
        keluarga.hubungan_kerabat_instansi
    );

    const tabs = [
        {
            key: "utama",
            label: "Data Utama",
            content: (
                <SubTabs
                    tabs={[
                        {
                            key: "lamaran",
                            label: "Lamaran",
                            content: (
                                <InfoGrid>
                                    <InfoItem
                                        label="Posisi"
                                        value={
                                            data.posisi_label ||
                                            rel.posisi?.nama_posisi ||
                                            data.posisi_yang_dilamar
                                        }
                                    />
                                    <InfoItem
                                        label="Perusahaan"
                                        value={
                                            data.perusahaan_label ||
                                            rel.perusahaan?.nama_perusahaan ||
                                            data.perusahaan_dilamar
                                        }
                                    />
                                    <InfoItem
                                        label="Sumber Informasi"
                                        value={
                                            data.sumber_informasi_label ||
                                            rel.sumberInformasi?.informasi ||
                                            data.sumber_informasi_id
                                        }
                                    />
                                    <InfoItem
                                        label="Tanggal Skrining"
                                        value={formatDate(data.tanggal_skrining)}
                                    />
                                    <InfoItem label="Token" value={data.token} />
                                    <InfoItem
                                        label="URL Pendaftaran"
                                        value={data.pendaftaran_url}
                                        wide
                                    />
                                </InfoGrid>
                            ),
                        },
                        {
                            key: "pribadi",
                            label: "Pribadi",
                            content: (
                                <InfoGrid>
                                    <InfoItem label="Nama Lengkap" value={data.nama_lengkap} />
                                    <InfoItem label="Nama Panggil" value={data.nama_panggil} />
                                    <InfoItem label="Email" value={data.email} />
                                    <InfoItem label="No. WhatsApp" value={data.no_wa} />
                                    <InfoItem label="Tempat Lahir" value={data.tempat_lahir} />
                                    <InfoItem label="Tanggal Lahir" value={formatDate(data.tanggal_lahir)} />
                                    <InfoItem label="Jenis Kelamin" value={data.jenis_kelamin} />
                                    <InfoItem label="Golongan Darah" value={data.gol_darah} />
                                    <InfoItem label="Tinggi Badan" value={withSuffix(data.tinggi_badan, "cm")} />
                                    <InfoItem label="Berat Badan" value={withSuffix(data.berat_badan, "kg")} />
                                </InfoGrid>
                            ),
                        },
                        {
                            key: "pendidikan",
                            label: "Pendidikan",
                            content: (
                                <InfoGrid>
                                    <InfoItem
                                        label="Pendidikan"
                                        value={
                                            data.pendidikan_label ||
                                            rel.pendidikan?.pendidikan ||
                                            data.pendidikan_id
                                        }
                                    />
                                    <InfoItem label="Jurusan" value={data.jurusan} />
                                    <InfoItem label="Nama Institusi" value={data.nama_institusi} />
                                    <InfoItem
                                        label="Agama"
                                        value={data.agama_label || rel.agama?.agama || data.agama_id}
                                    />
                                    <InfoItem
                                        label="Kewarganegaraan"
                                        value={
                                            data.kewarganegaraan_label ||
                                            rel.kewarganegaraan?.kewarganegaraan ||
                                            data.kewarganegaraan_id
                                        }
                                    />
                                    <InfoItem
                                        label="Status Pernikahan"
                                        value={
                                            data.status_pernikahan_label ||
                                            rel.statusPernikahan?.status_pernikahan ||
                                            data.status_pernikahan_id
                                        }
                                    />
                                </InfoGrid>
                            ),
                        },
                        {
                            key: "alamat",
                            label: "Alamat",
                            content: (
                                <InfoGrid>
                                    <InfoItem label="Alamat KTP" value={data.alamat_ktp} wide />
                                    <InfoItem label="Alamat Domisili" value={data.alamat_domisili} wide />
                                    <InfoItem label="Provinsi ID" value={data.provinsi_id} />
                                    <InfoItem label="Kabupaten ID" value={data.kabupaten_id} />
                                    <InfoItem label="Kecamatan ID" value={data.kecamatan_id} />
                                    <InfoItem label="Kelurahan ID" value={data.kelurahan_id} />
                                </InfoGrid>
                            ),
                        },
                    ]}
                />
            ),
        },
        {
            key: "keluarga",
            label: "Keluarga",
            content: (
                <SubTabs
                    tabs={[
                        {
                            key: "orang-tua",
                            label: "Orang Tua",
                            content: (
                                <InfoGrid>
                                    <InfoItem label="Nama Ayah" value={keluarga.nama_ayah || keluarga.nama_ayah_kandung} />
                                    <InfoItem label="NIK Ayah" value={keluarga.nik_ayah} />
                                    <InfoItem label="Tempat Lahir Ayah" value={keluarga.tempat_lahir_ayah} />
                                    <InfoItem label="Tanggal Lahir Ayah" value={formatDate(keluarga.tanggal_lahir_ayah)} />
                                    <InfoItem label="Pekerjaan Ayah" value={keluarga.pekerjaan_ayah || keluarga.pekerjaan_ayah_kandung} />
                                    <InfoItem label="No. HP Ayah" value={keluarga.no_hp_ayah} />
                                    <InfoItem label="Alamat Ayah" value={keluarga.alamat_ayah} wide />

                                    <InfoItem label="Nama Ibu" value={keluarga.nama_ibu || keluarga.nama_ibu_kandung} />
                                    <InfoItem label="NIK Ibu" value={keluarga.nik_ibu} />
                                    <InfoItem label="Tempat Lahir Ibu" value={keluarga.tempat_lahir_ibu} />
                                    <InfoItem label="Tanggal Lahir Ibu" value={formatDate(keluarga.tanggal_lahir_ibu)} />
                                    <InfoItem label="Pekerjaan Ibu" value={keluarga.pekerjaan_ibu || keluarga.pekerjaan_ibu_kandung} />
                                    <InfoItem label="No. HP Ibu" value={keluarga.no_hp_ibu} />
                                    <InfoItem label="Alamat Ibu" value={keluarga.alamat_ibu} wide />
                                </InfoGrid>
                            ),
                        },
                        {
                            key: "pasangan",
                            label: "Pasangan & Mertua",
                            content: (
                                <InfoGrid>
                                    <InfoItem label="Nama Suami/Istri" value={keluarga.nama_suami_istri} />
                                    <InfoItem label="Pekerjaan Suami/Istri" value={keluarga.pekerjaan_suami_istri} />
                                    <InfoItem label="Telepon Suami/Istri" value={keluarga.tlpn_suami_istri} />
                                    <InfoItem label="Nama Bapak Mertua" value={keluarga.nama_bapak_mertua} />
                                    <InfoItem label="Pekerjaan Bapak Mertua" value={keluarga.pekerjaan_bapak_mertua} />
                                    <InfoItem label="Nama Ibu Mertua" value={keluarga.nama_ibu_mertua} />
                                    <InfoItem label="Pekerjaan Ibu Mertua" value={keluarga.pekerjaan_ibu_mertua} />
                                </InfoGrid>
                            ),
                        },
                        {
                            key: "saudara-kandung",
                            label: "Saudara Kandung",
                            content: (
                                <DataTableBlock
                                    emptyText="Data saudara kandung belum tersedia."
                                    data={saudaraKandung}
                                    columns={[
                                        ["Nama", "nama_saudara_kandung"],
                                        ["Hubungan", "hubungan"],
                                        ["Jenis Kelamin", "jenis_kelamin"],
                                        ["Pekerjaan", "pekerjaan"],
                                        ["No. HP", "no_hp"],
                                        ["Alamat", "alamat"],
                                    ]}
                                />
                            ),
                        },
                        {
                            key: "saudara-ipar",
                            label: "Saudara Ipar",
                            content: (
                                <DataTableBlock
                                    emptyText="Data saudara ipar belum tersedia."
                                    data={saudaraIpar}
                                    columns={[
                                        ["Nama", "nama_saudara_ipar"],
                                        ["Hubungan", "hubungan"],
                                        ["Jenis Kelamin", "jenis_kelamin"],
                                        ["Pekerjaan", "pekerjaan"],
                                        ["No. HP", "no_hp"],
                                        ["Alamat", "alamat"],
                                    ]}
                                />
                            ),
                        },
                        {
                            key: "kontak-darurat",
                            label: "Kontak Darurat",
                            content: (
                                <DataTableBlock
                                    emptyText="Kontak darurat belum diisi."
                                    data={kontakDarurat}
                                    columns={[
                                        ["Nama", "nama"],
                                        ["Status", "status"],
                                        [
                                            "No. Telepon",
                                            (item) =>
                                                item.nomor ||
                                                item.no_tlpn ||
                                                item.no_telp ||
                                                item.telepon,
                                        ],
                                    ]}
                                />
                            ),
                        },
                        {
                            key: "kerabat-instansi",
                            label: "Kerabat Instansi",
                            content: (
                                <DataTableBlock
                                    emptyText="Hubungan kerabat instansi belum diisi."
                                    data={hubunganKerabatInstansi}
                                    columns={[
                                        ["Nama", (item) => item.nama || item.name],
                                        ["Status / Hubungan", (item) => item.status || item.hubungan],
                                        ["Keterangan", (item) => item.keterangan || item.nomor || item.no_tlpn],
                                    ]}
                                />
                            ),
                        },
                    ]}
                />
            ),
        },
        {
            key: "kesehatan",
            label: "Kesehatan",
            content: (
                <SubTabs
                    tabs={[
                        {
                            key: "fisik",
                            label: "Kondisi Fisik",
                            content: (
                                <InfoGrid>
                                    <InfoItem label="Buta Warna" value={kesehatan.buta_warna} />
                                    <InfoItem
                                        label="Opsi Kacamata"
                                        value={
                                            kesehatan.opsi_kacamata?.opsi ||
                                            kesehatan.opsiKacamata?.opsi ||
                                            kesehatan.opsi_kacamata?.nama ||
                                            kesehatan.opsiKacamata?.nama ||
                                            kesehatan.opsi_kacamata_id
                                        }
                                    />
                                    <InfoItem label="Alat Bantu Dengar" value={kesehatan.alat_bantu_dengar} />
                                    <InfoItem label="Menulis Dengan Tangan" value={kesehatan.menulis_dengan_tangan} />
                                    <InfoItem label="Sering Gemetar" value={kesehatan.sering_gemetar} />
                                    <InfoItem label="Tangan Berkeringat" value={kesehatan.tangan_sering_berkeringat} />
                                </InfoGrid>
                            ),
                        },
                        {
                            key: "penyakit",
                            label: "Penyakit & Alergi",
                            content: (
                                <InfoGrid>
                                    <InfoItem label="Penyakit Menular" value={kesehatan.penyakit_menular} />
                                    <InfoItem label="Program Keahlian" value={kesehatan.program_keahlian} />
                                    <InfoItem label="Punya Alergi" value={kesehatan.punya_alergi} />
                                    <InfoItem label="Nama Alergi" value={kesehatan.nama_alergi} wide />
                                    <InfoItem label="Penyakit Genetik" value={kesehatan.punya_penyakit_genetik} />
                                    <InfoItem label="Nama Penyakit" value={kesehatan.nama_penyakit} wide />
                                    <InfoItem label="Riwayat Kronis" value={kesehatan.riwayat_kronis} />
                                </InfoGrid>
                            ),
                        },
                        {
                            key: "tindakan",
                            label: "Tindakan Medis",
                            content: (
                                <InfoGrid>
                                    <InfoItem label="Pengobatan Psikolog" value={kesehatan.pengobatan_psikolog} />
                                    <InfoItem label="Kapan Dilakukan" value={kesehatan.kapan_dilakukan} wide />
                                    <InfoItem label="Pernah Kecelakaan" value={kesehatan.pernah_kecelakaan} />
                                    <InfoItem label="Bagian Tubuh Kecelakaan" value={kesehatan.bagian_tubuh_kecelakaan} wide />
                                    <InfoItem label="Pernah Operasi" value={kesehatan.pernah_operasi} />
                                    <InfoItem label="Diagnosa Dokter" value={kesehatan.diagnosa_dokter} wide />
                                </InfoGrid>
                            ),
                        },
                    ]}
                />
            ),
        },
        {
            key: "pekerjaan",
            label: "Pekerjaan",
            content: (
                <DataTableBlock
                    emptyText="Data riwayat pekerjaan belum tersedia."
                    data={riwayatPekerjaan}
                    columns={[
                        ["Perusahaan", "nama_perusahaan"],
                        ["Posisi", (item) => item.posisi_pekerjaan_terakhir || item.posisi_pekerjaan],
                        ["Bidang", "bidang_pekerjaan"],
                        ["Lokasi", "lokasi_perusahaan"],
                        [
                            "Periode",
                            (item) =>
                                `${show(formatDate(item.periode_kerja_awal))} - ${show(
                                    formatDate(item.periode_kerja_akhir)
                                )}`,
                        ],
                        ["Gaji", (item) => formatMoney(item.gaji_terakhir)],
                        ["Alasan Berhenti", "alasan_berhenti"],
                    ]}
                />
            ),
        },
        {
            key: "kesiapan",
            label: "Kesiapan",
            content: (
                <InfoGrid>
                    <InfoItem label="Kapan Siap Bekerja" value={kesiapan.kapan_siap_bekerja} />
                    <InfoItem label="Ekspektasi Gaji" value={formatMoney(kesiapan.ekspektasi_gaji || kesiapan.expetasi_gaji)} />
                    <InfoItem label="Penempatan" value={kesiapan.penempatan} wide />
                    <InfoItem label="Proses Backing" value={kesiapan.proses_backing || kesiapan.proses_bckhng} />
                    <InfoItem label="Dapat Dipertanggungjawabkan" value={kesiapan.dapat_dipertanggung_jawabkan} />
                    <InfoItem label="Bersedia Training" value={kesiapan.bersedia_training} />
                </InfoGrid>
            ),
        },
        {
            key: "dokumen",
            label: "Dokumen Interview",
            content: <DokumenInterviewBlock dokumen={dokumenInterview} />,
        },
        {
            key: "sistem",
            label: "Sistem",
            content: (
                <InfoGrid>
                    <InfoItem label="ID" value={data.id} />
                    <InfoItem label="Status Aktif" value={data.str_aktif} />
                    <InfoItem label="Dibuat Pada" value={formatDateTime(data.created_at)} />
                    <InfoItem label="Diperbarui Pada" value={formatDateTime(data.updated_at)} />
                    <InfoItem label="Dihapus Pada" value={formatDateTime(data.deleted_at)} />
                    <InfoItem label="Dihapus Oleh" value={data.deleted_by} />
                </InfoGrid>
            ),
        },
    ];

    const activeTabData = tabs.find((tab) => tab.key === activeTab) || tabs[0];

    return (
        <div className="space-y-6">
            <ProfileHeader
                data={data}
                posisi={data.posisi_label || rel.posisi?.nama_posisi}
                perusahaan={data.perusahaan_label || rel.perusahaan?.nama_perusahaan}
                onBack={onBack}
            />

            <SummaryCards
                items={[
                    { label: "WhatsApp", value: data.no_wa },
                    { label: "Email", value: data.email },
                    { label: "Tanggal Skrining", value: formatDate(data.tanggal_skrining) },
                    { label: "Status", value: data.str_aktif },
                ]}
            />

            <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-100 bg-white px-4 py-4">
                    <div className="flex gap-2 overflow-x-auto pb-1">
                        {tabs.map((tab) => {
                            const isActive = activeTab === tab.key;

                            return (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => setActiveTab(tab.key)}
                                    className={`shrink-0 rounded-2xl px-4 py-3 text-sm font-black transition ${
                                        isActive
                                            ? "bg-slate-950 text-white shadow-lg shadow-slate-200"
                                            : "bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-950"
                                    }`}
                                >
                                    {tab.label}
                                </button>
                            );
                        })}
                    </div>
                </div>

                <div className="bg-slate-50/40 p-5 sm:p-6">
                    <div className="mb-5">
                        <h2 className="text-lg font-black text-slate-950">
                            {activeTabData.label}
                        </h2>
                        <p className="mt-1 text-sm font-bold text-slate-500">
                            Pilih sub tab di bawah untuk melihat detail data.
                        </p>
                    </div>

                    {activeTabData.content}
                </div>
            </div>
        </div>
    );
}


function DokumenInterviewBlock({ dokumen }) {
    const fileCv = dokumen?.file_cv || dokumen?.fileCv || dokumen?.file_cv_url || dokumen?.fileCvUrl || null;
    const fileFoto = dokumen?.file_foto || dokumen?.fileFoto || dokumen?.file_foto_url || dokumen?.fileFotoUrl || null;
    const hasDocument = Boolean(fileCv || fileFoto);

    if (!hasDocument) {
        return (
            <div className="rounded-[1.5rem] border border-slate-200 bg-white p-5">
                <p className="text-sm font-black text-slate-700">
                    Dokumen interview belum tersedia.
                </p>
                <p className="mt-2 text-sm font-semibold leading-6 text-slate-500">
                    Jika kandidat sudah upload CV atau foto dari halaman Cek Tahapan, dokumen akan muncul di sini.
                </p>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <InfoGrid>
                <InfoItem
                    label="Judul Interview"
                    value={dokumen?.judul_interview || dokumen?.judulInterview}
                />
                <InfoItem
                    label="Jadwal Interview"
                    value={formatDateTime(
                        dokumen?.jadwal_interview ||
                            dokumen?.jadwalInterview ||
                            dokumen?.tanggal_interview ||
                            dokumen?.tanggalInterview
                    )}
                />
                <InfoItem
                    label="ID Jadwal Interview Kandidat"
                    value={
                        dokumen?.jadwal_interview_kandidat_id ||
                        dokumen?.jadwalInterviewKandidatId
                    }
                />
            </InfoGrid>

            <div className="grid gap-4 md:grid-cols-2">
                <DokumenCard
                    title="CV Interview"
                    description="Dokumen CV yang di-upload kandidat pada halaman Cek Tahapan."
                    url={fileCv}
                    emptyText="CV belum di-upload."
                    type="document"
                />

                <DokumenCard
                    title="Foto Interview"
                    description="Foto yang di-upload kandidat pada halaman Cek Tahapan."
                    url={fileFoto}
                    emptyText="Foto belum di-upload."
                    type="image"
                />
            </div>
        </div>
    );
}

function DokumenCard({ title, description, url, emptyText, type = "document" }) {
    if (!url) {
        return (
            <div className="rounded-[1.5rem] border border-slate-200 bg-white p-5">
                <p className="text-xs font-black uppercase tracking-wide text-slate-400">
                    {title}
                </p>
                <p className="mt-2 text-sm font-bold text-slate-500">
                    {emptyText || "Dokumen belum tersedia."}
                </p>
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
            {type === "image" && isImageUrl(url) && (
                <a href={url} target="_blank" rel="noopener noreferrer">
                    <img
                        src={url}
                        alt={title}
                        className="h-56 w-full object-cover"
                    />
                </a>
            )}

            <div className="p-5">
                <p className="text-xs font-black uppercase tracking-wide text-slate-400">
                    {title}
                </p>

                <p className="mt-2 text-sm font-semibold leading-6 text-slate-500">
                    {description}
                </p>

             {/*    <p className="mt-3 break-all rounded-2xl bg-slate-50 p-3 text-xs font-bold leading-5 text-slate-600">
                    {url}
                </p> */}

                <a
                    href={url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="mt-4 inline-flex rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-black text-white transition hover:bg-indigo-700"
                >
                    Buka Dokumen
                </a>
            </div>
        </div>
    );
}

function SubTabs({ tabs = [] }) {
    const [activeSubTab, setActiveSubTab] = useState(tabs?.[0]?.key || "");

    useEffect(() => {
        setActiveSubTab(tabs?.[0]?.key || "");
    }, [tabs]);

    const activeTabData =
        tabs.find((tab) => tab.key === activeSubTab) || tabs[0];

    if (!tabs.length) {
        return (
            <p className="rounded-2xl bg-white p-4 text-sm font-bold text-slate-500">
                Data belum tersedia.
            </p>
        );
    }

    return (
        <div className="rounded-[1.5rem] border border-slate-200 bg-white">
            <div className="border-b border-slate-100 px-4 py-3">
                <div className="flex gap-2 overflow-x-auto pb-1">
                    {tabs.map((tab) => {
                        const isActive = activeSubTab === tab.key;

                        return (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => setActiveSubTab(tab.key)}
                                className={`shrink-0 rounded-xl px-4 py-2.5 text-sm font-black transition ${
                                    isActive
                                        ? "bg-indigo-600 text-white shadow-md shadow-indigo-100"
                                        : "bg-slate-50 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700"
                                }`}
                            >
                                {tab.label}
                            </button>
                        );
                    })}
                </div>
            </div>

            <div className="p-4 sm:p-5">
                {activeTabData?.content}
            </div>
        </div>
    );
}

function ProfileHeader({ data, posisi, perusahaan, onBack }) {
    const initial = show(data.nama_lengkap).charAt(0).toUpperCase();

    return (
        <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <div className="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-900 px-6 py-7 text-white">
                <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-4">
                        <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-3xl bg-white/15 text-2xl font-black ring-1 ring-white/20">
                            {initial}
                        </div>

                        <div>
                            <p className="text-xs font-black uppercase tracking-[0.22em] text-indigo-200">
                                Profil Pelamar
                            </p>

                            <h1 className="mt-1 text-2xl font-black tracking-tight text-white">
                                {show(data.nama_lengkap)}
                            </h1>

                            <div className="mt-2 flex flex-wrap gap-2">
                                <HeaderBadge>{show(posisi)}</HeaderBadge>
                                <HeaderBadge>{show(perusahaan)}</HeaderBadge>
                                <HeaderBadge>Token: {show(data.token)}</HeaderBadge>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        onClick={onBack}
                        className="w-fit rounded-2xl bg-white px-5 py-3 text-sm font-black text-slate-900 shadow-sm transition hover:bg-slate-100"
                    >
                        ← Kembali
                    </button>
                </div>
            </div>
        </div>
    );
}

function HeaderBadge({ children }) {
    if (!children || children === "-") return null;

    return (
        <span className="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-black text-white ring-1 ring-white/15">
            {children}
        </span>
    );
}

function SummaryCards({ items = [] }) {
    const visibleItems = items.filter((item) => show(item.value) !== "-");

    if (visibleItems.length === 0) return null;

    return (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {visibleItems.map((item) => (
                <div
                    key={item.label}
                    className="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p className="text-xs font-black uppercase tracking-wide text-slate-400">
                        {item.label}
                    </p>
                    <p className="mt-2 break-words text-sm font-black leading-6 text-slate-900">
                        {show(item.value)}
                    </p>
                </div>
            ))}
        </div>
    );
}

function InfoGrid({ children }) {
    const filteredChildren = React.Children.toArray(children).filter(Boolean);

    if (filteredChildren.length === 0) {
        return (
            <p className="rounded-2xl bg-white p-4 text-sm font-bold text-slate-500">
                Data belum tersedia.
            </p>
        );
    }

    return (
        <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            {filteredChildren}
        </div>
    );
}

function InfoItem({ label, value, wide = false }) {
    const display = show(value);

    if (display === "-") {
        return null;
    }

    return (
        <div
            className={`rounded-2xl border border-slate-100 bg-white p-4 shadow-sm ${
                wide ? "md:col-span-2 xl:col-span-3" : ""
            }`}
        >
            <p className="text-[11px] font-black uppercase tracking-wide text-slate-400">
                {label}
            </p>

            <p className="mt-1.5 whitespace-pre-line break-words text-sm font-bold leading-6 text-slate-800">
                {display}
            </p>
        </div>
    );
}

function DataTableBlock({ data = [], columns = [], emptyText }) {
    const visibleRows = data.filter(Boolean);

    if (visibleRows.length === 0) {
        return (
            <p className="rounded-2xl bg-white p-4 text-sm font-bold text-slate-500">
                {emptyText || "Data belum tersedia."}
            </p>
        );
    }

    return (
        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-slate-200">
                    <thead className="bg-slate-50">
                        <tr>
                            <th className="w-14 px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-400">
                                No
                            </th>

                            {columns.map(([label]) => (
                                <th
                                    key={label}
                                    className="px-4 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-400"
                                >
                                    {label}
                                </th>
                            ))}
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-100 bg-white">
                        {visibleRows.map((item, index) => (
                            <tr key={item?.id || index} className="hover:bg-slate-50">
                                <td className="px-4 py-3 text-sm font-black text-slate-500">
                                    {index + 1}
                                </td>

                                {columns.map(([label, accessor]) => {
                                    const value =
                                        typeof accessor === "function"
                                            ? accessor(item)
                                            : item?.[accessor];

                                    return (
                                        <td
                                            key={label}
                                            className="max-w-xs px-4 py-3 align-top text-sm font-bold leading-6 text-slate-700"
                                        >
                                            {show(value)}
                                        </td>
                                    );
                                })}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}


function normalizeDokumenInterview(data) {
    if (!data) return {};

    const source =
        data.dokumen_interview ||
        data.dokumenInterview ||
        data.latest_dokumen_interview ||
        data.latestDokumenInterview ||
        data.jadwal_interview ||
        data.jadwalInterview ||
        {};

    const fileCv = normalizeFileUrl(
        source.file_cv ||
            source.fileCv ||
            source.file_cv_url ||
            source.fileCvUrl ||
            data.file_cv_interview ||
            data.fileCvInterview ||
            data.file_cv ||
            data.fileCv ||
            null
    );

    const fileFoto = normalizeFileUrl(
        source.file_foto ||
            source.fileFoto ||
            source.file_foto_url ||
            source.fileFotoUrl ||
            data.file_foto_interview ||
            data.fileFotoInterview ||
            data.file_foto ||
            data.fileFoto ||
            null
    );

    return {
        ...source,
        file_cv: fileCv,
        fileCv,
        file_cv_url: fileCv,
        fileCvUrl: fileCv,
        file_foto: fileFoto,
        fileFoto,
        file_foto_url: fileFoto,
        fileFotoUrl: fileFoto,
        ada_dokumen: Boolean(fileCv || fileFoto),
        adaDokumen: Boolean(fileCv || fileFoto),
    };
}

function normalizeFileUrl(value) {
    if (!value) return null;

    const text = String(value).trim();

    if (!text) return null;

    if (text.startsWith("http://") || text.startsWith("https://") || text.startsWith("/storage/")) {
        return text;
    }

    if (text.startsWith("storage/")) {
        return `/${text}`;
    }

    return `/storage/${text.replace(/^\/+/, "")}`;
}

function isImageUrl(value) {
    if (!value) return false;

    return /\.(jpg|jpeg|png|webp|gif|bmp|svg)(\?.*)?$/i.test(String(value));
}

function getRelation(data, keys = []) {
    if (!data) return null;

    for (const key of keys) {
        if (data[key] !== undefined && data[key] !== null) {
            return data[key];
        }
    }

    return null;
}

function normalizeObject(value) {
    if (Array.isArray(value)) {
        return value[0] || {};
    }

    if (value && typeof value === "object") {
        return value;
    }

    return {};
}

function normalizeArray(value) {
    if (!value) return [];

    if (Array.isArray(value)) {
        return value;
    }

    if (typeof value === "object") {
        return [value];
    }

    return [];
}

function normalizeArrayFromJson(value) {
    if (!value) return [];

    if (Array.isArray(value)) {
        return value.filter(Boolean);
    }

    if (typeof value === "object") {
        return [value];
    }

    if (typeof value === "string") {
        try {
            const parsed = JSON.parse(value);

            if (Array.isArray(parsed)) {
                return parsed.filter(Boolean);
            }

            if (parsed && typeof parsed === "object") {
                return [parsed];
            }

            return [];
        } catch {
            return [];
        }
    }

    return [];
}

function show(value) {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    if (typeof value === "boolean") {
        return value ? "Ya" : "Tidak";
    }

    if (typeof value === "object") {
        return formatObjectSimple(value);
    }

    return String(value);
}

function formatObjectSimple(value) {
    if (!value || typeof value !== "object") {
        return "-";
    }

    if (Array.isArray(value)) {
        return value
            .map((item) => formatObjectSimple(item))
            .filter((item) => item && item !== "-")
            .join("\n");
    }

    return Object.entries(value)
        .filter(([, itemValue]) => {
            return (
                itemValue !== null &&
                itemValue !== undefined &&
                itemValue !== ""
            );
        })
        .map(([key, itemValue]) => `${formatLabel(key)}: ${itemValue}`)
        .join("\n");
}

function formatLabel(key) {
    return String(key)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function withSuffix(value, suffix) {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    return `${value} ${suffix}`;
}

function formatDate(value) {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
}

function formatDateTime(value) {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function formatMoney(value) {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    const number = Number(value);

    if (Number.isNaN(number)) {
        return value;
    }

    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(number);
}
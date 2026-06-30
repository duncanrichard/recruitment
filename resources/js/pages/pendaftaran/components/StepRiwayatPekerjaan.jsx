import React from "react";

const EMPTY_JOB = {
    id: "",
    nama_perusahaan: "",
    posisi_pekerjaan_terakhir: "",
    posisi_pekerjaan: "",
    periode_kerja_awal: "",
    periode_kerja_akhir: "",
    gaji_terakhir: "",
    bidang_pekerjaan: "",
    lokasi_perusahaan: "",
    deskripsi_pekerjaan: "",
    alasan_berhenti: "",
    keahlian: "",
    referensi_kerja: "",
    nama_refrensi: "",
    telp_refrensi: "",
    refrensi_rekan_kerja: "",
    nama_refrensi_rekan: "",
    telp_refrensi_rekan: "",
    refrensi_kerabat: "",
    nama_refrensi_kerabat: "",
    telp_refrensi_kerabat: "",
};

export default function StepRiwayatPekerjaan({
    form = {},
    handleChange,
    errors = {},
}) {
    const maxGajiTerakhir = 999999999999999999;

    const statusPekerjaan = String(form.status_pekerjaan || "").trim();
    const isBelumBekerja = statusPekerjaan.toLowerCase() === "belum bekerja";
    const isWorkFieldsDisabled = isBelumBekerja;

    const pekerjaanList = React.useMemo(() => {
        if (Array.isArray(form.riwayat_pekerjaan) && form.riwayat_pekerjaan.length > 0) {
            return form.riwayat_pekerjaan.map((item) => ({
                ...EMPTY_JOB,
                ...item,
            }));
        }

        const hasLegacyValue = [
            "nama_perusahaan",
            "posisi_pekerjaan_terakhir",
            "posisi_pekerjaan",
            "periode_kerja_awal",
            "periode_kerja_akhir",
            "gaji_terakhir",
            "bidang_pekerjaan",
            "lokasi_perusahaan",
            "deskripsi_pekerjaan",
            "alasan_berhenti",
            "keahlian",
            "referensi_kerja",
            "nama_refrensi",
            "telp_refrensi",
            "refrensi_rekan_kerja",
            "nama_refrensi_rekan",
            "telp_refrensi_rekan",
            "refrensi_kerabat",
            "nama_refrensi_kerabat",
            "telp_refrensi_kerabat",
        ].some((key) => form[key] !== undefined && form[key] !== null && form[key] !== "");

        if (hasLegacyValue) {
            return [
                {
                    ...EMPTY_JOB,
                    id: form.riwayat_pekerjaan_id || form.pekerjaan_id || "",
                    nama_perusahaan: form.nama_perusahaan || "",
                    posisi_pekerjaan_terakhir:
                        form.posisi_pekerjaan_terakhir || form.posisi_pekerjaan || "",
                    posisi_pekerjaan:
                        form.posisi_pekerjaan || form.posisi_pekerjaan_terakhir || "",
                    periode_kerja_awal: normalizeDateValue(form.periode_kerja_awal),
                    periode_kerja_akhir: normalizeDateValue(form.periode_kerja_akhir),
                    gaji_terakhir: form.gaji_terakhir || "",
                    bidang_pekerjaan: form.bidang_pekerjaan || "",
                    lokasi_perusahaan: form.lokasi_perusahaan || "",
                    deskripsi_pekerjaan: form.deskripsi_pekerjaan || "",
                    alasan_berhenti: form.alasan_berhenti || "",
                    keahlian: form.keahlian || "",
                    referensi_kerja: form.referensi_kerja || form.refrensi_kerja || "",
                    nama_refrensi: form.nama_refrensi || "",
                    telp_refrensi: form.telp_refrensi || "",
                    refrensi_rekan_kerja: form.refrensi_rekan_kerja || "",
                    nama_refrensi_rekan: form.nama_refrensi_rekan || "",
                    telp_refrensi_rekan: form.telp_refrensi_rekan || "",
                    refrensi_kerabat: form.refrensi_kerabat || "",
                    nama_refrensi_kerabat: form.nama_refrensi_kerabat || "",
                    telp_refrensi_kerabat: form.telp_refrensi_kerabat || "",
                },
            ];
        }

        return [{ ...EMPTY_JOB }];
    }, [form]);

    const setPekerjaanList = React.useCallback(
        (rows) => {
            const nextRows = rows.map((item) => ({
                ...EMPTY_JOB,
                ...item,
            }));

            /*
             * Penting:
             * Kirim hanya 1 perubahan state untuk field riwayat_pekerjaan.
             * Jangan sync field legacy seperti nama_perusahaan, posisi_pekerjaan,
             * dll di sini, karena beberapa parent handleChange memakai setState
             * non-functional sehingga update state bisa tertimpa dan yang terkirim
             * ke controller hanya data pertama.
             */
            handleChange({
                target: {
                    name: "riwayat_pekerjaan",
                    value: nextRows,
                    type: "array",
                    checked: false,
                },
            });
        },
        [handleChange]
    );

    const updatePekerjaan = (index, field, value) => {
        const nextRows = pekerjaanList.map((item, itemIndex) => {
            if (itemIndex !== index) {
                return item;
            }

            const nextItem = {
                ...item,
                [field]: value,
            };

            if (field === "posisi_pekerjaan_terakhir") {
                nextItem.posisi_pekerjaan = value;
            }

            if (field === "posisi_pekerjaan") {
                nextItem.posisi_pekerjaan_terakhir = value;
            }

            if (field === "referensi_kerja" && String(value).toLowerCase() !== "ya") {
                nextItem.nama_refrensi = "";
                nextItem.telp_refrensi = "";
            }

            if (field === "refrensi_rekan_kerja" && String(value).toLowerCase() !== "ya") {
                nextItem.nama_refrensi_rekan = "";
                nextItem.telp_refrensi_rekan = "";
            }

            if (field === "refrensi_kerabat" && String(value).toLowerCase() !== "ya") {
                nextItem.nama_refrensi_kerabat = "";
                nextItem.telp_refrensi_kerabat = "";
            }

            return nextItem;
        });

        setPekerjaanList(nextRows);
    };

    const updateGaji = (index, value) => {
        const numericOnly = String(value || "").replace(/[^\d]/g, "");

        if (numericOnly === "") {
            updatePekerjaan(index, "gaji_terakhir", "");
            return;
        }

        const numericValue = Number(numericOnly);
        const safeValue = Math.min(numericValue, maxGajiTerakhir);

        updatePekerjaan(index, "gaji_terakhir", String(safeValue));
    };

    const addPekerjaan = () => {
        setPekerjaanList([...pekerjaanList, { ...EMPTY_JOB }]);
    };

    const removePekerjaan = (index) => {
        if (pekerjaanList.length <= 1) {
            setPekerjaanList([{ ...EMPTY_JOB }]);
            return;
        }

        setPekerjaanList(pekerjaanList.filter((_, itemIndex) => itemIndex !== index));
    };

    const handleStatusPekerjaanChange = (e) => {
        const value = e.target.value;

        handleChange(e);

        if (String(value).trim().toLowerCase() === "belum bekerja") {
            handleChange({
                target: {
                    name: "riwayat_pekerjaan",
                    value: [],
                    type: "array",
                    checked: false,
                },
            });
        }
    };

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                <h3 className="text-base font-bold text-orange-800">
                    Riwayat Pekerjaan
                </h3>

                <p className="mt-1 text-sm text-orange-600">
                    Lengkapi status pekerjaan, pengalaman kerja, deskripsi pekerjaan,
                    dan data referensi kerja. Riwayat pekerjaan dapat ditambahkan lebih dari satu.
                </p>
            </div>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Status Pekerjaan
                </h4>

                <Select
                    label="Status Pekerjaan"
                    name="status_pekerjaan"
                    value={form.status_pekerjaan}
                    onChange={handleStatusPekerjaanChange}
                    error={errors.status_pekerjaan}
                    options={[
                        "Belum Bekerja",
                        "Sedang Bekerja",
                        "Pernah Bekerja",
                        "Freelance",
                        "Wiraswasta",
                    ]}
                    required
                />

                {isBelumBekerja && (
                    <div className="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-600">
                        Status pekerjaan dipilih <span className="font-black">Belum Bekerja</span>,
                        sehingga form pengalaman kerja dan referensi kerja tidak perlu diisi.
                    </div>
                )}
            </section>

            <input
                type="hidden"
                name="riwayat_pekerjaan"
                value={JSON.stringify(pekerjaanList)}
                readOnly
            />

            {pekerjaanList.map((item, index) => (
                <React.Fragment key={`riwayat-pekerjaan-hidden-${index}`}>
                    <input
                        type="hidden"
                        name={`riwayat_pekerjaan[${index}][id]`}
                        value={item.id || ""}
                        readOnly
                    />

                    {Object.keys(EMPTY_JOB).map((field) => (
                        field === "id" ? null : (
                            <input
                                key={field}
                                type="hidden"
                                name={`riwayat_pekerjaan[${index}][${field}]`}
                                value={item[field] ?? ""}
                                readOnly
                            />
                        )
                    ))}
                </React.Fragment>
            ))}

            {!isBelumBekerja && (
                <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h4 className="text-lg font-bold text-slate-800">
                                Riwayat / Pengalaman Kerja
                            </h4>
                            <p className="mt-1 text-sm text-slate-500">
                                Tambahkan satu atau lebih pengalaman kerja yang pernah dimiliki.
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={addPekerjaan}
                            className="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-blue-100 transition hover:bg-blue-700"
                        >
                            + Tambah Pengalaman
                        </button>
                    </div>

                    <div className="space-y-5">
                        {pekerjaanList.map((item, index) => {
                            const referensiAtasanTidak = String(item.referensi_kerja || "")
                                .trim()
                                .toLowerCase() === "tidak";
                            const referensiRekanTidak = String(item.refrensi_rekan_kerja || "")
                                .trim()
                                .toLowerCase() === "tidak";
                            const referensiKerabatTidak = String(item.refrensi_kerabat || "")
                                .trim()
                                .toLowerCase() === "tidak";

                            const isReferensiAtasanDisabled =
                                String(item.referensi_kerja || "").toLowerCase() !== "ya";
                            const isReferensiRekanDisabled =
                                String(item.refrensi_rekan_kerja || "").toLowerCase() !== "ya";
                            const isReferensiKerabatDisabled =
                                String(item.refrensi_kerabat || "").toLowerCase() !== "ya";

                            return (
                                <div
                                    key={item.id || index}
                                    className="rounded-3xl border border-slate-200 bg-slate-50 p-5"
                                >
                                    <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <h5 className="text-base font-black text-slate-800">
                                                Pengalaman Kerja {index + 1}
                                            </h5>
                                            <p className="mt-1 text-xs font-medium text-slate-500">
                                                Isi detail perusahaan, posisi, periode, dan deskripsi pekerjaan.
                                            </p>
                                        </div>

                                        {pekerjaanList.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() => removePekerjaan(index)}
                                                className="rounded-xl bg-red-100 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-200"
                                            >
                                                Hapus
                                            </button>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                        <Input
                                            label="Bidang Pekerjaan"
                                            name={`riwayat_pekerjaan_${index}_bidang_pekerjaan`}
                                            value={item.bidang_pekerjaan}
                                            onChange={(e) => updatePekerjaan(index, "bidang_pekerjaan", e.target.value)}
                                            placeholder="Contoh: Administrasi, IT, Marketing"
                                            error={getPekerjaanError(errors, index, "bidang_pekerjaan")}
                                        />

                                        <Input
                                            label="Lokasi Perusahaan"
                                            name={`riwayat_pekerjaan_${index}_lokasi_perusahaan`}
                                            value={item.lokasi_perusahaan}
                                            onChange={(e) => updatePekerjaan(index, "lokasi_perusahaan", e.target.value)}
                                            placeholder="Contoh: Jakarta"
                                            error={getPekerjaanError(errors, index, "lokasi_perusahaan")}
                                        />
                                    </div>

                                    <div className="mt-6 rounded-2xl border border-blue-100 bg-white p-4">
                                        <h6 className="mb-4 text-sm font-black uppercase tracking-wide text-slate-700">
                                            Pengalaman Kerja Terakhir
                                        </h6>

                                        <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                            <Input
                                                label="Nama Perusahaan"
                                                name={`riwayat_pekerjaan_${index}_nama_perusahaan`}
                                                value={item.nama_perusahaan}
                                                onChange={(e) => updatePekerjaan(index, "nama_perusahaan", e.target.value)}
                                                placeholder="Contoh: PT Maju Bersama"
                                                error={getPekerjaanError(errors, index, "nama_perusahaan")}
                                            />

                                            <Input
                                                label="Posisi Pekerjaan Terakhir"
                                                name={`riwayat_pekerjaan_${index}_posisi_pekerjaan_terakhir`}
                                                value={item.posisi_pekerjaan_terakhir || item.posisi_pekerjaan || ""}
                                                onChange={(e) => updatePekerjaan(index, "posisi_pekerjaan_terakhir", e.target.value)}
                                                placeholder="Contoh: Staff Administrasi"
                                                error={
                                                    getPekerjaanError(errors, index, "posisi_pekerjaan_terakhir") ||
                                                    getPekerjaanError(errors, index, "posisi_pekerjaan")
                                                }
                                            />
                                        </div>

                                        <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                                            <Input
                                                label="Periode Kerja Awal"
                                                type="date"
                                                name={`riwayat_pekerjaan_${index}_periode_kerja_awal`}
                                                value={normalizeDateValue(item.periode_kerja_awal)}
                                                onChange={(e) => updatePekerjaan(index, "periode_kerja_awal", e.target.value)}
                                                error={getPekerjaanError(errors, index, "periode_kerja_awal")}
                                            />

                                            <Input
                                                label="Periode Kerja Akhir"
                                                type="date"
                                                name={`riwayat_pekerjaan_${index}_periode_kerja_akhir`}
                                                value={normalizeDateValue(item.periode_kerja_akhir)}
                                                onChange={(e) => updatePekerjaan(index, "periode_kerja_akhir", e.target.value)}
                                                error={getPekerjaanError(errors, index, "periode_kerja_akhir")}
                                            />
                                        </div>

                                        <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                                            <Input
                                                label="Gaji Terakhir"
                                                type="number"
                                                name={`riwayat_pekerjaan_${index}_gaji_terakhir`}
                                                value={item.gaji_terakhir}
                                                onChange={(e) => updateGaji(index, e.target.value)}
                                                placeholder="Contoh: 4000000"
                                                error={getPekerjaanError(errors, index, "gaji_terakhir")}
                                                min="0"
                                                max={String(maxGajiTerakhir)}
                                                step="1000"
                                            />

                                            <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-700">
                                                Maksimal gaji yang dapat diinput adalah{" "}
                                                <span className="font-black">
                                                    Rp {formatRupiah(maxGajiTerakhir)}
                                                </span>.
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-6 grid grid-cols-1 gap-5">
                                        <Textarea
                                            label="Deskripsi Pekerjaan"
                                            name={`riwayat_pekerjaan_${index}_deskripsi_pekerjaan`}
                                            value={item.deskripsi_pekerjaan}
                                            onChange={(e) => updatePekerjaan(index, "deskripsi_pekerjaan", e.target.value)}
                                            placeholder="Jelaskan tugas dan tanggung jawab pekerjaan"
                                            error={getPekerjaanError(errors, index, "deskripsi_pekerjaan")}
                                        />

                                        <Textarea
                                            label="Alasan Berhenti"
                                            name={`riwayat_pekerjaan_${index}_alasan_berhenti`}
                                            value={item.alasan_berhenti}
                                            onChange={(e) => updatePekerjaan(index, "alasan_berhenti", e.target.value)}
                                            placeholder="Contoh: Kontrak selesai, ingin mencari pengalaman baru"
                                            error={getPekerjaanError(errors, index, "alasan_berhenti")}
                                        />

                                        <Textarea
                                            label="Keahlian"
                                            name={`riwayat_pekerjaan_${index}_keahlian`}
                                            value={item.keahlian}
                                            onChange={(e) => updatePekerjaan(index, "keahlian", e.target.value)}
                                            placeholder="Contoh: Microsoft Office, komunikasi, administrasi"
                                            error={getPekerjaanError(errors, index, "keahlian")}
                                        />
                                    </div>

                                    <div className="mt-6 rounded-2xl border border-slate-200 bg-white p-4">
                                        <h6 className="mb-4 text-sm font-black uppercase tracking-wide text-slate-700">
                                            Referensi Kerja
                                        </h6>

                                        <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                                            <SelectYaTidak
                                                label="Ada Referensi Atasan?"
                                                name={`riwayat_pekerjaan_${index}_referensi_kerja`}
                                                value={item.referensi_kerja}
                                                onChange={(e) => updatePekerjaan(index, "referensi_kerja", e.target.value)}
                                                error={getPekerjaanError(errors, index, "referensi_kerja")}
                                            />

                                            <Input
                                                label="Nama Referensi Atasan"
                                                name={`riwayat_pekerjaan_${index}_nama_refrensi`}
                                                value={referensiAtasanTidak ? "" : item.nama_refrensi}
                                                onChange={(e) => updatePekerjaan(index, "nama_refrensi", e.target.value)}
                                                placeholder="Nama atasan"
                                                error={getPekerjaanError(errors, index, "nama_refrensi")}
                                                disabled={isReferensiAtasanDisabled}
                                            />

                                            <Input
                                                label="Telepon Referensi Atasan"
                                                name={`riwayat_pekerjaan_${index}_telp_refrensi`}
                                                value={referensiAtasanTidak ? "" : item.telp_refrensi}
                                                onChange={(e) => updatePekerjaan(index, "telp_refrensi", e.target.value)}
                                                placeholder="Nomor telepon"
                                                error={getPekerjaanError(errors, index, "telp_refrensi")}
                                                disabled={isReferensiAtasanDisabled}
                                            />
                                        </div>

                                        <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                                            <SelectYaTidak
                                                label="Ada Referensi Rekan Kerja?"
                                                name={`riwayat_pekerjaan_${index}_refrensi_rekan_kerja`}
                                                value={item.refrensi_rekan_kerja}
                                                onChange={(e) => updatePekerjaan(index, "refrensi_rekan_kerja", e.target.value)}
                                                error={getPekerjaanError(errors, index, "refrensi_rekan_kerja")}
                                            />

                                            <Input
                                                label="Nama Referensi Rekan"
                                                name={`riwayat_pekerjaan_${index}_nama_refrensi_rekan`}
                                                value={referensiRekanTidak ? "" : item.nama_refrensi_rekan}
                                                onChange={(e) => updatePekerjaan(index, "nama_refrensi_rekan", e.target.value)}
                                                placeholder="Nama rekan kerja"
                                                error={getPekerjaanError(errors, index, "nama_refrensi_rekan")}
                                                disabled={isReferensiRekanDisabled}
                                            />

                                            <Input
                                                label="Telepon Referensi Rekan"
                                                name={`riwayat_pekerjaan_${index}_telp_refrensi_rekan`}
                                                value={referensiRekanTidak ? "" : item.telp_refrensi_rekan}
                                                onChange={(e) => updatePekerjaan(index, "telp_refrensi_rekan", e.target.value)}
                                                placeholder="Nomor telepon"
                                                error={getPekerjaanError(errors, index, "telp_refrensi_rekan")}
                                                disabled={isReferensiRekanDisabled}
                                            />
                                        </div>

                                        <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                                            <SelectYaTidak
                                                label="Ada Referensi Kerabat?"
                                                name={`riwayat_pekerjaan_${index}_refrensi_kerabat`}
                                                value={item.refrensi_kerabat}
                                                onChange={(e) => updatePekerjaan(index, "refrensi_kerabat", e.target.value)}
                                                error={getPekerjaanError(errors, index, "refrensi_kerabat")}
                                            />

                                            <Input
                                                label="Nama Referensi Kerabat"
                                                name={`riwayat_pekerjaan_${index}_nama_refrensi_kerabat`}
                                                value={referensiKerabatTidak ? "" : item.nama_refrensi_kerabat}
                                                onChange={(e) => updatePekerjaan(index, "nama_refrensi_kerabat", e.target.value)}
                                                placeholder="Nama kerabat"
                                                error={getPekerjaanError(errors, index, "nama_refrensi_kerabat")}
                                                disabled={isReferensiKerabatDisabled}
                                            />

                                            <Input
                                                label="Telepon Referensi Kerabat"
                                                name={`riwayat_pekerjaan_${index}_telp_refrensi_kerabat`}
                                                value={referensiKerabatTidak ? "" : item.telp_refrensi_kerabat}
                                                onChange={(e) => updatePekerjaan(index, "telp_refrensi_kerabat", e.target.value)}
                                                placeholder="Nomor telepon"
                                                error={getPekerjaanError(errors, index, "telp_refrensi_kerabat")}
                                                disabled={isReferensiKerabatDisabled}
                                            />
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </section>
            )}
        </div>
    );
}

function getPekerjaanError(errors, index, field) {
    if (!errors) return null;

    const keys = [
        `riwayat_pekerjaan.${index}.${field}`,
        `riwayat_pekerjaan_${index}_${field}`,
        index === 0 ? field : null,
    ].filter(Boolean);

    for (const key of keys) {
        const value = errors[key];
        if (Array.isArray(value)) return value[0];
        if (value) return value;
    }

    return null;
}

function normalizeDateValue(value) {
    if (!value) {
        return "";
    }

    const stringValue = String(value);

    if (/^\d{4}$/.test(stringValue)) {
        return `${stringValue}-01-01`;
    }

    if (/^\d{4}-\d{2}-\d{2}/.test(stringValue)) {
        return stringValue.slice(0, 10);
    }

    return "";
}

function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID").format(value);
}

function FieldLabel({ label, required = false }) {
    return (
        <label className="mb-2 block text-sm font-semibold text-slate-700">
            {label}
            {required && <span className="ml-1 text-red-500">*</span>}
        </label>
    );
}

function ErrorMessage({ message }) {
    if (!message) {
        return null;
    }

    return (
        <p className="mt-2 text-xs font-semibold text-red-500">
            {message}
        </p>
    );
}

function fieldClass(error, disabled = false) {
    return `w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 ${
        disabled
            ? "cursor-not-allowed border-slate-200 bg-slate-100 text-slate-500 placeholder:text-slate-400"
            : error
            ? "border-red-300 bg-red-50 focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-100"
            : "border-slate-200 bg-slate-50 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
    }`;
}

function Input({
    label,
    type = "text",
    name,
    value,
    onChange,
    placeholder,
    error,
    disabled = false,
    min,
    max,
    step,
    inputMode,
    maxLength,
    required = false,
}) {
    return (
        <div>
            <FieldLabel label={label} required={required} />

            <input
                type={type}
                name={name}
                value={value ?? ""}
                onChange={onChange}
                placeholder={type === "date" ? undefined : placeholder || `Masukkan ${label.toLowerCase()}`}
                className={fieldClass(error, disabled)}
                disabled={disabled}
                min={min}
                max={max}
                step={step}
                inputMode={inputMode}
                maxLength={maxLength}
                required={required}
                autoComplete="off"
            />

            <ErrorMessage message={error} />
        </div>
    );
}

function Textarea({
    label,
    name,
    value,
    onChange,
    placeholder,
    error,
    disabled = false,
    rows = 4,
    required = false,
}) {
    return (
        <div>
            <FieldLabel label={label} required={required} />

            <textarea
                name={name}
                value={value ?? ""}
                onChange={onChange}
                placeholder={placeholder || `Masukkan ${label.toLowerCase()}`}
                className={`${fieldClass(error, disabled)} resize-none`}
                disabled={disabled}
                rows={rows}
                required={required}
                autoComplete="off"
            />

            <ErrorMessage message={error} />
        </div>
    );
}

function Select({
    label,
    name,
    value,
    onChange,
    error,
    options = [],
    disabled = false,
    required = false,
}) {
    return (
        <div>
            <FieldLabel label={label} required={required} />

            <select
                name={name}
                value={value ?? ""}
                onChange={onChange}
                className={fieldClass(error, disabled)}
                disabled={disabled}
                required={required}
            >
                <option value="">Pilih {label}</option>

                {options.map((option) => (
                    <option key={option} value={option}>
                        {option}
                    </option>
                ))}
            </select>

            <ErrorMessage message={error} />
        </div>
    );
}

function SelectYaTidak({
    label,
    name,
    value,
    onChange,
    error,
    disabled = false,
}) {
    return (
        <div>
            <FieldLabel label={label} />

            <select
                name={name}
                value={value ?? ""}
                onChange={onChange}
                className={fieldClass(error, disabled)}
                disabled={disabled}
            >
                <option value="">Pilih</option>
                <option value="Ya">Ya</option>
                <option value="Tidak">Tidak</option>
            </select>

            <ErrorMessage message={error} />
        </div>
    );
}

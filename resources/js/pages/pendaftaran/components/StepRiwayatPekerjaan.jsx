import React from "react";

export default function StepRiwayatPekerjaan({
    form = {},
    handleChange,
    errors = {},
}) {
    const maxGajiTerakhir = 999999999999999999;

    const statusPekerjaan = String(form.status_pekerjaan || "").trim();
    const isBelumBekerja =
        statusPekerjaan.toLowerCase() === "belum bekerja";

    const isWorkFieldsDisabled = isBelumBekerja;

    const handlePosisiChange = (e) => {
        const value = e.target.value;

        handleChange({
            target: {
                name: "posisi_pekerjaan_terakhir",
                value,
                type: "text",
                checked: false,
            },
        });

        handleChange({
            target: {
                name: "posisi_pekerjaan",
                value,
                type: "text",
                checked: false,
            },
        });
    };

    const handleGajiChange = (e) => {
        const rawValue = e.target.value;
        const numericOnly = rawValue.replace(/[^\d]/g, "");

        if (numericOnly === "") {
            handleChange({
                target: {
                    name: "gaji_terakhir",
                    value: "",
                    type: "text",
                    checked: false,
                },
            });
            return;
        }

        const numericValue = Number(numericOnly);
        const safeValue = Math.min(numericValue, maxGajiTerakhir);

        handleChange({
            target: {
                name: "gaji_terakhir",
                value: String(safeValue),
                type: "text",
                checked: false,
            },
        });
    };

    const handleTahunChange = (e) => {
        const { name, value } = e.target;
        const numericOnly = String(value || "")
            .replace(/[^\d]/g, "")
            .slice(0, 4);

        handleChange({
            target: {
                name,
                value: numericOnly,
                type: "text",
                checked: false,
            },
        });
    };

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                <h3 className="text-base font-bold text-orange-800">
                    Riwayat Pekerjaan
                </h3>

                <p className="mt-1 text-sm text-orange-600">
                    Lengkapi status pekerjaan, detail pekerjaan, pengalaman kerja terakhir,
                    dan data referensi kerja.
                </p>
            </div>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Tambahan Detail Pekerjaan
                </h4>

                {isBelumBekerja && (
                    <div className="mb-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-600">
                        Status pekerjaan dipilih{" "}
                        <span className="font-black">Belum Bekerja</span>,
                        sehingga semua form pekerjaan dan referensi kerja tidak perlu diisi.
                    </div>
                )}

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Select
                        label="Status Pekerjaan"
                        name="status_pekerjaan"
                        value={form.status_pekerjaan}
                        onChange={handleChange}
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

                    <Input
                        label="Bidang Pekerjaan"
                        name="bidang_pekerjaan"
                        value={form.bidang_pekerjaan}
                        onChange={handleChange}
                        placeholder="Contoh: Administrasi, IT, Marketing"
                        error={errors.bidang_pekerjaan}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Lokasi Perusahaan"
                        name="lokasi_perusahaan"
                        value={form.lokasi_perusahaan}
                        onChange={handleChange}
                        placeholder="Contoh: Jakarta"
                        error={errors.lokasi_perusahaan}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Tahun Mulai Bekerja"
                        name="tahun_mulai_bekerja"
                        value={form.tahun_mulai_bekerja}
                        onChange={handleTahunChange}
                        placeholder="Contoh: 2020"
                        error={errors.tahun_mulai_bekerja}
                        inputMode="numeric"
                        maxLength={4}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Tahun Selesai Bekerja"
                        name="tahun_selesai_bekerja"
                        value={form.tahun_selesai_bekerja}
                        onChange={handleTahunChange}
                        placeholder="Contoh: 2024"
                        error={errors.tahun_selesai_bekerja}
                        inputMode="numeric"
                        maxLength={4}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Lama Bekerja"
                        name="lama_bekerja"
                        value={
                            form.lama_bekerja
                                ? `${String(form.lama_bekerja).replace(/ tahun$/i, "")} tahun`
                                : ""
                        }
                        onChange={handleChange}
                        placeholder="Otomatis dari tahun mulai dan selesai"
                        error={errors.lama_bekerja}
                        disabled
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5">
                    <Textarea
                        label="Deskripsi Pekerjaan"
                        name="deskripsi_pekerjaan"
                        value={form.deskripsi_pekerjaan}
                        onChange={handleChange}
                        placeholder="Jelaskan tugas dan tanggung jawab pekerjaan"
                        error={errors.deskripsi_pekerjaan}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Textarea
                        label="Alasan Berhenti"
                        name="alasan_berhenti"
                        value={form.alasan_berhenti}
                        onChange={handleChange}
                        placeholder="Contoh: Kontrak selesai, ingin mencari pengalaman baru"
                        error={errors.alasan_berhenti}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Textarea
                        label="Keahlian"
                        name="keahlian"
                        value={form.keahlian}
                        onChange={handleChange}
                        placeholder="Contoh: Microsoft Office, komunikasi, administrasi"
                        error={errors.keahlian}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Textarea
                        label="Catatan Pekerjaan"
                        name="catatan_pekerjaan"
                        value={form.catatan_pekerjaan}
                        onChange={handleChange}
                        placeholder="Catatan tambahan terkait pekerjaan"
                        error={errors.catatan_pekerjaan}
                        disabled={isWorkFieldsDisabled}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Pengalaman Kerja Terakhir
                </h4>

                {isBelumBekerja && (
                    <div className="mb-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-600">
                        Pengalaman kerja terakhir otomatis dinonaktifkan karena status pekerjaan
                        adalah <span className="font-black">Belum Bekerja</span>.
                    </div>
                )}

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Perusahaan"
                        name="nama_perusahaan"
                        value={form.nama_perusahaan}
                        onChange={handleChange}
                        placeholder="Contoh: PT Maju Bersama"
                        error={errors.nama_perusahaan}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Posisi Pekerjaan Terakhir"
                        name="posisi_pekerjaan_terakhir"
                        value={
                            form.posisi_pekerjaan_terakhir ||
                            form.posisi_pekerjaan ||
                            ""
                        }
                        onChange={handlePosisiChange}
                        placeholder="Contoh: Staff Administrasi"
                        error={
                            errors.posisi_pekerjaan_terakhir ||
                            errors.posisi_pekerjaan
                        }
                        disabled={isWorkFieldsDisabled}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Periode Kerja Awal"
                        type="date"
                        name="periode_kerja_awal"
                        value={normalizeDateValue(form.periode_kerja_awal)}
                        onChange={handleChange}
                        error={errors.periode_kerja_awal}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Periode Kerja Akhir"
                        type="date"
                        name="periode_kerja_akhir"
                        value={normalizeDateValue(form.periode_kerja_akhir)}
                        onChange={handleChange}
                        error={errors.periode_kerja_akhir}
                        disabled={isWorkFieldsDisabled}
                    />
                </div>

                <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Gaji Terakhir"
                        type="number"
                        name="gaji_terakhir"
                        value={form.gaji_terakhir}
                        onChange={handleGajiChange}
                        placeholder="Contoh: 4000000"
                        error={errors.gaji_terakhir}
                        min="0"
                        max={String(maxGajiTerakhir)}
                        step="1000"
                        disabled={isWorkFieldsDisabled}
                    />

                    <div
                        className={`rounded-2xl border p-4 text-sm font-semibold ${
                            isWorkFieldsDisabled
                                ? "border-slate-200 bg-slate-50 text-slate-500"
                                : "border-amber-200 bg-amber-50 text-amber-700"
                        }`}
                    >
                        Maksimal gaji yang dapat diinput adalah{" "}
                        <span className="font-black">
                            Rp {formatRupiah(maxGajiTerakhir)}
                        </span>
                        .
                    </div>
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Referensi Kerja
                </h4>

                {isBelumBekerja && (
                    <div className="mb-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-600">
                        Referensi kerja tidak perlu diisi karena status pekerjaan
                        adalah <span className="font-black">Belum Bekerja</span>.
                    </div>
                )}

                <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <SelectYaTidak
                        label="Ada Referensi Kerja?"
                        name="referensi_kerja"
                        value={form.referensi_kerja}
                        onChange={handleChange}
                        error={errors.referensi_kerja}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Nama Referensi"
                        name="nama_refrensi"
                        value={form.nama_refrensi}
                        onChange={handleChange}
                        placeholder="Nama referensi"
                        error={errors.nama_refrensi}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Telepon Referensi"
                        name="telp_refrensi"
                        value={form.telp_refrensi}
                        onChange={handleChange}
                        placeholder="Nomor telepon"
                        error={errors.telp_refrensi}
                        disabled={isWorkFieldsDisabled}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Referensi Rekan Kerja
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <SelectYaTidak
                        label="Ada Referensi Rekan Kerja?"
                        name="refrensi_rekan_kerja"
                        value={form.refrensi_rekan_kerja}
                        onChange={handleChange}
                        error={errors.refrensi_rekan_kerja}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Nama Referensi Rekan"
                        name="nama_refrensi_rekan"
                        value={form.nama_refrensi_rekan}
                        onChange={handleChange}
                        placeholder="Nama rekan kerja"
                        error={errors.nama_refrensi_rekan}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Telepon Referensi Rekan"
                        name="telp_refrensi_rekan"
                        value={form.telp_refrensi_rekan}
                        onChange={handleChange}
                        placeholder="Nomor telepon"
                        error={errors.telp_refrensi_rekan}
                        disabled={isWorkFieldsDisabled}
                    />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Referensi Kerabat
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <SelectYaTidak
                        label="Ada Referensi Kerabat?"
                        name="refrensi_kerabat"
                        value={form.refrensi_kerabat}
                        onChange={handleChange}
                        error={errors.refrensi_kerabat}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Nama Referensi Kerabat"
                        name="nama_refrensi_kerabat"
                        value={form.nama_refrensi_kerabat}
                        onChange={handleChange}
                        placeholder="Nama kerabat"
                        error={errors.nama_refrensi_kerabat}
                        disabled={isWorkFieldsDisabled}
                    />

                    <Input
                        label="Telepon Referensi Kerabat"
                        name="telp_refrensi_kerabat"
                        value={form.telp_refrensi_kerabat}
                        onChange={handleChange}
                        placeholder="Nomor telepon"
                        error={errors.telp_refrensi_kerabat}
                        disabled={isWorkFieldsDisabled}
                    />
                </div>
            </section>
        </div>
    );
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
}) {
    return (
        <div>
            <FieldLabel label={label} />

            <input
                type={type}
                name={name}
                value={value ?? ""}
                onChange={onChange}
                placeholder={placeholder || `Masukkan ${label.toLowerCase()}`}
                className={fieldClass(error, disabled)}
                disabled={disabled}
                min={min}
                max={max}
                step={step}
                inputMode={inputMode}
                maxLength={maxLength}
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
}) {
    return (
        <div>
            <FieldLabel label={label} />

            <textarea
                name={name}
                value={value ?? ""}
                onChange={onChange}
                placeholder={placeholder || `Masukkan ${label.toLowerCase()}`}
                className={`${fieldClass(error, disabled)} resize-none`}
                disabled={disabled}
                rows={rows}
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

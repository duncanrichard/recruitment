import React from "react";

export default function StepRiwayatPekerjaan({
    form,
    handleChange,
    errors = {},
}) {
    const maxGajiTerakhir = 999999999999999999;

    const handlePosisiChange = (e) => {
        const value = e.target.value;

        handleChange({
            target: {
                name: "posisi_pekerjaan_terakhir",
                value,
            },
        });

        handleChange({
            target: {
                name: "posisi_pekerjaan",
                value,
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
                    Lengkapi pengalaman kerja terakhir dan data referensi kerja.
                </p>
            </div>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h4 className="mb-5 text-lg font-bold text-slate-800">
                    Pengalaman Kerja Terakhir
                </h4>

                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <Input
                        label="Nama Perusahaan"
                        name="nama_perusahaan"
                        value={form.nama_perusahaan}
                        onChange={handleChange}
                        placeholder="Contoh: PT Maju Bersama"
                        error={errors.nama_perusahaan}
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
                    />

                    <Input
                        label="Periode Kerja Akhir"
                        type="date"
                        name="periode_kerja_akhir"
                        value={normalizeDateValue(form.periode_kerja_akhir)}
                        onChange={handleChange}
                        error={errors.periode_kerja_akhir}
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
                    />

                    <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-700">
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

                <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <SelectYaTidak
                        label="Ada Referensi Kerja?"
                        name="refrensi_kerja"
                        value={form.refrensi_kerja}
                        onChange={handleChange}
                        error={errors.refrensi_kerja}
                    />

                    <Input
                        label="Nama Referensi"
                        name="nama_refrensi"
                        value={form.nama_refrensi}
                        onChange={handleChange}
                        placeholder="Nama referensi"
                        error={errors.nama_refrensi}
                    />

                    <Input
                        label="Telepon Referensi"
                        name="telp_refrensi"
                        value={form.telp_refrensi}
                        onChange={handleChange}
                        placeholder="Nomor telepon"
                        error={errors.telp_refrensi}
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
                    />

                    <Input
                        label="Nama Referensi Rekan"
                        name="nama_refrensi_rekan"
                        value={form.nama_refrensi_rekan}
                        onChange={handleChange}
                        placeholder="Nama rekan kerja"
                        error={errors.nama_refrensi_rekan}
                    />

                    <Input
                        label="Telepon Referensi Rekan"
                        name="telp_refrensi_rekan"
                        value={form.telp_refrensi_rekan}
                        onChange={handleChange}
                        placeholder="Nomor telepon"
                        error={errors.telp_refrensi_rekan}
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
                    />

                    <Input
                        label="Nama Referensi Kerabat"
                        name="nama_refrensi_kerabat"
                        value={form.nama_refrensi_kerabat}
                        onChange={handleChange}
                        placeholder="Nama kerabat"
                        error={errors.nama_refrensi_kerabat}
                    />

                    <Input
                        label="Telepon Referensi Kerabat"
                        name="telp_refrensi_kerabat"
                        value={form.telp_refrensi_kerabat}
                        onChange={handleChange}
                        placeholder="Nomor telepon"
                        error={errors.telp_refrensi_kerabat}
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

function FieldLabel({ label }) {
    return (
        <label className="mb-2 block text-sm font-semibold text-slate-700">
            {label}
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
            ? "cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400"
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
                autoComplete="off"
            />

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
}) {
    return (
        <div>
            <FieldLabel label={label} />

            <select
                name={name}
                value={value ?? ""}
                onChange={onChange}
                className={fieldClass(error)}
            >
                <option value="">Pilih</option>
                <option value="Ya">Ya</option>
                <option value="Tidak">Tidak</option>
            </select>

            <ErrorMessage message={error} />
        </div>
    );
}

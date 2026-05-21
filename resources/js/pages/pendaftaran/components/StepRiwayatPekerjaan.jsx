import React from "react";

export default function StepRiwayatPekerjaan({
    form,
    handleChange,
    errors = {},
}) {
    const statusPekerjaan = form.status_pekerjaan ?? "";
    const isBelumBekerja = statusPekerjaan === "Belum Bekerja";

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                <h3 className="text-base font-bold text-orange-800">
                    Riwayat Pekerjaan
                </h3>
                <p className="mt-1 text-sm text-orange-600">
                    Lengkapi pengalaman kerja terakhir atau pengalaman yang relevan.
                </p>
            </div>

            <div>
                <FieldLabel label="Status Pekerjaan Saat Ini" />

                <select
                    name="status_pekerjaan"
                    value={form.status_pekerjaan ?? ""}
                    onChange={handleChange}
                    className={fieldClass(errors.status_pekerjaan)}
                >
                    <option value="">Pilih status pekerjaan</option>
                    <option value="Belum Bekerja">Belum Bekerja</option>
                    <option value="Sedang Bekerja">Sedang Bekerja</option>
                    <option value="Pernah Bekerja">Pernah Bekerja</option>
                    <option value="Fresh Graduate">Fresh Graduate</option>
                </select>

                <ErrorMessage message={errors.status_pekerjaan} />
            </div>

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
                    label="Posisi / Jabatan"
                    name="posisi_pekerjaan"
                    value={form.posisi_pekerjaan}
                    onChange={handleChange}
                    placeholder={
                        isBelumBekerja
                            ? "Tidak wajib diisi jika belum bekerja"
                            : "Contoh: Staff Administrasi"
                    }
                    error={errors.posisi_pekerjaan}
                    disabled={isBelumBekerja}
                />
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <Input
                    label="Bidang Pekerjaan"
                    name="bidang_pekerjaan"
                    value={form.bidang_pekerjaan}
                    onChange={handleChange}
                    placeholder="Contoh: Administrasi, IT, Marketing"
                    error={errors.bidang_pekerjaan}
                />

                <Input
                    label="Lokasi Perusahaan"
                    name="lokasi_perusahaan"
                    value={form.lokasi_perusahaan}
                    onChange={handleChange}
                    placeholder="Contoh: Jakarta"
                    error={errors.lokasi_perusahaan}
                />
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-3">
                <Input
                    label="Tahun Mulai"
                    name="tahun_mulai_bekerja"
                    value={form.tahun_mulai_bekerja}
                    onChange={handleChange}
                    placeholder="Contoh: 2020"
                    error={errors.tahun_mulai_bekerja}
                />

                <Input
                    label="Tahun Selesai"
                    name="tahun_selesai_bekerja"
                    value={form.tahun_selesai_bekerja}
                    onChange={handleChange}
                    placeholder="Contoh: 2023 / Sekarang"
                    error={errors.tahun_selesai_bekerja}
                />

                <Input
                    label="Lama Bekerja"
                    name="lama_bekerja"
                    value={form.lama_bekerja}
                    onChange={handleChange}
                    placeholder="Contoh: 2 tahun"
                    error={errors.lama_bekerja}
                />
            </div>

            <div>
                <FieldLabel label="Deskripsi Pekerjaan" />

                <textarea
                    name="deskripsi_pekerjaan"
                    value={form.deskripsi_pekerjaan ?? ""}
                    onChange={handleChange}
                    rows="4"
                    placeholder="Jelaskan tugas dan tanggung jawab pada pekerjaan sebelumnya"
                    className={`${fieldClass(errors.deskripsi_pekerjaan)} resize-none`}
                />

                <ErrorMessage message={errors.deskripsi_pekerjaan} />
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <Input
                    label="Alasan Berhenti"
                    name="alasan_berhenti"
                    value={form.alasan_berhenti}
                    onChange={handleChange}
                    placeholder="Contoh: Kontrak selesai"
                    error={errors.alasan_berhenti}
                />

                <Input
                    label="Gaji Terakhir"
                    name="gaji_terakhir"
                    value={form.gaji_terakhir}
                    onChange={handleChange}
                    placeholder="Contoh: 4000000"
                    error={errors.gaji_terakhir}
                />
            </div>

            <div>
                <FieldLabel label="Keahlian / Skill" />

                <textarea
                    name="keahlian"
                    value={form.keahlian ?? ""}
                    onChange={handleChange}
                    rows="3"
                    placeholder="Contoh: Microsoft Office, Komunikasi, Administrasi, Laravel, React"
                    className={`${fieldClass(errors.keahlian)} resize-none`}
                />

                <ErrorMessage message={errors.keahlian} />
            </div>

            <div>
                <FieldLabel label="Catatan Pengalaman Kerja" />

                <textarea
                    name="catatan_pekerjaan"
                    value={form.catatan_pekerjaan ?? ""}
                    onChange={handleChange}
                    rows="3"
                    placeholder="Tulis catatan tambahan jika ada"
                    className={`${fieldClass(errors.catatan_pekerjaan)} resize-none`}
                />

                <ErrorMessage message={errors.catatan_pekerjaan} />
            </div>
        </div>
    );
}

function FieldLabel({ label }) {
    return (
        <label className="mb-2 block text-sm font-semibold text-slate-700">
            {label}
        </label>
    );
}

function ErrorMessage({ message }) {
    if (!message) return null;

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
            />

            <ErrorMessage message={error} />
        </div>
    );
}
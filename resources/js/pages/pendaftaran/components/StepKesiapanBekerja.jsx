import React from "react";

export default function StepKesiapanBekerja({ form, handleChange }) {
    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-cyan-100 bg-cyan-50 p-4">
                <h3 className="text-base font-bold text-cyan-800">
                    Data Kesiapan Bekerja
                </h3>
                <p className="mt-1 text-sm text-cyan-600">
                    Lengkapi informasi kesiapan pelamar untuk mulai bekerja.
                </p>
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <Select
                    label="Bersedia Ditempatkan di Mana Saja"
                    name="bersedia_ditempatkan"
                    value={form.bersedia_ditempatkan}
                    onChange={handleChange}
                    options={["Ya", "Tidak"]}
                />

                <Select
                    label="Bersedia Bekerja Shift"
                    name="bersedia_shift"
                    value={form.bersedia_shift}
                    onChange={handleChange}
                    options={["Ya", "Tidak"]}
                />
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <Select
                    label="Bersedia Lembur"
                    name="bersedia_lembur"
                    value={form.bersedia_lembur}
                    onChange={handleChange}
                    options={["Ya", "Tidak"]}
                />

                <Select
                    label="Bersedia Bekerja di Hari Libur"
                    name="bersedia_hari_libur"
                    value={form.bersedia_hari_libur}
                    onChange={handleChange}
                    options={["Ya", "Tidak"]}
                />
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <Input
                    label="Tanggal Siap Mulai Bekerja"
                    type="date"
                    name="tanggal_siap_kerja"
                    value={form.tanggal_siap_kerja}
                    onChange={handleChange}
                />

                <Input
                    label="Gaji yang Diharapkan"
                    name="gaji_diharapkan"
                    value={form.gaji_diharapkan}
                    onChange={handleChange}
                    placeholder="Contoh: 4000000"
                />
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <Input
                    label="Posisi yang Dilamar"
                    name="posisi_dilamar"
                    value={form.posisi_dilamar}
                    onChange={handleChange}
                    placeholder="Contoh: Staff Administrasi"
                />

                <Input
                    label="Lokasi Kerja yang Diinginkan"
                    name="lokasi_kerja_diinginkan"
                    value={form.lokasi_kerja_diinginkan}
                    onChange={handleChange}
                    placeholder="Contoh: Jakarta / Bandung"
                />
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <Select
                    label="Memiliki Kendaraan Pribadi"
                    name="memiliki_kendaraan"
                    value={form.memiliki_kendaraan}
                    onChange={handleChange}
                    options={["Ya", "Tidak"]}
                />

                <Select
                    label="Memiliki SIM"
                    name="memiliki_sim"
                    value={form.memiliki_sim}
                    onChange={handleChange}
                    options={["Tidak Ada", "SIM A", "SIM C", "SIM A dan C"]}
                />
            </div>

            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                <Select
                    label="Bersedia Mengikuti Pelatihan"
                    name="bersedia_pelatihan"
                    value={form.bersedia_pelatihan}
                    onChange={handleChange}
                    options={["Ya", "Tidak"]}
                />

                <Select
                    label="Status Ikatan Kerja Saat Ini"
                    name="status_ikatan_kerja"
                    value={form.status_ikatan_kerja}
                    onChange={handleChange}
                    options={[
                        "Tidak Ada",
                        "Masih Bekerja",
                        "Kontrak Berjalan",
                        "Menunggu Resign",
                    ]}
                />
            </div>

            <div>
                <label className="mb-2 block text-sm font-semibold text-slate-700">
                    Alasan Melamar
                </label>
                <textarea
                    name="alasan_melamar"
                    value={form.alasan_melamar}
                    onChange={handleChange}
                    rows="4"
                    placeholder="Jelaskan alasan Anda melamar pekerjaan ini"
                    className="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
                />
            </div>

            <div>
                <label className="mb-2 block text-sm font-semibold text-slate-700">
                    Catatan Kesiapan Bekerja
                </label>
                <textarea
                    name="catatan_kesiapan"
                    value={form.catatan_kesiapan}
                    onChange={handleChange}
                    rows="3"
                    placeholder="Tulis catatan tambahan jika ada"
                    className="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
                />
            </div>
        </div>
    );
}

function Input({
    label,
    type = "text",
    name,
    value,
    onChange,
    placeholder,
}) {
    return (
        <div>
            <label className="mb-2 block text-sm font-semibold text-slate-700">
                {label}
            </label>

            <input
                type={type}
                name={name}
                value={value ?? ""}
                onChange={onChange}
                placeholder={placeholder || `Masukkan ${label.toLowerCase()}`}
                className="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            />
        </div>
    );
}

function Select({
    label,
    name,
    value,
    onChange,
    options = [],
}) {
    return (
        <div>
            <label className="mb-2 block text-sm font-semibold text-slate-700">
                {label}
            </label>

            <select
                name={name}
                value={value ?? ""}
                onChange={onChange}
                className="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            >
                <option value="">Pilih {label.toLowerCase()}</option>
                {options.map((item) => (
                    <option key={item} value={item}>
                        {item}
                    </option>
                ))}
            </select>
        </div>
    );
}
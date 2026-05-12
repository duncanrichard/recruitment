import React from "react";

export default function PendaftarPage() {
    const applicants = [
        {
            name: "Andi Saputra",
            position: "Staff Administrasi",
            stage: "Interview",
            date: "12 Mei 2026",
        },
        {
            name: "Rani Wijaya",
            position: "Operator Produksi",
            stage: "Psikotes",
            date: "12 Mei 2026",
        },
        {
            name: "Dimas Pratama",
            position: "Quality Control",
            stage: "Administrasi",
            date: "11 Mei 2026",
        },
    ];

    return (
        <section className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/70">
            <div className="border-b border-slate-100 p-6">
                <h3 className="text-xl font-black text-slate-950">
                    Semua Pendaftar
                </h3>
                <p className="mt-1 text-sm text-slate-500">
                    Daftar seluruh kandidat yang terdaftar di sistem.
                </p>
            </div>

            <div className="overflow-x-auto">
                <table className="w-full min-w-[680px] text-left text-sm">
                    <thead className="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th className="px-6 py-4">Nama</th>
                            <th className="px-6 py-4">Posisi</th>
                            <th className="px-6 py-4">Tahapan</th>
                            <th className="px-6 py-4">Tanggal</th>
                            <th className="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-100">
                        {applicants.map((item) => (
                            <tr
                                key={`${item.name}-${item.position}`}
                                className="transition hover:bg-slate-50"
                            >
                                <td className="px-6 py-4 font-bold text-slate-900">
                                    {item.name}
                                </td>
                                <td className="px-6 py-4 text-slate-700">
                                    {item.position}
                                </td>
                                <td className="px-6 py-4">
                                    <span className="inline-flex rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-teal-700 ring-1 ring-cyan-100">
                                        {item.stage}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-slate-500">
                                    {item.date}
                                </td>
                                <td className="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        className="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200"
                                    >
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </section>
    );
}
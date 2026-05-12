import React from "react";

export default function DashboardPage() {
    const summaryCards = [
        {
            title: "Total Pendaftar",
            value: "248",
            description: "Data pelamar masuk",
            color: "bg-teal-50 text-teal-700 ring-teal-100",
        },
        {
            title: "Menunggu Review",
            value: "36",
            description: "Belum diproses admin",
            color: "bg-amber-50 text-amber-700 ring-amber-100",
        },
        {
            title: "Lolos Seleksi",
            value: "84",
            description: "Kandidat memenuhi kriteria",
            color: "bg-emerald-50 text-emerald-700 ring-emerald-100",
        },
        {
            title: "Tidak Lolos",
            value: "19",
            description: "Tidak memenuhi kualifikasi",
            color: "bg-red-50 text-red-700 ring-red-100",
        },
    ];

    return (
        <div>
            <section className="mb-8 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/70">
                <div className="relative bg-gradient-to-br from-slate-950 via-blue-950 to-teal-900 p-6 text-white sm:p-8">
                    <div className="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-teal-400/10 blur-3xl" />
                    <div className="absolute -bottom-16 -left-16 h-52 w-52 rounded-full bg-cyan-400/10 blur-3xl" />

                    <div className="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <span className="inline-flex rounded-full border border-teal-300/20 bg-teal-300/10 px-4 py-1 text-xs font-bold uppercase tracking-wide text-teal-100">
                                Dashboard Admin
                            </span>

                            <h3 className="mt-4 text-3xl font-black tracking-tight sm:text-4xl">
                                Kelola Proses Rekrutmen
                            </h3>

                            <p className="mt-3 max-w-2xl text-sm leading-7 text-slate-200">
                                Pantau data pelamar, tahapan seleksi, status
                                pendaftaran, dan laporan rekrutmen dalam satu
                                halaman admin.
                            </p>
                        </div>

                        <div className="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <p className="text-sm font-semibold text-slate-200">
                                Status Sistem
                            </p>
                            <p className="mt-2 text-2xl font-black text-teal-200">
                                Aktif
                            </p>
                            <p className="mt-1 text-xs text-slate-300">
                                Siap digunakan untuk input menu berikutnya.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section className="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                {summaryCards.map((card) => (
                    <div
                        key={card.title}
                        className="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60"
                    >
                        <div
                            className={`mb-4 inline-flex rounded-2xl px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 ${card.color}`}
                        >
                            {card.title}
                        </div>

                        <p className="text-4xl font-black text-slate-950">
                            {card.value}
                        </p>

                        <p className="mt-2 text-sm leading-6 text-slate-500">
                            {card.description}
                        </p>
                    </div>
                ))}
            </section>
        </div>
    );
}
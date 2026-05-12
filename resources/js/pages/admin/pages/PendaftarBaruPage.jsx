import React from "react";

export default function PendaftarBaruPage() {
    return (
        <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
            <span className="inline-flex rounded-full bg-teal-50 px-4 py-1 text-xs font-bold uppercase tracking-wide text-teal-700 ring-1 ring-teal-100">
                Sub Menu
            </span>

            <h3 className="mt-4 text-2xl font-black text-slate-950">
                Pendaftar Baru
            </h3>

            <p className="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                Halaman ini digunakan untuk menampilkan data pelamar yang baru
                melakukan pendaftaran dan belum diproses oleh admin.
            </p>
        </section>
    );
}
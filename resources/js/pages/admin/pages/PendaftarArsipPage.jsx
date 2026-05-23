import React from "react";

export default function PendaftarArsipPage() {
    
    return (
        <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
            <span className="inline-flex rounded-full bg-slate-100 px-4 py-1 text-xs font-bold uppercase tracking-wide text-slate-700">
                Sub Menu
            </span>

            <h3 className="mt-4 text-2xl font-black text-slate-950">
                Arsip Pendaftar
            </h3>

            <p className="mt-2 max-w-2xl text-sm leading-7 text-slate-500">
                Halaman ini digunakan untuk menyimpan atau menampilkan data
                pelamar yang sudah selesai diproses.
            </p>
        </section>
    );
}
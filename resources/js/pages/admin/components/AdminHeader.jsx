import React from "react";

export default function AdminHeader({
    activeMenuData,
    showActionButton,
    onOpenSidebar,
    onHeaderAction,
}) {
    return (
        <header className="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div className="flex items-center justify-between gap-4 px-5 py-4 sm:px-8">
                <div className="flex items-center gap-3">
                    <button
                        type="button"
                        onClick={onOpenSidebar}
                        className="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-700 transition hover:bg-slate-200 lg:hidden"
                    >
                        ☰
                    </button>

                    <div>
                        <p className="text-xs font-bold uppercase tracking-wide text-teal-700">
                            {activeMenuData.parentLabel || "Sistem Rekrutmen"}
                        </p>

                        <h2 className="text-lg font-black text-slate-950 sm:text-xl">
                            {activeMenuData.label}
                        </h2>
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    {showActionButton && activeMenuData.action && (
                        <button
                            type="button"
                            onClick={onHeaderAction}
                            className="rounded-2xl bg-teal-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-teal-100 transition hover:bg-teal-700"
                        >
                            {activeMenuData.action.label}
                        </button>
                    )}
                </div>
            </div>
        </header>
    );
}

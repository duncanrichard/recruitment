import React from "react";

export default function StepMenu({ steps, activeStep, setActiveStep }) {
    return (
        <div className="space-y-3">
            {steps.map((item) => {
                const isActive = activeStep === item.id;
                const isDone = activeStep > item.id;

                return (
                    <button
                        key={item.id}
                        type="button"
                        onClick={() => setActiveStep(item.id)}
                        className={`group relative flex w-full items-start gap-4 rounded-2xl border p-4 text-left transition-all duration-300 ${
                            isActive
                                ? "border-blue-200 bg-white shadow-lg shadow-blue-950/10"
                                : isDone
                                ? "border-emerald-200 bg-emerald-50/80 hover:bg-emerald-50"
                                : "border-slate-200 bg-white/70 hover:border-blue-200 hover:bg-white hover:shadow-md"
                        }`}
                    >
                        <div
                            className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-bold transition ${
                                isActive
                                    ? "bg-blue-600 text-white shadow-md shadow-blue-200"
                                    : isDone
                                    ? "bg-emerald-500 text-white"
                                    : "bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600"
                            }`}
                        >
                            {isDone ? "✓" : item.id}
                        </div>

                        <div className="min-w-0 flex-1">
                            <div className="flex items-center justify-between gap-3">
                                <p
                                    className={`text-xs font-semibold uppercase tracking-wide ${
                                        isActive
                                            ? "text-blue-600"
                                            : isDone
                                            ? "text-emerald-600"
                                            : "text-slate-400"
                                    }`}
                                >
                                    Langkah {item.id}
                                </p>

                                {isActive && (
                                    <span className="rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-600">
                                        Aktif
                                    </span>
                                )}
                            </div>

                            <h3
                                className={`mt-1 text-sm font-bold ${
                                    isActive ? "text-slate-950" : "text-slate-700"
                                }`}
                            >
                                {item.title}
                            </h3>

                            <p className="mt-1 text-xs leading-5 text-slate-500">
                                {item.description}
                            </p>
                        </div>
                    </button>
                );
            })}
        </div>
    );
}
import React from "react";

export default function InfoItem({ label, value }) {
    return (
        <div>
            <p className="text-sm text-slate-500">{label}</p>
            <p className="mt-1 font-semibold text-slate-800">
                {value || "-"}
            </p>
        </div>
    );
}
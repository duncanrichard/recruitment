import React, { useEffect, useMemo, useState } from "react";

export default function DataTable({
    data = [],
    columns = [],
    rowKey = "id",
    searchPlaceholder = "Cari data...",
    defaultSortKey = "",
    defaultSortDirection = "asc",
    perPageOptions = [5, 10, 25, 50, 100],
    defaultPerPage = 10,
    emptyTitle = "Data tidak ditemukan",
    emptyDescription = "Belum ada data atau kata kunci pencarian tidak cocok.",
}) {
    const [searchTable, setSearchTable] = useState("");
    const [perPage, setPerPage] = useState(defaultPerPage);
    const [currentPage, setCurrentPage] = useState(1);
    const [sortBy, setSortBy] = useState(defaultSortKey);
    const [sortDirection, setSortDirection] = useState(defaultSortDirection);

    useEffect(() => {
        setCurrentPage(1);
    }, [searchTable, perPage, data]);

    const searchableColumns = useMemo(() => {
        return columns.filter((column) => column.searchable !== false);
    }, [columns]);

    const filteredData = useMemo(() => {
        const keyword = searchTable.toLowerCase().trim();

        if (!keyword) return data;

        return data.filter((item) => {
            return searchableColumns.some((column) => {
                const value = getColumnValue(item, column);
                return String(value || "").toLowerCase().includes(keyword);
            });
        });
    }, [data, searchableColumns, searchTable]);

    const sortedData = useMemo(() => {
        if (!sortBy) return filteredData;

        const selectedColumn = columns.find((column) => column.key === sortBy);

        if (!selectedColumn) return filteredData;

        const sorted = [...filteredData];

        sorted.sort((a, b) => {
            const valueA = getColumnValue(a, selectedColumn);
            const valueB = getColumnValue(b, selectedColumn);

            const compare = String(valueA || "").localeCompare(
                String(valueB || ""),
                "id",
                {
                    numeric: true,
                    sensitivity: "base",
                }
            );

            return sortDirection === "asc" ? compare : -compare;
        });

        return sorted;
    }, [filteredData, columns, sortBy, sortDirection]);

    const totalPages = Math.ceil(sortedData.length / perPage) || 1;

    const paginatedData = useMemo(() => {
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;

        return sortedData.slice(start, end);
    }, [sortedData, currentPage, perPage]);

    const startData =
        sortedData.length === 0 ? 0 : (currentPage - 1) * perPage + 1;

    const endData = Math.min(currentPage * perPage, sortedData.length);

    const handleSort = (column) => {
        if (column.sortable === false) return;

        if (sortBy === column.key) {
            setSortDirection((prev) => (prev === "asc" ? "desc" : "asc"));
            return;
        }

        setSortBy(column.key);
        setSortDirection("asc");
    };

    const getSortIcon = (column) => {
        if (column.sortable === false) return "";
        if (sortBy !== column.key) return "↕";
        return sortDirection === "asc" ? "↑" : "↓";
    };

    return (
        <div className="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <div className="border-b border-slate-100 px-6 py-4">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="text-sm font-bold text-slate-500">
                            Tampilkan
                        </span>

                        <select
                            value={perPage}
                            onChange={(e) => setPerPage(Number(e.target.value))}
                            className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        >
                            {perPageOptions.map((option) => (
                                <option key={option} value={option}>
                                    {option}
                                </option>
                            ))}
                        </select>

                        <span className="text-sm font-bold text-slate-500">
                            data
                        </span>
                    </div>

                    <div className="w-full lg:w-96">
                        <input
                            type="text"
                            value={searchTable}
                            onChange={(e) => setSearchTable(e.target.value)}
                            placeholder={searchPlaceholder}
                            className="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm outline-none transition placeholder:text-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                        />
                    </div>
                </div>
            </div>

            <div className="overflow-x-auto">
                <table className="min-w-full">
                    <thead>
                        <tr className="bg-slate-50/80">
                            {columns.map((column) => (
                                <th
                                    key={column.key}
                                    className={`px-6 py-4 text-${column.align || "left"} text-xs font-black uppercase tracking-[0.12em] text-slate-500`}
                                >
                                    {column.sortable === false ? (
                                        <span>{column.label}</span>
                                    ) : (
                                        <button
                                            type="button"
                                            onClick={() => handleSort(column)}
                                            className={`inline-flex items-center gap-2 transition hover:text-indigo-700 ${
                                                column.align === "right"
                                                    ? "justify-end"
                                                    : "justify-start"
                                            }`}
                                        >
                                            <span>{column.label}</span>
                                            <span className="text-[11px]">
                                                {getSortIcon(column)}
                                            </span>
                                        </button>
                                    )}
                                </th>
                            ))}
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-100 bg-white">
                        {paginatedData.length > 0 ? (
                            paginatedData.map((item, index) => (
                                <tr
                                    key={item[rowKey] || index}
                                    className="group transition hover:bg-slate-50"
                                >
                                    {columns.map((column) => (
                                        <td
                                            key={column.key}
                                            className={`px-6 py-5 text-${column.align || "left"}`}
                                        >
                                            {column.render
                                                ? column.render(item, index)
                                                : getColumnValue(item, column) || "-"}
                                        </td>
                                    ))}
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td
                                    colSpan={columns.length}
                                    className="px-6 py-16"
                                >
                                    <div className="mx-auto max-w-sm text-center">
                                        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-2xl">
                                            📄
                                        </div>

                                        <h3 className="mt-4 text-lg font-black text-slate-900">
                                            {emptyTitle}
                                        </h3>

                                        <p className="mt-2 text-sm font-medium text-slate-500">
                                            {emptyDescription}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <div className="border-t border-slate-100 px-6 py-4">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <p className="text-sm font-bold text-slate-500">
                        Menampilkan{" "}
                        <span className="text-slate-900">{startData}</span>{" "}
                        sampai{" "}
                        <span className="text-slate-900">{endData}</span>{" "}
                        dari{" "}
                        <span className="text-slate-900">
                            {sortedData.length}
                        </span>{" "}
                        data
                    </p>

                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            disabled={currentPage === 1}
                            onClick={() => setCurrentPage(1)}
                            className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            First
                        </button>

                        <button
                            type="button"
                            disabled={currentPage === 1}
                            onClick={() =>
                                setCurrentPage((prev) => Math.max(prev - 1, 1))
                            }
                            className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Prev
                        </button>

                        <span className="rounded-xl bg-indigo-50 px-4 py-2 text-sm font-black text-indigo-700">
                            {currentPage} / {totalPages}
                        </span>

                        <button
                            type="button"
                            disabled={currentPage === totalPages}
                            onClick={() =>
                                setCurrentPage((prev) =>
                                    Math.min(prev + 1, totalPages)
                                )
                            }
                            className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>

                        <button
                            type="button"
                            disabled={currentPage === totalPages}
                            onClick={() => setCurrentPage(totalPages)}
                            className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Last
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

function getColumnValue(item, column) {
    if (typeof column.accessor === "function") {
        return column.accessor(item);
    }

    if (column.accessor) {
        return item[column.accessor];
    }

    return item[column.key];
}
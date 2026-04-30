import PropTypes from "prop-types";

function buildPages(current, last, delta = 2) {
  // 例: current=10, last=30, delta=2
  // => [1, "…", 8, 9, 10, 11, 12, "…", 30]
  const pages = [];
  const left = Math.max(2, current - delta);
  const right = Math.min(last - 1, current + delta);

  pages.push(1);

  if (left > 2) pages.push("…");

  for (let p = left; p <= right; p++) {
    pages.push(p);
  }

  if (right < last - 1) pages.push("…");

  if (last > 1) pages.push(last);

  return pages;
}

export default function Pagination({ meta, onPageChange }) {
  if (!meta) return null;

  const current = meta.current_page ?? 1;
  const last = meta.last_page ?? 1;

  if (last <= 1) return null;

  const pages = buildPages(current, last, 2);

  const go = (p) => {
    if (p === "…") return;
    if (p < 1 || p > last) return;
    if (p === current) return;
    onPageChange(p);
  };

  return (
    <nav className="mt-6 flex items-center justify-center gap-2" aria-label="Pagination">
      {/* Prev */}
      <button
        type="button"
        onClick={() => go(current - 1)}
        disabled={current === 1}
        className={[
          "px-2 py-1.5 rounded border text-sm",
          current === 1 ? "opacity-40 cursor-not-allowed" : "hover:bg-gray-50 cursor-pointer",
        ].join(" ")}
      >
        前へ
      </button>

      {/* Numbers */}
      <ul className="flex items-center gap-1">
        {pages.map((p, idx) => {
          const isActive = p === current;
          const isEllipsis = p === "…";

          if (isEllipsis) {
            return (
              <li key={`e-${idx}`} className="px-2 text-sm text-gray-500 select-none">
                …
              </li>
            );
          }

          return (
            <li key={p}>
              <button
                type="button"
                onClick={() => go(p)}
                aria-current={isActive ? "page" : undefined}
                className={[
                  "min-w-8 px-2 py-1.5 rounded border text-sm",
                  isActive
                  ? "bg-gray-900 text-white border-gray-900"
                  : "hover:bg-gray-50 cursor-pointer",
                ].join(" ")}
              >
                {p}
              </button>
            </li>
          );
        })}
      </ul>

      {/* Next */}
      <button
        type="button"
        onClick={() => go(current + 1)}
        disabled={current === last}
        className={[
          "px-2 py-1.5 rounded border text-sm",
          current === last
          ? "opacity-40 cursor-not-allowed"
          : "hover:bg-gray-50 cursor-pointer",
        ].join(" ")}
      >
        次へ
      </button>
    </nav>
  );
}

Pagination.propTypes = {
  meta: PropTypes.object.isRequired,
  onPageChange: PropTypes.func.isRequired
}
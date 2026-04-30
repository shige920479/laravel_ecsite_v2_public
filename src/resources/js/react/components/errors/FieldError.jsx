import PropTypes from "prop-types";

export default function FieldError({ errors, field, mode = "first", className = "" }) {
  const raw = errors?.[field];
  if (! raw) return null;

  const messages = Array.isArray(raw) ? raw : [String(raw)];

  if (mode === "all") {
    return (
      <ul className={className} role="alert" aria-live="polite">
        {messages.map((msg, i) => (
          <li key={`${field}-${i}`}>{msg}</li>
        ))}
      </ul>
    )
  }

  return (
    <p className={className} role="alert" aria-live="polite">
      {messages[0]}
    </p>
  );
}

FieldError.propTypes = {
  errors: PropTypes.object.isRequired,
  field: PropTypes.string.isRequired,
  mode: PropTypes.string,
  className: PropTypes.string,
}
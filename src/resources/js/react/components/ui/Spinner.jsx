import PropTypes from "prop-types";
import { faSpinner } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

export default function Spinner({
  size = "1x",
  center = false,
  overlay = false,
  className = "",
}) {
  return (
    <div
      className={`
        ${center ? "spinner-center" : ""}
        ${overlay ? "spinner-overlay" : ""}
        ${className}
      `}
    >
      <FontAwesomeIcon icon={faSpinner} spin size={size} className="spinner-icon"/>
    </div>
  )
}

Spinner.propTypes = {
  size: PropTypes.string,
  center: PropTypes.string,
  overlay: PropTypes.string,
  className: PropTypes.string,
}
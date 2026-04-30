import PropTypes from "prop-types"

export default function Modal({children, onClose}) {
 return (
  <div className="modal-overlay" onClick={onClose}>
    <div className="modal-content" onClick={(e) => e.stopPropagation()}>
      <button className="modal-close-btn" onClick={onClose}>
        ×
      </button>
      { children }
    </div>
  </div>
 )
}

Modal.propTypes = {
  children: PropTypes.children,
  onClose: PropTypes.func.isRequired,
}
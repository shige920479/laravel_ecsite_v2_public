import PropTypes from "prop-types"
import { BASE_PATH } from "../../../config";

export default function ItemImage({item}) {

  return (

    <div className="mt-10">
      <a href={`${BASE_PATH}/item/${item.id}`}><img className="h-50 object-cover" src={item.image} alt="" /></a>
      <p><strong className="mr-5">{item.name}</strong><span>{item.shop?.name}</span></p>
      <p>{item.price}円</p>
    </div>

  )
}

ItemImage.propTypes = {
  item: PropTypes.object.isRequired,
}
import PropTypes from "prop-types";
import { Swiper, SwiperSlide } from "swiper/react";
import 'swiper/css';
import "swiper/css/effect-fade";
import { Autoplay, EffectFade, Thumbs } from "swiper/modules";
import { useState } from "react";

export default function ItemImageGallery({ images = [] }) {
  const [thumbsSwiper, setThumbsSwiper] = useState(null);
  if (! images.length) return null;

  return (
    <div className="item-img">
      {/* メイン画像 */}
      <Swiper
        modules={[Thumbs, Autoplay, EffectFade]}
        autoplay={{
          delay: 3000,
          disableOnInteraction: false,
        }}
        effect="fade"
        fadeEffect={{ crossFade: true }}
        thumbs={{ swiper: thumbsSwiper }}
        spaceBetween={0}
      >
        {images.map((img) => (
          <SwiperSlide key={img.id}>
            <img
              src={img.filename}
              className="w-100 h-75 object-cover"
            />
          </SwiperSlide>
        ))}
      </Swiper>
      {/* サムネイル画像 */}
      <Swiper
        onSwiper={setThumbsSwiper}
        spaceBetween={10}
        slidesPerView={4}
        className="mt-2"
      >
        {images.map((image) => (
          <SwiperSlide key={image.id}>
            <img
              src={image.filename} 
              alt=""
              className="cursor-pointer w-30 h-16 object-cover"
            />
          </SwiperSlide>
        ))}
      </Swiper>
    </div>
  )
}

ItemImageGallery.propTypes = {
  images: PropTypes.array.isRequired,
}
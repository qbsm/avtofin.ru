export default () => {
    const classNames = {
      map: 'js-map',
      balloon: 'map__balloon',
    };

    const $maps = $(`.${classNames.map}`);
    const $window = $(window);

    if (!$maps.length) {
      return false;
    }

    function createMap(indexMap, map) {
      function init() {
        const $map = $(map);
        const $balloons = $map.find(`.${classNames.balloon}`);
        const center = String($map.attr('data-center')).split(',');
        const centerSM = String($map.attr('data-centerSM')).split(',');
        const zoom = $map.attr('data-zoom');
        const zoomSM = $map.attr('data-zoomSM');
        const containerId = $map.attr('data-container-id');

        let currentCenter = center;
        let currentZoom = zoom;

        if ($window.width() < 992) {
          console.log(1);
          currentCenter = centerSM;
          currentZoom = zoomSM;
        }

        const myMap = new ymaps.Map(containerId, {
          zoom: currentZoom,
          center: currentCenter,
          controls: ["zoomControl"],
        });

        $balloons.each((indexBalloon, balloon) => {
          const $balloon = $(balloon);
          const content = $balloon.wrap('<div></div>').parent().html();
          const icon = $balloon.data('icon');
          const location = $balloon.data('location').split(',');
          const imageSize = $balloon.data('image-size').split(',').map(item => +item);
          const imageOffset = $balloon.data('image-offset').split(',').map(item => +item);

          const placemark = new ymaps.Placemark(location,
            {
              balloonContent: content,
            },
            {
              iconLayout: 'default#image',
              iconImageHref: icon,
              iconImageSize: imageSize,
              iconImageOffset: imageOffset,
            },
          );

          myMap.geoObjects.add(placemark);
          myMap.panes.get('ground').getElement().style.filter = 'grayscale(100%)';
        });
        myMap.behaviors.disable('scrollZoom');

        $window.resize( function handleResizeWindow(e) {
          if ($window.width() < 992) {
            myMap.setCenter(centerSM);
            myMap.setZoom(zoomSM);
          } else {
            myMap.setCenter(center);
            myMap.setZoom(zoom);
          }
        })

        //myMap.options.set('maxAnimationZoomDifference', Infinity);
        $('.js-map-link').on('click', function() {
          const loc = $(this).data('location').split(',');
          myMap.setCenter(loc);
          //myMap.setZoom(10);
          //setTimeout(() => {  myMap.setZoom(16, {duration: 500}); }, 700);
          myMap.setZoom(16);
          return false;
        });

        if (window.innerWidth < 768) {
          myMap.behaviors.disable('drag');
        }
      }

      ymaps.ready(init);
    }


    $window.on('scroll', function renderMap() {
      $maps.each(createMap);
      $window.off('scroll');
    })

    return false;

  };

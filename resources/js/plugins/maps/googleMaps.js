import {googleMapsApiKey} from '../../config';
import mapStyles from './mapStyles';

const isScriptLoaded = lib => {
  return document.querySelectorAll(`[src="${lib}"]`).length > 0;
};

const loadMapsScript = () => {
  if (window.google !== undefined && window.google.maps !== undefined) {
    return;
  }
  const script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsApiKey}`;
  if (!isScriptLoaded(script.src)) {
    document.body.appendChild(script);
  }
};


class LibryoMap {

  constructor(element, activeLibryo, zoom, centerLat, centerLng) {
    this.element = element;
    this.activeLibryo = activeLibryo;
    this.zoom = zoom;
    this.centerLat = centerLat;
    this.centerLng = centerLng;
    this.map = {};
    this.activeLibryoMarker = {};
    this.allLibryos = [];
    this.otherMarkers = [];
    this.infoWindow = {};
    this.loadLibryosTimeout = {};
    this.address = '';
    this.zooming = false;
    this.countGoogleRetries = 0;
    this.panorama = null;
    this.initializeMapWhenGoogleReady();
  }

  initMap() {
    if (typeof window.google !== 'object') {
      return;
    }
    this.map = new window.google.maps.Map(this.element, {
      zoom: this.zoom,
      center: { lat: this.centerLat, lng: this.centerLng },
      styles: mapStyles,
    });

    this.map.addListener('idle', () => {
      this.loadLibryos();
    });

    this.infoWindow = new window.google.maps.InfoWindow();
  }

  getIconForMarker(place) {
    let iconSrc = '/img/map-icons/marker.png';
    if (place.quantity > 1 && place.quantity < 10) {
      iconSrc = '/img/map-icons/m1.png';
    } else if (place.quantity >= 10 && place.quantity < 100) {
      iconSrc = '/img/map-icons/m2.png';
    } else if (place.quantity >= 100 && place.quantity < 1000) {
      iconSrc = '/img/map-icons/m3.png';
    } else if (place.quantity >= 1000 && place.quantity < 10000) {
      iconSrc = '/img/map-icons/m4.png';
    } else if (place.quantity >= 10000 && place.quantity < 100000) {
      iconSrc = '/img/map-icons/m5.png';
    }
    return iconSrc;
  }

  libryoMarkers() {
    if (typeof this.map.getDiv !== 'function') {
      return;
    }

    if (typeof this.activeLibryoMarker.setMap === 'function') {
      this.activeLibryoMarker.setMap(null);
    }

    if (this.activeLibryo) {
      //this.setActiveLibryoMarker();
    }

    this.otherMarkers.forEach(marker => marker.setMap(null));
    this.otherMarkers = [];
    this.allLibryos.forEach(place => {
      const newMarker = this.createNewMarker(
        {
          position: this.getPositionFromLibryo(place),
          title: place.quantity > 1 ? place.quantity.toString() : place.title,
          label: place.quantity > 1 ? place.quantity.toString() : null,
          icon: this.getIconForMarker(place),
        },
        place,
      );
      this.otherMarkers.push(newMarker);
    });

    // only set the map once all markers have been created
    this.otherMarkers.forEach(marker => {
      marker.setMap(this.map);
    });
  }

  setActiveLibryoMarker() {
    const markerProps = {
      position: this.activeLibryoPosition,
      title: this.activeLibryo.title,
      icon: '/img/map-icons/active.png',
      zIndex: 10,
    };

    if (this.editable) {
      markerProps.draggable = true;
    }

    this.activeLibryoMarker = this.createNewMarker(markerProps, this.activeLibryo);
    this.activeLibryoMarker.setMap(this.map);

    if (!this.editable) return;

    window.google.maps.event.clearListeners(this.activeLibryoMarker, 'dragend');
    this.activeLibryoMarker.addListener('dragend', e => {
      this.$emit('marker-moved', {
        lat: e.latLng.lat(),
        lng: e.latLng.lng(),
      });
    });
  }

  createNewMarker(markerOptions, place) {
    const marker = new window.google.maps.Marker(markerOptions);
    this.addMarkerClickListener(marker, place);
    return marker;
  }

  addMarkerClickListener(marker, place) {
    marker.addListener('click', () => {
      if (place.quantity > 1) {
        this.map.setCenter(marker.getPosition());
        this.map.setZoom(this.map.getZoom() + 1);
      } else {
        const content = this.getMarkerContent(marker, place);
        this.infoWindow.setContent(content);
        this.infoWindow.open(this.map, marker);
        this.addInfoWindowClick(place);
      }
    });
  }

  addInfoWindowClick(place) {
    setTimeout(() => {
      const marker = document.getElementById(`map_libryo_${place.id}`);
      if (marker) marker.addEventListener('click', this.handleMarkerClick);
    }, 100);
  }

  handleMarkerClick(event) {
    const id = event.srcElement.getAttribute('data-id');
    if (id !== this.activeLibryo.id) {
      this.$emit('update', id);
    }
  }

  getMarkerContent(marker, libryo) {
    /* eslint-disable prefer-template */
    return `<a id="map_libryo_${libryo.id}" data-id="${libryo.id}">${marker.title}</a><small>
      <br/>
      ${libryo.address ? '<br/>' + this.$t.t('libryos.address') + ': ' + libryo.address : ''}
      </small>
    `;
    /* eslint-enable prefer-template */
  }

  getPositionFromLibryo(libryo) {
    return {
      lat: parseFloat(libryo.geo_lat),
      lng: parseFloat(libryo.geo_lng),
    };
  }

  loadLibryos() {
    const northEast = this.map.getBounds().getNorthEast();
    const southWest = this.map.getBounds().getSouthWest();
    const north = northEast.lat();
    const east = northEast.lng();
    const south = southWest.lat();
    const west = southWest.lng();
    return window.axios
      .get(
        `/libryos/markers?bounds=${north},${east},${south},${west}&zoom=${this.map.getZoom()}`,
      )
      .then((response) => {
        this.allLibryos = response.data;
        this.libryoMarkers();
        this.zooming = false;
        return this.allLibryos;
      });
  }

  geocodeAddress() {
    const geocoder = new window.google.maps.Geocoder();
    geocoder.geocode({ address: this.address }, (results, status) => {
      if (status === 'OK') {
        this.map.setCenter(results[0].geometry.location);
        if (results[0].geometry.viewport) {
          this.map.fitBounds(results[0].geometry.viewport);
        }
      } else {
        // eslint-disable-next-line no-console
      }
    });
  }

  initializeMapWhenGoogleReady() {
    // prevent infinite loop if there's something wrong with google
    if (this.countGoogleRetries > 20) {
      return;
    }
    loadMapsScript();
    this.countGoogleRetries += 1;
    if (window.google === undefined || !this.activeLibryo) {
      setTimeout(() => {
        this.initializeMapWhenGoogleReady();
      }, 500);
      return;
    }

    this.initMap();
  }


  setupStreetView() {
    this.panorama = this.map.getStreetView();
    this.panorama.setPosition(this.activeLibryoPosition);
    this.panorama.setPov({
      heading: 0,
      pitch: -10,
    });
  }
}


window.LibryoMap = LibryoMap;

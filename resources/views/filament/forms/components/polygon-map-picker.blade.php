<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="polygonMapPicker({
            state: $wire.$entangle('{{ $getStatePath() }}'),
            center: [{{ $getCenter()[0] }}, {{ $getCenter()[1] }}],
            zoom: {{ $getZoom() }}
        })"
        wire:ignore
        class="w-full"
    >
        <div
            x-ref="map"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm overflow-hidden"
            style="height: {{ $getHeight() }}px; min-height: 400px; z-index: 1;"
        ></div>

        {{-- Leaflet Assets --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        {{-- Geoman Assets --}}
        <link
            rel="stylesheet"
            href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css"
        />
        <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>

        <link href='https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css' rel='stylesheet'>
        <link href='https://cdn.boxicons.com/3.0.8/fonts/filled/boxicons-filled.min.css' rel='stylesheet'>
        <link href='https://cdn.boxicons.com/3.0.8/fonts/brands/boxicons-brands.min.css' rel='stylesheet'>
    </div>
</x-dynamic-component>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('polygonMapPicker', ({ state, center, zoom }) => ({
            state,
            center,
            zoom,
            map: null,
            marker: null,
            polygonLayer: null,
            primaryColor: '#e7000b',

            init() {
                this.$watch('state', (value) => {
                    if (value && !this.polygonLayer && this.map) {
                        this.loadPolygon(value);
                    }
                });

                // Handle Filament Modal opening
                window.addEventListener('open-modal', (event) => {
                    this.$nextTick(() => {
                        if (this.map) {
                            setTimeout(() => {
                                this.map.invalidateSize();
                                if (this.polygonLayer) {
                                    this.map.fitBounds(this.polygonLayer.getBounds());
                                } else {
                                    this.map.setView(this.center, this.zoom);
                                }
                            }, 300);
                        }
                    });
                });

                const initMap = () => {
                    if (typeof L === 'undefined' || typeof this.$refs.map === 'undefined') {
                        setTimeout(initMap, 100);
                        return;
                    }

                    this.map = L.map(this.$refs.map, {
                        zoomControl: false,
                        attributionControl: false
                    }).setView(this.center, this.zoom);

                    // Resize Observer to handle any container size changes
                    new ResizeObserver(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        }
                    }).observe(this.$refs.map);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        maxZoom: 20
                    }).addTo(this.map);

                    // Geoman controls
                    this.map.pm.addControls({
                        position: 'topleft',
                        drawCircleMarker: false,
                        drawMarker: false,
                        drawPolyline: false,
                        drawRectangle: false,
                        drawCircle: false,
                        drawText: false,
                        cutPolygon: true,
                        dragMode: true,
                        rotateMode: false,
                        oneBlock: true,
                    });

                    this.map.pm.setGlobalOptions({
                        pinning: true,
                        snapping: true,
                        pathOptions: {
                            color: this.primaryColor,
                            fillColor: this.primaryColor,
                            fillOpacity: 0.2,
                        },
                        templineStyle: {
                            color: this.primaryColor,
                        },
                        hintlineStyle: {
                            color: this.primaryColor,
                            dashArray: [5, 5],
                        },
                    });

                    // Set global path options for existing and new layers
                    this.map.pm.setPathOptions({
                        color: this.primaryColor,
                        fillColor: this.primaryColor,
                        fillOpacity: 0.2,
                    });

                    // Load initial state
                    if (this.state) {
                        this.loadPolygon(this.state);
                    }

                    this.map.on('pm:create', (e) => {
                        if (e.shape === 'Polygon') {
                            // Enforce single polygon
                            if (this.polygonLayer) {
                                this.map.removeLayer(this.polygonLayer);
                            }
                            this.polygonLayer = e.layer;
                            
                            // Ensure the new layer has the correct style
                            this.polygonLayer.setStyle({
                                color: this.primaryColor,
                                fillColor: this.primaryColor,
                                fillOpacity: 0.2,
                            });

                            this.updateState();
                            this.setupLayerEvents(this.polygonLayer);
                            this.updateMarker(true); // Center map on new polygon
                        }
                    });

                    this.map.on('pm:remove', (e) => {
                        if (e.layer === this.polygonLayer) {
                            this.polygonLayer = null;
                            if (this.marker) {
                                this.map.removeLayer(this.marker);
                                this.marker = null;
                            }
                            this.state = null;
                        }
                    });

                    setTimeout(() => this.map.invalidateSize(), 200);
                };

                initMap();
            },

            getIcon() {
                return L.divIcon({
                    html: `
                        <div class="relative flex flex-col items-center">
                            <div class="relative group">
                                <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-4 h-1.5 bg-black/20 rounded-[100%] blur-[1px]"></div>
                                <div class="relative animate-bounce-slow">
                                    <svg width="50" height="60" viewBox="0 0 50 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="drop-shadow-2xl">
                                        <path d="M25 0C11.1929 0 0 11.1929 0 25C0 39.5 25 60 25 60C25 60 50 39.5 50 25C50 11.1929 38.8071 0 25 0Z" fill="${this.primaryColor}"/>
                                        <circle cx="25" cy="24" r="18" fill="white"/>
                                    </svg>
                                    <div class="absolute top-[12px] left-[13px]">
                                        <i class="bxf bx-carrot text-2xl" style="color: ${this.primaryColor}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `,
                    className: '',
                    iconSize: [50, 60],
                    iconAnchor: [25, 60],
                });
            },

            loadPolygon(geojson) {
                try {
                    if (typeof geojson === 'string') {
                        geojson = JSON.parse(geojson);
                    }

                    if (!geojson || !this.map) {
                        return;
                    }

                    // Check if it's a valid GeoJSON (Feature or FeatureCollection)
                    const hasFeatures = geojson.type === 'FeatureCollection' && geojson.features && geojson.features.length > 0;
                    const isFeature = geojson.type === 'Feature';

                    if (hasFeatures || isFeature) {
                        // Clear existing layer if any
                        if (this.polygonLayer) {
                            this.map.removeLayer(this.polygonLayer);
                        }

                        const geoJsonLayer = L.geoJSON(geojson, {
                            style: {
                                color: this.primaryColor,
                                fillColor: this.primaryColor,
                                fillOpacity: 0.2,
                            }
                        });
                        const layers = geoJsonLayer.getLayers();

                        if (layers.length > 0) {
                            this.polygonLayer = layers[0];
                            this.polygonLayer.addTo(this.map);
                            this.setupLayerEvents(this.polygonLayer);
                            this.updateMarker(false);

                            const bounds = this.polygonLayer.getBounds();
                            if (bounds.isValid()) {
                                this.map.fitBounds(bounds);
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error loading polygon:', e);
                }
            },

            setupLayerEvents(layer) {
                layer.on('pm:update pm:dragend pm:markerdragend pm:edit', () => {
                    this.updateState();
                });
            },

            updateState() {
                if (this.polygonLayer) {
                    const feature = this.polygonLayer.toGeoJSON();
                    // Wrap in FeatureCollection to match model expectation and GeoJSON standards
                    this.state = {
                        type: 'FeatureCollection',
                        features: [feature]
                    };
                    this.updateMarker(false);
                }
            },

            updateMarker(shouldCenter = false) {
                if (this.polygonLayer) {
                    const bounds = this.polygonLayer.getBounds();
                    const center = bounds.getCenter();

                    if (!this.marker) {
                        this.marker = L.marker(center, {
                            interactive: false,
                            draggable: false,
                            icon: this.getIcon()
                        }).addTo(this.map);
                    } else {
                        this.marker.setLatLng(center);
                        if (!this.map.hasLayer(this.marker)) {
                            this.marker.addTo(this.map);
                        }
                    }

                    if (shouldCenter) {
                        this.map.panTo(center);
                    }
                } else if (this.marker) {
                    this.map.removeLayer(this.marker);
                    this.marker = null;
                }
            }
        }))
    })
</script>

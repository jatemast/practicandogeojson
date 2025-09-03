<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mapa de Colombia</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #map {
            height: 100%;
            width: 100%;
        }
        .filter-box {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 8px 12px;
            border-radius: 5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="filter-box">
        <label for="departamento">Filtrar por departamento: </label>
        <select id="departamento">
            <option value="all">Todos</option>
        </select>
    </div>
    <div id="map"></div>

    <script>
        // Inicializamos el mapa
        var map = L.map('map').setView([4.5709, -74.2973], 6);

        // Capa base
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let geojsonLayer;
        let departamentos = [];
        let geoData;

        // Cargar GeoJSON
        fetch("{{ asset('geojson/co.json') }}")
            .then(res => res.json())
            .then(data => {
                geoData = data;

                // Guardar todos los departamentos
                data.features.forEach(f => {
                    departamentos.push(f.properties.name);
                });

                // Llenar select con nombres de departamentos
                let select = document.getElementById('departamento');
                [...new Set(departamentos)].sort().forEach(dep => {
                    let option = document.createElement('option');
                    option.value = dep;
                    option.text = dep;
                    select.add(option);
                });

                // Mostrar todos los departamentos al inicio
                geojsonLayer = L.geoJSON(data, {
                    style: {
                        color: "#3388ff",
                        weight: 1,
                        fillOpacity: 0.3
                    },
                    onEachFeature: function (feature, layer) {
                        layer.bindPopup("<b>" + feature.properties.name + "</b>");
                    }
                }).addTo(map);

                // Evento para filtrar
                select.addEventListener('change', function () {
                    if (geojsonLayer) {
                        geojsonLayer.clearLayers();
                    }

                    if (this.value === "all") {
                        geojsonLayer.addData(data);
                        map.setView([4.5709, -74.2973], 6); // Resetear vista
                    } else {
                        let filtered = {
                            type: "FeatureCollection",
                            features: data.features.filter(f => f.properties.name === this.value)
                        };

                        geojsonLayer = L.geoJSON(filtered, {
                            style: {
                                color: "green",
                                weight: 2,
                                fillColor: "green",
                                fillOpacity: 0.5
                            },
                            onEachFeature: function (feature, layer) {
                                layer.bindPopup("<b>" + feature.properties.name + "</b>");
                            }
                        }).addTo(map);

                        // Hacer zoom al departamento seleccionado
                        map.fitBounds(geojsonLayer.getBounds());
                    }
                });
            });
    </script>
</body>
</html>

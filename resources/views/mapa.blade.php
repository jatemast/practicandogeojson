<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mapa México - Estados y Municipios</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 90vh; margin-top: 10px; }
        select { margin: 5px; padding: 5px; }
    </style>
</head>
<body>
    <h2>Mapa de México</h2>

    <select id="estado">
        <option value="">Seleccione Estado</option>
    </select>

    <select id="municipio">
        <option value="">Seleccione Municipio</option>
    </select>

    <div id="map"></div>

    <script>
    const map = L.map('map').setView([23.6345, -102.5528], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18
    }).addTo(map);

    let estadosData = [];
    let municipiosData = [];
    let estadoLayer;
    let municipioLayer;

    const estadoSelect = document.getElementById('estado');
    const municipioSelect = document.getElementById('municipio');

    // 1. Cargar Estados
    fetch('https://soymetrix.com/api/estados')
        .then(res => res.json())
        .then(data => {
            estadosData = data.features;
            estadosData.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.properties.id;
                opt.textContent = e.properties.nombre || `Estado ${e.properties.id}`;
                estadoSelect.appendChild(opt);
            });
        });

    // 2. Cargar Municipios (una sola vez)
    fetch('https://soymetrix.com/api/municipios')
        .then(res => res.json())
        .then(data => {
            municipiosData = data.features;
        });

    // 3. Cuando selecciona un estado
    estadoSelect.addEventListener('change', function() {
        if (estadoLayer) map.removeLayer(estadoLayer);
        if (municipioLayer) map.removeLayer(municipioLayer);
        municipioSelect.innerHTML = '<option value="">Seleccione Municipio</option>';

        const estadoId = this.value;
        if (!estadoId) return;

        const estado = estadosData.find(e => e.properties.id == estadoId);
        if (!estado) return;

        const geom = JSON.parse(estado.geometry);
        estadoLayer = L.geoJSON(geom, {
            style: { color: 'blue', weight: 2, fillColor: '#66c2a5', fillOpacity: 0.5 }
        }).addTo(map);
        map.fitBounds(estadoLayer.getBounds());

        // Llenar municipios
        const municipiosFiltrados = municipiosData.filter(m => m.properties.estado_id == estadoId);
        municipiosFiltrados.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.properties.id;
            opt.textContent = m.properties.nombre || `Municipio ${m.properties.id}`;
            municipioSelect.appendChild(opt);
        });
    });

    // 4. Cuando selecciona un municipio
    municipioSelect.addEventListener('change', function() {
        if (municipioLayer) map.removeLayer(municipioLayer);

        const municipioId = this.value;
        if (!municipioId) return;

        const municipio = municipiosData.find(m => m.properties.id == municipioId);
        if (!municipio) return;

        const geom = JSON.parse(municipio.geometry);
        municipioLayer = L.geoJSON(geom, {
            style: { color: 'green', weight: 2, fillColor: '#fc8d62', fillOpacity: 0.5 }
        }).addTo(map);
        map.fitBounds(municipioLayer.getBounds());
    });
    </script>
</body>
</html>

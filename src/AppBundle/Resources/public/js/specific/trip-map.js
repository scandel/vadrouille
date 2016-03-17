/*
 * JS for the visualization of the trip on a map
 * (with Mappy).
 * Template : tripmap.html.twig
 * Initialisation :
 * - Show the map
 * - Put markers
 * - Draw route
 * - "onclick" events on markers and links
 */

// Nb max d'étapes
var max_stops = 5
var maxmax = 200;

// Carto
var map, markerLayer;
var polyline = null;
var markers = new Array();
var travel_time=0;
var currentRoadbook = null;
var modify = 0;

if (typeof L !== 'undefined') {
    france = new Object;
    france.center = L.latLng(46.40, 2.60); // (3.082418, 45.777168);
    france.zoom = 3;
}

// Array of stops positions
var pos= new Array();

if (typeof L !== 'undefined') {
    //Extend the Default marker class
    var RedIcon = L.Icon.Default.extend({
        options: {
            iconUrl: '/bundles/app/img/icons/marker-icon_red.png'
        }
    });

    var GreenIcon = L.Icon.Default.extend({
        options: {
            iconUrl: '/bundles/app/img/icons/marker-icon_green.png'
        }
    });
}

/*=======  Fonctions Mappy pour le départ et l'arrivée ================*/

/**
 * Initialisation carte
 *
 */
function MapInit(roadbook = null) {

    if (typeof L !== 'undefined') {
        // Création de la carte
        map = new L.Mappy.Map('map', {
            layersControl: false,
            logoControl: {
                dir: "/bundles/app/mappy/images/"
            }
        });

        // Affiche l'itinéraire sur la carte,
        // centre sur l'itinéraire, et affiche les infos trajet.

        if (roadbook) {
            polyline = L.polyline(roadbook["polyline-definition"].polyline).addTo(map);
            // zoom the map to the polyline
            map.fitBounds(polyline.getBounds());

            // Temps et distance actualisés
            console.log("Roadbook : ", roadbook);

            travel_time = roadbook.summary['time'];
            var time = sprintf("%dh%02d", Math.floor(travel_time / 3600), Math.floor((travel_time / 60) % 60));
            if ($("#time").text() == '0') {
                $("#time").text(time);
            }

            var length = Math.floor(roadbook.summary['length'] / 1000) + "km";
            $("#distance").text(length);
        }
        else {
            map.setView( france.center, france.zoom);
        }

        // Instantiation du token pour utiliser le service de route
        L.Mappy.setToken("nbgQVKjuZyTf4Xp/oddeUjQZXtPzwBqZylL5hyS+pzuqM1Lge5kASz+cCeRN/+6FatA3ADO6/mWuxlNsPshDTQ==");

        /* Cadre Pour les marqueurs */
        markerLayer = L.layerGroup().addTo(map);

    }
}

/**
 * Met un marqueur sur le point de RV;
 * ne change pas l'affichage de la carte
 *
 * @param stop : an index of pos
 * @param type : 'dep', 'arr' or null
 */
function MapPutMarker(stop, type = null) {
    if (typeof L !== 'undefined') {
        // console.log("Marker ",stop,pos[stop]);
        switch (type) {
            case 'dep':
                var icon = new GreenIcon();
                var tooltip = 'Cliquez pour centrer la carte sur le point de départ.';
                break;
            case 'arr':
                var icon = new RedIcon();
                var tooltip = 'Cliquez pour centrer la carte sur le point d \'arrivée.';
                break;
            default:
                var icon = new L.Icon.Default();
                var tooltip = 'Cliquez pour centrer la carte sur l\'étape.';
                break;
        }

        if (pos[stop].set) {
            markerLayer.removeLayer(markers[stop]);
        }
        else pos[stop].set = true;

        markers[stop] = L.marker(pos[stop].center, {
            title: tooltip,
            icon: icon
        }).addTo(markerLayer);

        markers[stop].on('click', function () {
         MapCenter(stop);
         });
        // console.log(markers);
    }
}


/**
 * Remove a marker
 * @param stop  : index of pos
 */
function MapRemoveMarker(stop) {
    markerLayer.removeLayer(markers[stop]);
    markers[stop]=null;
    pos[stop].set = false;
}


/**
 * Centre la carte sur le point de départ, arrivée, ou étape
 * @param item  : l'index de pos
 */
function MapCenter(item) {
    // console.log("MapCenter ", item);
    if (!pos[item].set) {
        alert('La ville  n\'est pas définie');
    }
    else {
        map.setView(pos[item].center, 9);
    }
}

/**
 * Centre la carte sur le trajet global
 */
function MapTripCenter() {
    map.fitBounds(polyline.getBounds());
}

/**
 * Execute on page load, except on trip form page 1
 */
function MapPageInit() {

    //-- Fill time if given in stops
    if (typeof arr_time !== 'undefined' && parseInt(arr_time) > 0) {
        var time = sprintf("%dh%02d", Math.floor(arr_time / 3600), Math.floor((arr_time / 60) % 60));
        $("#time").text(time);
    }

    //---- Init Map with road polyline ----
    if (typeof roadbook != 'undefined') {
        MapInit(roadbook);
    }
    else {
        MapInit();
    }

    if (pos.length >= 2) {

        //---- Init markers : pour les stops remplis  ---
        // They are already ordered by delta
        $.each(pos, function (index, stop) {
            if (typeof pos[index] !== 'undefined') {
                var type = (index == 0) ? 'dep' : (index == (pos.length - 1)) ? 'arr' : null;
                MapPutMarker(index, type);
            }
        });

        //---- Liens de centrage de la carte ----
        $.each(['dep','arr'],function(index, item) {
            $('#'+item+'_center').click(function(e) {
                e.preventDefault();
                switch(item) {
                    case 'dep':
                        MapCenter(0);
                        break;
                    case 'arr':
                        MapCenter(pos.length-1);
                        break;
                }
            });
        });
        $('#trip_center').click(function(e) {
            e.preventDefault();
            if (travel_time!=0) MapTripCenter();
            else alert('Le trajet n\'est pas défini !');
        });
    }
}
/*
 * JS for the "edit trip" page
 */

// Nb max d'étapes
var max_stops = 5
var maxmax = 200;

// Carto
var map, markerLayer;
var polyline = null;
var markers = new Array();
var travel_time=0;
// var activeSteps = 0; // nb d'étapes actives (visibles - meme si vides)
var currentRoadbook = null;
var modify = 0;

if (typeof L !== 'undefined') {
    france = new Object;
    france.center = L.latLng(46.40, 2.60); // (3.082418, 45.777168);
    france.zoom = 3;
}

// Array of stops positions
// 0 = departure, 1 = arrival, 2+ = intermediate stops
var pos= new Array();
for (var i=0; i < maxmax; i++){
    pos['app_trip_edit_stops_' + i] = new Object;
    pos['app_trip_edit_stops_' + i].set = false;
}

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

/**
 * Remplit les temps de parcours (hidden) pour les différentes étapes
 * @param roadbook
 */
function UpdateTimes(roadbook) {

    if (roadbook==null) {
        Itinerary();
        return;
    }

    // Update temps total
    var total_time = roadbook.summary.time;
    total_time = Math.round(total_time/300)*300 ; // arrondi à 5 minutes près
    // Update temps total
    $('#app_trip_edit_stops_1_time').val(total_time);

    // Les temps des différentes étapes
    var nActions = roadbook.actions.action.length;

    // Calcul n°s des étapes
    var steps = new Array();
    var index = 0;
    for (var i=2; i < maxmax; i++){
       if ( pos['app_trip_edit_stops_' + i].set ) {
           steps[index] = i;
           index++;
       }
    }

    var index = 0;
    for (var i = 0 ; i <nActions ; i++) {
        if (roadbook.actions.action[i].type == 'waypoint') {
            // debug(roadbook.actions.action[i]);
            travel_time = parseInt(roadbook.actions.action[i].sec) ;
            travel_time = Math.round(travel_time/300)*300 ; // arrondi à 5 minutes pr?s
            // Update temps étape
            $('#app_trip_edit_stops_'+steps[index]+'_time').val(travel_time);
            index++;
          }
    }
}


jQuery(document).ready(function() {
    console.log('Ready !');
});
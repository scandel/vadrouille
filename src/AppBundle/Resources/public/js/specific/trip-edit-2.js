/*
 * JS for the "edit trip" page
 */

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
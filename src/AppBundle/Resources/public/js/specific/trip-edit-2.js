/*
 * JS for the "post/edit trip" form, page 2
 */

/**
 * Renvoie l'élément de formulaire de Stop pour le delta donné
 */
function findStopFormElementByDelta(delta) {
    if ( $('input[id^=app_trip_edit_stops][id$=delta][value="'+ delta + '"]').length ) {
        return $('input[id^=app_trip_edit_stops][id$=delta][value="' + delta + '"]').parent();
    }
    console.log("Pas trouvé ",delta);
    return null;
}

function setPricesFromDiff() {

    // Les prix différentiels
    var diffs = [];
    $('#stop_prices input').each( function() {
        diffs.push( $( this ).val() );
    });
    console.log("Prix différentiels : ", diffs);

    // Actualiser les champs "prix" cachés
    var price = 0;
    var nbOfStops = $('div[id^=app_trip_edit_stops_]').length;
    for (var delta=0; delta < nbOfStops; delta++) {
        findStopFormElementByDelta(delta).find('input[id$=price]').val(price);
        price += parseInt(diffs[delta]);
    }
}


/**
 * Remplit les temps de parcours (hidden) pour les différentes étapes
 * @param roadbook
 */
function updateTimes(roadbook) {

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

    // Empêche l'utilisateur de rentrer manuellement autre chose que des
    // entiers positifs dans les champs prix différentiels
    $('#stop_prices input').blur( function(){
        var v = parseInt( $(this).val(), 10) ;
        if (isNaN(v) || v < 0 ){
            $(this).val(0);
            setPricesFromDiff();
        }
    });

    // Actualise les champs cachés de prix quand les prix différentiels
    // entre étapes sont modifiés manuellement
    $('#stop_prices input').change( function() {
        setPricesFromDiff()
    });


});
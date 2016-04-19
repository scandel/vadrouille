/*
 * JS for the "post/edit trip" form, page 2
 */

var ajaxCallsRemaining;

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

/*=======  Calcul des prix  ================*/

/**
 * Actualise les prix des étapes (champs hidden des formulaires stops)
 * en fonction des prix différentiels (modifiés par l'utilisateur)
 */
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
 * Calcule le total et actualise l'element #total
 */
function setTotal() {
    if ( $('#total').length ) {
        var total = 0;
        $('#stop_prices input').each(function () {
            total += parseInt($(this).val());
        });
        console.log("total : ", total);
        $('#total').html('<strong>' + total + ' €</strong>');
    }
}

/**
 * Calcule le prix différentiel de l'étape delta-1 -> delta
 * Par une requête d'itinéraire (Mappy)
 * et la met dans le chmap diffprice
 *
 * Hypothèses :
 * - Sans Plomb 95.
 * - voiture moyenne
 * - 4 places dans la voiture
 * todo : le récupérer du fichier de constantes JS des prix du carburant
 *
 * @param delta
 */
function computeDiffPriceForStop(delta) {

    if (typeof L !== 'undefined') {
        // Départ et arrivée
        var iti = [];
        for (var i = delta - 1; i <= delta; i++) {
            var stop = findStopFormElementByDelta(i);
            var lat = parseFloat(stop.find('input[id$=lat]').val());
            var lng = parseFloat(stop.find('input[id$=lng]').val());
            if (lat == 0 || lng == 0)
                return;
            iti.push(L.latLng(lat, lng));
        }

        // On affiche un spinner dans le champ diff price
        $('#app_trip_edit_pricediff_' + (delta -1)).addClass('loading');

        var options = {
            vehicle: L.Mappy.RouteModes.CAR, // PEDESTRIAN, BIKE, MOTORBIKE
            cost: "time", // or "length" or "price"
            gascost: 1.0,
            gas: "petrol", // or diesel, lpg
            nopass: 0, // 1 pour un trajet sans col
            notoll: 0, // 1 pour un trajet sans péage
            caravane: 0, // 1 pour un trajet avec caravane
            infotraffic: 0 // 1 pour un trajet avec trafic
        };

        L.Mappy.Services.route(iti, options,
            // Callback de succès
            function (results) {
                var stopRoadbook = results.routes.route;
                // arrête du  spinner
                $('#app_trip_edit_pricediff_' + (delta -1)).removeClass('loading');
                // Calcule et affiche le prix dans le champ
                var diffprice = priceFromRoadbook(stopRoadbook);
                $('#app_trip_edit_pricediff_' + (delta -1) ).val(diffprice);

                // Var globale ; on calcule quand tout est arrivé
                --ajaxCallsRemaining;
                if (ajaxCallsRemaining == 0) {
                    setPricesFromDiff();
                    setTotal();
                    console.log('Calcul des prix après appels auto');
                }
            },
            // Callback d'erreur
            function () {
                // Error during route calculation
                // arrête du  spinner
                $('#app_trip_edit_pricediff_' + (delta -1)).removeClass('loading');
                $('#app_trip_edit_pricediff_' + (delta -1) ).val('');
                console.log('Erreur de calcul de l itinéraire pour le prix');
                --ajaxCallsRemaining;
                if (ajaxCallsRemaining == 0) {
                    setPricesFromDiff();
                    setTotal();
                    console.log('Calcul des prix après appels auto');
                }
            }
        );
    }
}

/**
 * Calcule le prix (carburant + péages) d'une étape à partir
 * d'un roadbook Mappy.
 *
 * @param roadbook
 */
function priceFromRoadbook(roadbook, gasCost = 1.5) {
    var summary = roadbook.summary;

    // Péages
    var toll_total = 0;
    if (summary.tolls && summary.tolls.toll) {
        toll = summary.tolls.toll;
        if (toll.currency=="EUR")
            toll_total = toll_total+parseFloat(toll.amount);
    }

    // Carburant
    var gas = parseFloat(summary.gas); // en litres

    // Places dans la voiture
    var places = 4.0;

    return Math.round(((gas * gasCost) + toll_total) / (places-1) ) ;
}

/*=======  Temps des étapes  ================*/

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

   var currentRoadbook = JSON.parse($('#app_trip_edit_mappyRoadbook').val());
    // console.log(currentRoadbook);

    // Calcul auto du prix
    var nbOfStops = $('div[id^=app_trip_edit_stops_]').length;
    console.log('Nb stops: ', nbOfStops);

    // Soit tout est rempli, soit rien n'est rempli ; pas de demi-mesure car trop compliqué.
    // On vérifie donc uniquement le dernier stop
    if (findStopFormElementByDelta(nbOfStops-1).find('input[id$=price]').val() == '') {
        // Pas d'étapes intérmédiaires
        if (nbOfStops == 2) {
            var price = priceFromRoadbook(currentRoadbook, 1.5);
            $('#app_trip_edit_pricediff_0').val(price);
            setPricesFromDiff();
            setTotal();
        }
        // étapes intermédiaires
        else {
            ajaxCallsRemaining = nbOfStops - 1;
            for (var delta=1; delta < nbOfStops; delta++) {
                computeDiffPriceForStop(delta);
            }
        }
    }
    // Sinon on calcule juste le total
    else {
        setTotal();
    }


    // Empêche l'utilisateur de rentrer manuellement autre chose que des
    // entiers positifs dans les champs prix différentiels
    $('#stop_prices input').blur( function(){
        var v = parseInt( $(this).val(), 10) ;
        if (isNaN(v) || v < 0 ){
            $(this).val(0);
            setPricesFromDiff();
            setTotal();
        }
    });

    // Actualise les champs cachés de prix quand les prix différentiels
    // entre étapes sont modifiés manuellement
    $('#stop_prices input').change( function() {
        setPricesFromDiff();
        setTotal();
    });


});
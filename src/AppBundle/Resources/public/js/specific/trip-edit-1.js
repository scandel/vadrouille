/*
 * JS for the "edit trip" page
 */

// Récupère le div qui contient la collection de tags
var collectionHolder = $('ul.stops');
// Indice pour numéroter les nouvelles étapes
var iter = collectionHolder.find('.deletable').length+2;

// ajoute un lien « add a tag »
var $addStopLink = $('<button id="add_stop" class="btn btn-default"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span> Ajouter une étape</button>');

// Array of stops positions (defined in trip-map.js)
// 0 = departure, 1 = arrival, 2+ = intermediate stops
for (var i=0; i < maxmax; i++){
    pos['app_trip_edit_stops_' + i] = new Object;
    pos['app_trip_edit_stops_' + i].set = false;
}

/*  ==== Adding New stops ======  */

function addStopForm(collectionHolder) {
    // Récupère l'élément ayant l'attribut data-prototype comme expliqué plus tôt
    var prototype = collectionHolder.attr('data-prototype');

    // Remplace '__name__' dans le HTML du prototype par un nombre basé sur
    // la longueur de la collection courante
    var newForm = prototype.replace(/__name__/g, iter++);

    // Affiche le formulaire dans la page dans un li, avant le lien "ajouter un tag"
    var $newFormLi = $('<li class="deletable"></li>').append(newForm);
    collectionHolder.append($newFormLi);

    // ajoute un lien de suppression au nouveau formulaire
    addStopFormDeleteLink($newFormLi);

    // Et un bouton pour drag n dropper l'élément
    addStopFormMoveButton($newFormLi);

    // ajoute l'autocompletion dans le nouveau formulaire
    $newFormLi.find('.city-autocomplete').each(function() {
        var input_name, input_id ;
        input_name = $(this).attr('id') ;
        input_item = input_name.replace('_city_name', '');
        input_id = input_name.replace('_city_name', '_city_id');
        $(this).autocomplete({
            //source: AJAX_WRAP+'?name=city_complete',
            source: function( request, response ) {
                var firstLetters = request.term;
                if ( firstLetters in cache ) {
                    response( cache[ firstLetters ] );
                    return;
                }

                $.getJSON( '/app_dev.php/ville/completer/' + firstLetters, null, function( data, status, xhr ) {
                    cache[ firstLetters ] = data;
                    response( data );
                });
            },
            minLength: 1,

            select: function( event, ui ) {
                event.preventDefault();
                console.log("Selection ", input_item);
                console.log(ui.item);
                if (!$('#'+input_id).val() || $('#'+input_id).val() != ui.item.id) {
                    UpdateCity(input_item, ui.item);
                }
            },

            change: function( event, ui ) {
                console.log("Changement ", input_item);
                if ( $(this).val().trim() == '' ) {
                    $.each(['city_id', /*'city_details',*/ 'place', 'lat', 'lng'], function (index, what) {
                        $('#' + input_item + '_' + what).val('');
                    });
                }
            },

        }).autocomplete( "instance" )._renderItem = function (ul, item) {
            // Style menu items with flags
            return $("<li>")
                .append('<a><img src="/bundles/app/img/flags/' + item.country+ '.png"> ' + item.name + ' (' + item.postcode + ')</a>')
                .appendTo(ul.addClass('autocomplete-city-row'));
        };
    });

    // Initialise le timepicker
    timePickerInit($newFormLi.find('.timepicker').first());

}

function addStopFormDeleteLink($stopFormLi) {
    var $removeForm = $('<button class="remove-button btn btn-xs btn-link" title="Supprimer cette étape" data-toggle="tooltip" data-placement="top"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>');
    $stopFormLi.children().first().append($removeForm);
    $removeForm.tooltip();
    var item = $stopFormLi.children().first().attr('id');

    $removeForm.on('click', function(e) {
        // empêche le lien de créer un « # » dans l'URL
        e.preventDefault();

        // supprime l'élément li pour le formulaire de tag
        $stopFormLi.remove();

        // recalcule les delta
        orderStops(collectionHolder);

        // Reactive le bouton "ajouter une étape" (si il était désactivé)
        $('#add_stop').prop('disabled', false);

        // supprime le marker de l'étape
        MapRemoveMarker(item);

        // Recalcule l'itiéraire
        if ((pos['app_trip_edit_stops_0'].set == true) && (pos['app_trip_edit_stops_1'].set == true)) {
            Itinerary(); // Calcule le trajet, centre la carte sur le trajet, écrit les infos trajet
        }

    });
}

function addStopFormMoveButton($stopFormLi) {
    // doit être un lien et pas un bouton, pour pouvoir être une poignée (handle)
    var $moveForm = $('<a class="move-button btn btn-xs btn-link" ' +
        'title="Cliquez et maintenez appuyé pour réordonner les étapes" data-toggle="tooltip" data-placement="top">' +
        '<span class="glyphicon glyphicon-move" aria-hidden="true"></span></a>');
    $stopFormLi.children().first().append($moveForm);
    $moveForm.tooltip();

    $moveForm.hover(
        // in
        function() {
             $( this ).parent().addClass('move-hover');
        },
        // out
        function() {
            $( this ).parent().removeClass('move-hover');
        }
    );
    $moveForm.mousedown(function() {
        // cache le tooltip sinon il se positionne mal lors du drag and drop
        $( this ).tooltip('hide');
    });
    $moveForm.mouseup(function() {
        // Enlève la classe jaune
        $( this ).parent().removeClass('move-hover');
    });

    $moveForm.on('click', function(e) {
        // empêche le lien de créer un « # » dans l'URL
        e.preventDefault();
    });
}

/**
 * Update delta hidden field, based on order created by the user when he sorts
 */
function orderStops(collectionHolder) {
    var delta = 1;
    console.log("orderStops");
    collectionHolder.find('.deletable').each(function() {
        console.log($(this).find("[id$='_city_name']").val(), delta);
        $(this).find("[id$='_delta']").val(delta);
        delta++;
    });
    $('#app_trip_edit_stops_1_delta').val(delta);
}

/*=======  Fonctions Mappy  ================*/

/**
 * Calcule le trajet, affiche l'itinéraire sur la carte,
 * centre sur l'itinéraire, et affiche les infos trajet
 * Calcule les temps de parcours (Aller-Retour : met les mêmes temps)
 *
 */
function Itinerary() {

    // On nettoie
    ClearPaths();
    if ($('#map-error') != null) {
        $('#map-error').remove();
    }

    // Il faut que le départ et l'arrivée soient définis
    if (!(pos['app_trip_edit_stops_0'].set && pos['app_trip_edit_stops_1'].set)) {
        return;
    }

    // On affiche le spinner
    $("#spinner").show();

    // Calcul itinéraire : ordre en fonction des deltas
    var iti = [];
    // Départ
    iti[0] = pos['app_trip_edit_stops_0'].center;
    for (i = 2; i < maxmax ; i++) {
        //console.log('i=' + i + ', set : '+ pos['app_trip_edit_stops_'+i].set + ' lat: '+
        //    $('#app_trip_edit_stops_'+i+'_lat').val() + ' lng: ' + $('#app_trip_edit_stops_'+i+'_lng').val());
        if ((pos['app_trip_edit_stops_'+i].set)
            && !isNaN(parseFloat($('#app_trip_edit_stops_'+i+'_lat').val()))
            && !isNaN(parseFloat($('#app_trip_edit_stops_'+i+'_lng').val())))
            iti[$('#app_trip_edit_stops_'+i+'_delta').val()] = pos['app_trip_edit_stops_'+i].center ;
    }
    iti[$('#app_trip_edit_stops_1_delta').val()] = pos['app_trip_edit_stops_1'].center ;

    // Tri sur les deltas, suppression des indices vides
    var sortedIti = [];
    for (i = 0; i< maxmax; i++) {
        if (typeof iti[i] !== 'undefined') {
            sortedIti.push(iti[i]);
        }
    }

    console.log(sortedIti);

    var options = {
        vehicle: L.Mappy.RouteModes.CAR, // PEDESTRIAN, BIKE, MOTORBIKE
        cost: "length", // or "time" or "price"
        gascost: 1.0,
        gas: "petrol", // or diesel, lpg
        nopass: 0, // 1 pour un trajet sans col
        notoll: 0, // 1 pour un trajet sans péage
        caravane: 0, // 1 pour un trajet avec caravane
        infotraffic: 0 // 1 pour un trajet avec trafic
    };

    L.Mappy.Services.route(sortedIti,
        options,
        // Callback de succès
        function(results) {

            // On enregistre le parcours
            currentRoadbook = results.routes.route;

            // on re-nettoie pour si il y a eu deux lancements rapprochés
            ClearPaths();

            polyline = L.polyline(results.routes.route["polyline-definition"].polyline).addTo(map);
            // zoom the map to the polyline
            map.fitBounds(polyline.getBounds());

            // Temps et distance actualisés
            console.log("Route : ",results.routes.route);


            travel_time = results.routes.route.summary['time'];
            var time = sprintf("%dh%02d",Math.floor(travel_time/3600),Math.floor((travel_time/60)%60));
            $("#time").text(time);

            var mylength = Math.floor(results.routes.route.summary['length']/1000) + "km";
            $("#distance").text(mylength);

            $("#spinner").hide();

            // Store roadbook
            $('#app_trip_edit_mappyRoadbook').val(JSON.stringify(currentRoadbook));

        },
        // Callback d'erreur
        function() {
            // Error during route calculation
            map.setView(france.center , france.zoom );
            $("#spinner").hide();

            // Ajout message d'erreur direct sur la carte
            var error = $('<div>').attr({
                'id' : 'map-error',
                'class' : 'map-error alert alert-danger',
            }).text("Erreur de calcul d'itinéraire !");
            $('#map').append(error);
        }
    );
}

/**
 * Efface les layers de type "_path"
 */
function ClearPaths() {
    for(i in map._layers) {
        if(map._layers[i]._path != undefined) {
            try {
                map.removeLayer(map._layers[i]);
            }
            catch(e) {
                // debug("problem with " + e + map._layers[i]);
            }
        }
    }
}

/*========= recherche, vérification, update villes ============*/

/**
 * Quand une ville est choisie (autocompleter ou search)
 * Fait toutes les opérations nécessaires :
 *
 * @param item : ex : 'app_trip_edit_stops_0'
 * @param cityid
 * @param city_name
 * @param city_details (postcode)
 *
 */
function UpdateCity(item, uiItem) {

    // console.log("Update City");

    // Le nom
    $('#'+item+'_city_name').val(uiItem.name);

    // L'id
    $('#'+item+'_city_id').val(uiItem.id);

    // Coordonnées
    $('#'+item+'_lat').val(uiItem.lat);
    $('#'+item+'_lng').val(uiItem.lng);

    // Les détails (code postal, pays) pour la géoloc
    $('#'+item+'_city_details').val(uiItem.postcode);

    // Effacer le pt de RV (placeholder "N'importe où")
    $('#'+item+'_place').val('');

    // Placer le marqueur
    if ( !(isNaN(uiItem.lat)) && uiItem.lat!=0) {
        OnPlaceUpdate(item, -1, true);
    }
}


/**
 * Place un marqueur sur le lieu ;
 * si zoom >= 0, centre sur le lieu avec le niveau de zoom donné
 * si iti = true, calcule (éventuellement) l'itinéraire
 * (les deux options ne devraient pas être utilisées ensemble)
 * à executer lors de l'update d'un lieu
 *
 * @param item
 * @param zoom
 * @param iti
 */
function OnPlaceUpdate(item,zoom,iti) {

    var lat = parseFloat($('#'+item+'_lat').val());
    var lng = parseFloat($('#'+item+'_lng').val());

    // place un marker et calcule éventuellement l'itinéraire
    if ( !(isNaN(lat)) && !(isNaN(lng))) {
        if (typeof L !== 'undefined') {
            // Est-ce que l'on change de position ?
            var change = true;
            if (pos[item].set) {
                var distance = pos[item].center.distanceTo(L.latLng(lat,lng));
                if (distance<10) // 10 m : on ne change pas
                    change = false ;
            }


            pos[item].center = L.latLng(lat, lng);

            if (change) {
                var type = (item == 'app_trip_edit_stops_0') ? 'dep' :
                    (item == 'app_trip_edit_stops_1') ? 'arr' : null ;
                MapPutMarker(item, type); // Met un marqueur sur le point de RV; ne change pas l'affichage de la carte
                // si les lieux de d?part et d'arriv?e sont connus tous deux, calcule et affiche l'itin?raire
                if (iti && (pos['app_trip_edit_stops_0'].set == true) && (pos['app_trip_edit_stops_1'].set == true)) {
                    Itinerary(); // Calcule le trajet, centre la carte sur le trajet, écrit les infos trajet
                }
            }
            if (zoom >= 0) {
                map.setView([lat, lng], zoom);
            }
        }
    }
}



jQuery(document).ready(function() {

    // Active les tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // ajoute un lien de suppression à tous les éléments li de
    // formulaires de tag existants
    collectionHolder.find('.deletable').each(function() {
        addStopFormDeleteLink($(this));
        addStopFormMoveButton($(this));
    });

    // ajoute l'ancre « ajouter un tag » et li à la balise ul
    collectionHolder.after($addStopLink);

    $addStopLink.on('click', function(e) {
        // empêche le lien de créer un « # » dans l'URL
        e.preventDefault();

        // ajoute un nouveau formulaire tag (voir le prochain bloc de code)
        addStopForm(collectionHolder);

        // Recalcule les deltas
        orderStops(collectionHolder);

        // Si on est au max d'éléments, disable l'élément
        if (collectionHolder.find('.deletable').length >= max_stops) {
            $('#add_stop').prop('disabled', 'disabled');
        }
    });

    // Rend les élements étapes triables (JQuery UI Sortable)
    $('#sortable').sortable({
        containment: "parent",
        handle: ".move-button",
        stop: function( event, ui ) {
            // Ecrit les delta en fonction de la position
            orderStops(collectionHolder);
            // Recalcule l'itinéraire
            Itinerary();
        }
    });

   //---- Montre/cache des champs en fonction de aller simple/retour, trajet unique/régulier

    // Au chargement : cacher/montrer ce qu'il faut
    if ( $('input[id=app_trip_edit_regular_0]').prop('checked') ) {
        $('.if_unique').show();
        $('.if_regular').hide();
    }
    else {
        $('.if_unique').hide();
        $('.if_regular').show();
    }

    $("input[id=app_trip_edit_regular_0]").click( function(e) {
        $('.if_regular').hide();
        $('.if_unique').show();
    });
    $("input[id=app_trip_edit_regular_1]").click( function(e) {
        $('.if_regular').show();
        $('.if_unique').hide();
    });


    //---- Init Map ----

    if ($('#app_trip_edit_mappyRoadbook').val() != '') {
        currentRoadbook = JSON.parse($('#app_trip_edit_mappyRoadbook').val());
        MapInit(currentRoadbook);
    }
    else {
        MapInit();
    }

    //---- Init markers : Si les villes sont déjà remplies  ---
    for (var i=0; i < maxmax; i++){
        var item = 'app_trip_edit_stops_' + i;
        var cityid = parseInt($('#'+item+'_city_id').val());
        if ( !(isNaN(cityid)) && cityid!=0) {
            OnPlaceUpdate(item,-1,false); // don't draw itinerary
        }
    }

    //---- Liens de centrage de la carte ----
    $.each(['dep','arr'],function(index, item) {
        $('#'+item+'_center').click(function(e) {
            e.preventDefault();
            MapCenter('app_trip_edit_stops_'+index);
        });
    });
    $('#trip_center').click(function(e) {
        e.preventDefault();
        if (travel_time!=0) MapTripCenter();
        else alert('Vous devez d\'abord définir complètement votre trajet');
    });

    //---- On "City" Change ----

    // Change select behavior
    $('.city-autocomplete').on("autocompleteselect", function (event, ui) {
        var input_item, input_id ;
        input_item = $(this).attr('id').replace('_city_name', '');
        input_id = $(this).attr('id').replace('_city_name', '_city_id');
        console.log("Selection ", input_item);
        console.log(ui.item);
        if (!$('#'+input_id).val() || $('#'+input_id).val() != ui.item.id) {
            UpdateCity(input_item, ui.item);
        }
    });

    // Change "change" behavior
    $('.city-autocomplete').on("autocompletechange", function () {
        var input_item ;
        input_item = $(this).attr('id').replace('_city_name', '');
        console.log("Changement ", input_item);
        if ( $(this).val().trim() == '' ){
            $.each(['city_id','city_details', 'place','lat','lng'], function(index, what) {
                $('#'+input_item+'_'+what).val('');
            });
        }
    });

    // Quand l'alerte "mode invité" est fermée, envoie un Ajax à une action
    // qui écrit dans la session d'accepter le mode invité.
    $('[data-dismiss="alert"]').on('click', function() {
        //console.log("Notice closed");
        $.ajax({
            url: "/covoiturage/mode-invite-ok"
        });
    });

});
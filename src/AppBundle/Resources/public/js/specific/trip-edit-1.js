/*
 * JS for the "edit trip" page
 */

// Nb max d'étapes
var max_stops = 5
var maxmax = 200;

// Récupère le div qui contient la collection de tags
var collectionHolder = $('ul.stops');
// Indice pour numéroter les nouvelles étapes
var iter = collectionHolder.find('.deletable').length+2;

// ajoute un lien « add a tag »
var $addStopLink = $('<div class="col-sm-8 col-sm-push-4"><button id="add_stop" class="btn btn-default"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Ajouter une étape</button></div>');
var $newLinkLi = $('<li></li>').append($addStopLink);

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

/*  ==== Adding New stops ======  */

function addStopForm(collectionHolder, $newLinkLi) {
    // Récupère l'élément ayant l'attribut data-prototype comme expliqué plus tôt
    var prototype = collectionHolder.attr('data-prototype');

    // Remplace '__name__' dans le HTML du prototype par un nombre basé sur
    // la longueur de la collection courante
    var newForm = prototype.replace(/__name__/g, iter++);

    // Affiche le formulaire dans la page dans un li, avant le lien "ajouter un tag"
    var $newFormLi = $('<li class="deletable"></li>').append(newForm);
    $newLinkLi.before($newFormLi);

    // ajoute un lien de suppression au nouveau formulaire
    addStopFormDeleteLink($newFormLi);

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
    var $removeFormA = $('<div class="row"><div class="col-sm-8 col-sm-push-4 clearfix"><button class="btn btn-link btn-sm"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Supprimer cette étape</button></div></div>');
    $stopFormLi.append($removeFormA);
    var item = $stopFormLi.children().first().attr('id');

    $removeFormA.on('click', function(e) {
        // empêche le lien de créer un « # » dans l'URL
        e.preventDefault();

        // supprime l'élément li pour le formulaire de tag
        $stopFormLi.remove();

        // supprime le marker de l'étape
        MapRemoveMarker(item);

        if (collectionHolder.find('.deletable').length < max_stops) {
            $('#add_stop').attr('disabled', false);
        }

        // Recalcule l'itiéraire
        if ((pos['app_trip_edit_stops_0'].set == true) && (pos['app_trip_edit_stops_1'].set == true)) {
            Itinerary(); // Calcule le trajet, centre la carte sur le trajet, écrit les infos trajet
        }

    });
}

/*=======  Fonctions Mappy pour le départ et l'arrivée ================*/

/**
 * Initialisation carte
 *
 */
function MapInit() {

    if (typeof L !== 'undefined') {
        // Création de la carte
        map = new L.Mappy.Map('map', {
            center: france.center,
            zoom: france.zoom,
            layersControl: false,
            logoControl: {
                dir: "/bundles/app/mappy/images/"
            }
        });

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
 * @param stop : 'app_trip_edit_stops_x'
 */
function MapPutMarker(stop) {
    if (typeof L !== 'undefined') {
        // console.log("Marker ",stop,pos[stop]);
        switch (stop) {
            case 'app_trip_edit_stops_0':
                var icon = new GreenIcon();
                var tooltip = 'Cliquez pour centrer la carte sur le point de départ.';
                break;
            case 'app_trip_edit_stops_1':
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
 * @param stop  : 'app_trip_edit_stops_x'
 */
function MapRemoveMarker(stop) {
    markerLayer.removeLayer(markers[stop]);
    markers[stop]=null;
    pos[stop].set = false;
}


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

    // On affiche le spinner
    $("#spinner").show();

    // Calcul itinéraire
    var iti = [pos['app_trip_edit_stops_0'].center];
    for (i = 2; i < maxmax ; i++)
    {
        //console.log('i=' + i + ', set : '+ pos['app_trip_edit_stops_'+i].set + ' lat: '+
        //    $('#app_trip_edit_stops_'+i+'_lat').val() + ' lng: ' + $('#app_trip_edit_stops_'+i+'_lng').val());
        if ((pos['app_trip_edit_stops_'+i].set)
            && !isNaN(parseFloat($('#app_trip_edit_stops_'+i+'_lat').val()))
            && !isNaN(parseFloat($('#app_trip_edit_stops_'+i+'_lng').val())))
            iti.push(pos['app_trip_edit_stops_'+i].center) ;
    }
    iti.push(pos['app_trip_edit_stops_1'].center) ;

    console.log(iti);

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

    L.Mappy.Services.route(iti,
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

/**
 * Centre la carte sur le point de départ, arrivée, ou étape
 * @param item  : 'dep', 'arr', 'stepX'
 */
function MapCenter(item) {
    console.log("MapCenter ", item);
    if (!pos[item].set) {
        switch (item)
        {
            case 'dep': alert('Vous devez d\'abord définir votre ville et lieu de départ');  break;
            case 'arr':  alert('Vous devez d\'abord définir votre ville et lieu d\'arrivée');  break;
            case 'step1':
            case 'step2':
            case 'step3':
            case 'step4':
            case 'step5':  alert('Vous devez d\'abord définir votre ville et lieu d\'étape');  break;
            default:
        }
    }
    else
        map.setView(pos[item].center, 9);
}


/**
 * Centre la carte sur le trajet global
 */
function MapTripCenter() {
    map.fitBounds(polyline.getBounds());
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

    // Change le nom dans les temps de parcours
    // UpdateStepNamesForTimes();
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
                MapPutMarker(item); // Met un marqueur sur le point de RV; ne change pas l'affichage de la carte
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

    // ajoute un lien de suppression à tous les éléments li de
    // formulaires de tag existants
    collectionHolder.find('.deletable').each(function() {
        addStopFormDeleteLink($(this));
    });

    // ajoute l'ancre « ajouter un tag » et li à la balise ul
    collectionHolder.append($newLinkLi);

    $addStopLink.on('click', function(e) {
        // empêche le lien de créer un « # » dans l'URL
        e.preventDefault();

        // ajoute un nouveau formulaire tag (voir le prochain bloc de code)
        addStopForm(collectionHolder, $newLinkLi);

        // Si on est au max d'éléments, disable l'élément
        if (collectionHolder.find('.deletable').length >= max_stops) {
            $('#add_stop').attr('disabled', 'disabled');
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
    MapInit();

    //---- Init markers : Si les villes sont déjà remplies  ---
    for (var i=0; i < 200; i++){
        var item = 'app_trip_edit_stops_' + i;
        var cityid = parseInt($('#'+item+'_city_id').val());
        if ( !(isNaN(cityid)) && cityid!=0) {
            OnPlaceUpdate(item,-1,true);
        }
    };

    //---- Liens de centrage de la carte ----
    $.each(['stops_0','stops_1'],function(index, item) {
        $('#'+item+'_center').click(function(e) {
            e.preventDefault();
            MapCenter('app_trip_edit_'+item);
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
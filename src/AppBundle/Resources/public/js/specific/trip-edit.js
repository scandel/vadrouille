/*
 * JS for the "edit trip" page
 */

// Nb max d'étapes
var max_stops = 5

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

france = new Object;
france.center = L.latLng(46.40, 2.60); // (3.082418, 45.777168);
france.zoom = 3;

/*
var pos= new Array();
$.each(['dep','arr','step1','step2','step3','step4','step5'],function(index, item) {
    pos[item] = new Object;
    pos[item].set = false;
});
*/

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
        input_id = input_name.replace('name', 'id');
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
                $(this).val( ui.item.name );
                // Set value for city id
                $("#"+input_id).val(ui.item.id);
                // console.log(cache);
                //console.log( ui.item ?
                //"For: " + input_name + ", Selected: " + ui.item.name + " aka " + ui.item.id :
                //"Nothing selected, input was " + this.value );
            },

            change: function( event, ui ) {
                //console.log(ui);
                // If value isn't selected from autocomplete, erase city id value
                if (ui.item) {
                    $("#" + input_id).val(ui.item.id);
                }
                else {
                    $("#" + input_id).val('');
                }
            },

        }).autocomplete( "instance" )._renderItem = function (ul, item) {
            // Style menu items with flags
            return $("<li>")
                .append('<a><img src="/bundles/app/img/flags/' + item.country+ '.png"> ' + item.name + ' (' + item.postcode + ')</a>')
                .appendTo(ul.addClass('autocomplete-city-row'));
        };
    });
}

function addStopFormDeleteLink($stopFormLi) {
    var $removeFormA = $('<div class="row"><div class="col-sm-8 col-sm-push-4 clearfix"><button class="btn btn-link btn-sm"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Supprimer cette étape</button></div></div>');
    $stopFormLi.append($removeFormA);

    $removeFormA.on('click', function(e) {
        // empêche le lien de créer un « # » dans l'URL
        e.preventDefault();

        // supprime l'élément li pour le formulaire de tag
        $stopFormLi.remove();

        if (collectionHolder.find('.deletable').length < max_stops) {
            $('#add_stop').attr('disabled', false);
        }

    });
}

/*=======  Fonctions Mappy pour le départ et l'arrivée ================*/

/**
 * Initialisation carte
 *
 */
function MapInitStep1() {

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
    markerLayer =  L.layerGroup().addTo(map);
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

    //---- Init Map ----
    MapInitStep1();

});
<?php

namespace AppBundle\Entity;

use Elastica\Script;
use FOS\ElasticaBundle\Repository;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\FunctionScore;


class CityRepository extends Repository
{
    public function searchByNameOrPostcode($firstLetters, $limit = 10)
    {
        // Main query with the sorting
        $query = new Query();
        // $query->addSort(array('note' => array('order' => 'desc')));

        // Bool query to combine name, and postcode query, and french boost.
        $boolQuery = new BoolQuery();

        // Search of type "phrase" against names (all terms in order)
        $nameQuery = new Match();
        $nameQuery->setFieldQuery('name',$firstLetters);
        $nameQuery->setFieldParam('name','type','phrase');
        $boolQuery->addShould($nameQuery);

        // And normal for postcodes (but only if more than 3 char in the query string)
        if (strlen($firstLetters) > 2) {
            $pcQuery = new Match();
            $pcQuery->setFieldQuery('postcode',$firstLetters);
            $boolQuery->addShould($pcQuery);
        }

        // Add function score to take note into account
        $functionScore = new FunctionScore();
        $functionScore->setQuery($boolQuery);

        // Ca marche, mais ça ne fait que classer par note
       // $functionScore->addFieldValueFactorFunction('note');

        $script = new Script(
            "country = doc['country.code'].value;
            note = doc['note'].value;
            if (country == here) {
                return note * turbo / max
             };
             return note / max;", // on peut aussi multiplier par _score, mais les résultats sont parfois bizzares
            array(
                "here" => "fr",   // Minuscules !!! très très important !!!
                "turbo" => 20,
                "max" => 3000000
            ));
        $functionScore->addScriptScoreFunction($script);
        $functionScore->setBoostMode("replace");

        $query->setQuery($functionScore);

        // exécution de la requête, limitée aux 10 premiers résultats
        $cities = $this->find($query, $limit);
        return $cities;
    }
}
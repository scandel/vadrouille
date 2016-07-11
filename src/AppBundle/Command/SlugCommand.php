<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\CityName;
use AppBundle\Entity\City;
use Cocur\Slugify\Slugify;

/**
 * Create slugs (not unique) for CitiesNames
 * Uses Cocur Slugify and per-language transliteration
 *
 * Class SlugCommand
 * @package AppBundle\Command
 */
class SlugCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('dbprepare:slug')
            ->setDescription('Slug cities names')
            ->addArgument(
                'languages',
                InputArgument::IS_ARRAY,
                'Which languages do you want to slug (separate multiple languages with a space)?'
            );
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        // Slugify rulesets for european languages
        $codefiles = array(
            'at' => 'austrian', // Autrichien
            'bg' => 'bulgarian', // bulgare
            'bs' => null, // Bosniaque
            'ca' => null, // Andorre - Catalan
            'cs' => 'czech', // tchèque
            'da' => null, // danois
            'de' => 'german', // allemand
            'el' => 'greek', // Grec (moderne)
            'en' => null,
            'es' => null, // espagnol
            'et' => null, // estonien
            'fi' => 'finnish', // finnois
            'fr' => null,   // Français
            'ga' => null,   // Irlandais
            'hu' => null,   // Hongrois
            'is' => null,   // Islandais
            'it' => null,   // Italien
            'lb' => null,   // Luxembourgeaois
            'lt' => null,   // Letton
            'mt' => null,   // Maltais
            'nl' => null,   // Neerlandais
            'no' => 'norwegian', // Norvégien
            'pl' => 'polish', // polonais
            'pt' => null, // Portuguais
            'ro' => null,   // Roumain
            'sk' => null,   // slovaque
            'sl' => null,   // Slovène
            'sr' => '',     // Serbe
            'ru' => 'russian', // Russe
            'sv' => 'swedish',  // Suedois,
            '' => null  // For cities without defined language (shouldn't be any)
        );

        // Languages to slugify
        if (!$languages = array_intersect($input->getArgument('languages'), array_keys($codefiles))) {
            $languages = array_keys($codefiles);
        }

        // Repository of City Names and manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $this->getContainer()->get('doctrine')->getRepository('AppBundle:CityName');

        // Boucle sur languages
        foreach ($languages as $language) {

            // new slugify each time, to delete previous rulesets
            $slugify = new Slugify();

            // Add specific ruleset if needed
            if ($codefiles[$language]) {
                $filename =
                    __DIR__ .
                    DIRECTORY_SEPARATOR . '..' .
                    DIRECTORY_SEPARATOR . 'Resources/slugifyRulesets/' . $codefiles[$language] . '.json';
                $fileContent = file_get_contents($filename);
                $ruleset = json_decode($fileContent, true);

                $slugify->addRuleset($language, $ruleset);
                $slugify->activateRuleset($language);
            }

            // Get all city names by language
            $names = $repo->findByLanguage($language);

            foreach($names as $cityName) {
                $name = $cityName->getName();
                $slug = $slugify->slugify($name);

                $cityName->setSlugNotUnique($slug);

                $output->writeln($name . " --> " . $slug );
            }
            $em->flush();

            unset($slugify);
        }
    }
}
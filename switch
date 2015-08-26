#!/bin/bash

# Usage : switch <environnement> <version>
# www par défaut pour l'environnement 
# la version la plus récente par défaut pour la version (sinon le nom du repertoire dans versions)
# à exécuter en tant que root ou avoir les droits : 
# - créer répertoire 

# Chemin du projet où déployer 
projectpath='/home/web/magicmakers.fr/ftp/'
projectversions='versions/'
projectshared='shared/'

# Environnement choisi, www par defaut 
if [ $# -ge 1 ] 
then
    projectenv=$1
else
    projectenv='www'
fi
# Version sur laquelle basculer, la plus recente par défaut 
if [ $# -ge 2 ] 
then
    version=$2
else
	version=`ls -t1 $projectpath$projectversions | head -n1`
fi


echo $projectpath
echo $projectenv
echo $version

projectdir=$projectpath$projectversions$version
cd $projectdir

# Backup de la base, enable features, revert features 
if [ "$projectenv" == "www" ] 
then 
	drush "@magic.$projectenv" bam-backup
	echo "Database backup $projectenv done"
fi
echo "Enabling features ..."
drush "@magic.$projectenv" pm-enable `ls sites/all/modules/features/`
echo "Reverting features ..."
drush "@magic.$projectenv" features-revert-all

echo "Clearing cache ..."
drush "@magic.$projectenv" cc all

# Création du lien symbolique de l'environnement
cd $projectpath
if [ -e ./$projectenv ] && [ -L ./$projectenv ]
then 
	rm ./$projectenv
	ln -s $projectdir ./$projectenv
	echo "Symlink $projectenv created"
elif [ ! -e ./$projectenv ]
then 
	ln -s $projectdir ./$projectenv	
	echo "Symlink $projectenv created"
else 
	echo "ATTENTION : $projectenv already exists and is not a symlink, not deleting it !!! "
fi 

echo "... Done !!!" 

(**Script originally created in 2009/01/25** for a french website : [http://www.phpcs.com/codes/PHP5-MULTI-THREADING-ACCELERATION-TEMPS-TRAVAIL-SCRIPT_49077.aspx](http://www.phpcs.com/codes/PHP5-MULTI-THREADING-ACCELERATION-TEMPS-TRAVAIL-SCRIPT_49077.aspx "PHP CS"))

L'article rédigé par Martin Roest [http://www.ibuildings.com/blog/archives/1539-Boost-performance-with-parallel-processing.html](http://www.ibuildings.com/blog/archives/1539-Boost-performance-with-parallel-processing.html) nous montre à quel point il peut-être facile de jouer avec plusieurs processus en même temps, même en PHP.

J'ai personnellement été très séduit par la simplicité, mais je trouve que l'on pouvais encore faire plus simple : une petite classe :)

L'utilité d'un tel code se trouve sur des scripts de traitement, tel qu'un remaniement de base de donnée, une modifications sur plusieurs fichiers, etc. Par contre, il est important de savoir que la gestion des processus tel qu'il est utilisé ici ne fonctionne pas sous Windows, et qu'il n'est pas conseillé d'appeler ce script depuis un environnement type Apache.

En gros et pour faire simple, ce type de script s'utilise en ligne de commande (php-cli) et dans un environnement Unix, type cron.

Pour vous prouver l'efficacité de mes dires, voici un code bateau qui utilise 5 processus simultanés pour exécuter un script :

	<?php
	/**
	 * Voici un exemple de ce que cela pourrait donner :
	 * Dans un terminal sous linux, appelez le, et vous devriez voir la façon dont la méthode "doBigWork" est appelée (5 fois par 5 fois jusqu'à 12) (5, 5, 2)
	 */
	require_once ('ProcessManager.php');
	
	function doBigWork ($iWork) {
		echo 'Sleeping for Work N° '.$iWork."\n";
		sleep (20);
	}
	
	try {
		// We instanciate the ProcessManager with 5 childs
		$oPM = new ProcessManager (5);
	}
	catch (Exception $oE) {
		die ('Your configuration does not support "pcntl" methods.');
	}
	
	for ($i = 0; $i < 12; $i++) {
		// It could happen that the script couldn't fork a process. In that case, an Exception would be raised
		try {
			$oPM->fork ('doBigWork', array ($i));
		}
		catch (Exception $oE) {
			echo 'Using non forked way :'."\n";
			doBigWork ($i);
		}
	}

    // Using a clousures
    for ($i = 1; $i <= 12; $i++) {
        $oPM->fork (function ($iWork) {
            echo 'Sleeping for Work Number ' . $iWork . PHP_EOL;
            sleep($iWork);
        }, array ($i));
    }

    // Using objects
    for ($i = 1; $i <= 12; $i++) {
        $oPM->fork (array(new Example(), 'doSomething'), array ($i));
    }

	?>

Au final, sans utiliser plusieurs processus, ce code aurait pris 12*20 = 240 secondes.
Avec 5 enfants, le temps de travail est divisé par ... 5, soit 48 secondes ! Quand même !

Bien entendu, vous pouvez augmenter le nombre d'enfant, tout dépendra des ressources que consomment votre fonction de travail (histoire de ne pas tuer votre machine (je l'ai fait pendant les tests :p)).

Une dernière modification qui serait sympathique, c'est d'inclure les fonctions lambdas dans la méthode fork, au lieu de l'appel à une méthode en utilisant le [call_user_func_array](http://fr2.php.net/call_user_func_array).

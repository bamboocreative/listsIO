<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 5/2/14
 * Time: 7:34 PM
 */

namespace ListsIO\Bundle\ListBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateListRecommendationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
          ->setName('listsio:updatelistrecs')
          ->setDescription('Update all Lists "Next List" to reflect new activity.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $recommender = $this->getContainer()->get('listsio.recommender');
        $qb = $em->getRepository('ListsIO\Bundle\ListBundle\Entity\LIOList')->createQueryBuilder('l');
        $query = $qb->getQuery();
        $lists = $query->getResult();
        $i = 0;
        $found = 0;
        $not_found = 0;
        $this_one_found = false;
        foreach($lists as $list) {
            $i++;
            $recommended = $recommender->mostSimilarLists($list, 5);
            if (count($recommended)) {
                $nextList = array_shift($recommended);
                if (($nextNextList = $nextList->getNextList()) && ($list->getId() == $nextNextList->getId())) {
                    $nextList = array_shift($recommended);
                }
                if ($nextList) {
                    $found++;
                    $output->writeln("List '" . $nextList->getTitle() . "' recommended as next list for list '" . $list->getTitle() . "'.");
                    $list->setNextList($nextList);
                    $this_one_found = true;
                } else {
                    $this_one_found = false;
                }
            } else {
                $this_one_found = false;
            }

            if ( ! $this_one_found) {
                $not_found++;
                $output->writeln("NO LIST recommended as next list for list '" . $list->getTitle() . "'.");
                $list->setNextList(NULL);
            }
            $em->persist($list);
        }
        $output->writeln($i . " lists updated.");
        $output->writeln("A recommended next list was found for " . $found . " of " . $i . ".");
        $output->writeln("A recommended next list could not be found for " . $not_found . " of " . $i . ".");
        $em->flush();
    }


} 
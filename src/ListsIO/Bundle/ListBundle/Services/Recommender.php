<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 5/1/14
 * Time: 8:39 PM
 */

namespace ListsIO\Bundle\ListBundle\Services;

use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class Recommender
 * Every method in this class is really slow, iterating over every user and/or list in the DB multiple times.
 * Derived from "Programming Collective Intelligence" by Toby Segaran (O'Reilly Media).
 *
 *
 * @package ListsIO\Bundle\ListBundle\Services
 */
class Recommender {

    private $em;

    const PREF_SCORE_AUTHOR = 2; // Preference score for being the owner of a list.
    const PREF_SCORE_LIKE = 2; // Preference score for liking a list.
    const PREF_SCORE_VIEW = 1; // Preference score for viewing a list (per view).
    const DEFAULT_SIM_FUNC = 'similarityPearson';

    /**
     * Default Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get the $numLists most similar to the given lists:
     * - Ignore lists in $ignore
     * - Use the similarity function named in $similarityFunction
     *
     * @param LIOList $list
     * @param int $numLists
     * @param array $ignore
     * @param string $similarityFunction
     * @return array|null
     */
    public function mostSimilarLists(LIOList $list,
                                     $numLists = 1,
                                     array $ignore = array(),
                                     $similarityFunction = self::DEFAULT_SIM_FUNC)
    {
        $scores = array();
        $listPrefs = $this->preferencesByList($list);
        $prefs = array();
        $prefs[$list->getId()] = $listPrefs;
        $qb = $this->em->getRepository('ListsIO\Bundle\ListBundle\Entity\LIOList')->createQueryBuilder('l');
        $query = $qb->getQuery();
        $otherLists = $query->getResult();
        $scoredLists = array();
        // Cycle through lists generating similarity score between it and given list.
        foreach ($otherLists as $otherList) {
            // Only score lists that are:
            // 1. Not the given list.
            // 2. Not in the "ignore" list.
            // 3. Have a title.
            if ($otherList->getId() != $list->getId()
                && ! in_array($otherList, $ignore)
                && $otherList->getTitle()) {
                $otherListPrefs = $this->preferencesByList($otherList);
                $prefs[$otherList->getId()] = $otherListPrefs;
                if (method_exists($this, $similarityFunction)) {
                    $otherListScore = $this->$similarityFunction($prefs, $list, $otherList);
                } else {
                    $sim_func = self::DEFAULT_SIM_FUNC;
                    $otherListScore = $this-$sim_func($prefs, $list, $otherList);
                }
                if ($otherListScore >= -1) {
                    $scores[$otherList->getId()] = $otherListScore;
                    $scoredLists[$otherList->getId()] = array(
                        "list" => $otherList,
                        "score" => $otherListScore
                    );
                }
            }
        }

        if ( ! count($scores)) {
            return NULL;
        }

        $offset = count($scores) - 1 - $numLists;
        asort($scores);
        $result =  array_slice($scores, $offset, $numLists, true);
        return array_intersect_key($scoredLists, $result);
    }

    /**
     * Determine Pearson correlation coefficient for $entity1 and $entity2, using scores in $prefs.
     *
     * @param $prefs
     * @param $entity1
     * @param $entity2
     * @return float
     */
    public function similarityPearson($prefs, $entity1, $entity2)
    {
        $entity1Id = $entity1->getId();
        $entity2Id = $entity2->getId();

        $similarities = array();
        foreach($prefs[$entity1Id] as $id => $score) {
            if (array_key_exists($id, $prefs[$entity2Id])) {
                $similarities[] = $id;
            }
        }

        $n = count($similarities);
        if ( ! $n) {
            return floatval(-2);
        }

        // Sum scores, sum squares of scores, and sum of products of scores.
        $sum1 = 0;
        $sum2 = 0;
        $sumSq1 = 0;
        $sumSq2 = 0;
        $productSum = 0;
        foreach ($similarities as $id) {
            $pref1 = $prefs[$entity1Id][$id];
            $pref2 = $prefs[$entity2Id][$id];
            $sum1 += $pref1;
            $sum2 += $pref2;
            $sumSq1 += $pref1 * $pref1;
            $sumSq2 += $pref2 * $pref2;
            $productSum += $pref1 * $pref2;
        }

        // Calculate Pearson score
        $num = $productSum - $sum1 * $sum2 / $n;
        $den = sqrt(($sumSq1 - $sum1 * $sum1 / $n) * ($sumSq2 - $sum2 * $sum2 / $n));
        // Avoid division by zero
        if ($den == 0) {
            return floatval(0);
        }
        return $num/$den;
    }

    /**
     * Generate an array of user preferences (keyed by user ID) for a given List.
     *
     * @param LIOList $list
     * @return array
     */
    public function preferencesByList(LIOList $list)
    {
        $prefs = array();
        $prefs[$list->getUser()->getId()] = self::PREF_SCORE_AUTHOR;

        foreach($list->getListViews() as $listView) {
            // ListView user can be null.
            $user = $listView->getUser();
            if ( empty($user)) {
                $userId = $listView->getAnonymousIdentifier();
            } else {
                $userId = $user->getId();
            }
            if ( ! empty($userId)) {
                if (empty($prefs[$userId])) {
                    $prefs[$userId] = self::PREF_SCORE_VIEW;
                } else {
                    $prefs[$userId] += self::PREF_SCORE_VIEW;
                }
            }
        }

        foreach($list->getListLikes() as $listLike) {
            // ListLike always has a user.
            $userId = $listLike->getUser()->getId();
            if (empty( $prefs[$userId])) {
                $prefs[$userId] = self::PREF_SCORE_LIKE;
            } else {
                $prefs[$userId] += self::PREF_SCORE_LIKE;
            }
        }

        return $prefs;
    }

    /**
     * Generate an array of user preferences (keyed by List ID) for a given user.
     *
     * @param User $user
     * @return array
     */
    public function preferencesByUser(User $user) {
        $prefs = array();
        foreach($user->getLists() as $list) {
            $listId = $list->getId();
            $prefs[$listId] = self::PREF_SCORE_AUTHOR;
        }

        foreach($user->getListViews() as $view) {
            $list = $view->getList();
            $listId = $list->getId();
            if (empty($prefs[$listId])) {
                $prefs[$listId] = self::PREF_SCORE_VIEW;
            } else {
                $prefs[$listId] += self::PREF_SCORE_VIEW;
            }
        }

        foreach($user->getListLikes() as $like) {
            $list = $like->getList();
            $listId = $list->getId();
            // You have to view a list to like it, so we can just add here.
            $prefs[$listId] += self::PREF_SCORE_LIKE;
        }
        return $prefs;
    }

} 
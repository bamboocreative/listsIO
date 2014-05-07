<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 5/6/14
 * Time: 9:09 AM
 */

namespace ListsIO\Bundle\ListBundle\Tests\Services;

use ListsIO\Bundle\ListBundle\Entity\LIOList;
use ListsIO\Bundle\ListBundle\Services\Recommender;
use ListsIO\Bundle\UserBundle\Entity\User;
use ListsIO\Utilities\Testing\DoctrineWebTestCase;


class RecommenderTest extends DoctrineWebTestCase {

    /** @var  $recommender Recommender */
    private $recommender;

    /** @var  $list LIOList */
    private $list;

    /** @var  $user1 User */
    private $user1;

    /** @var  $user2 User */
    private $user2;

    /** @var  $user3 User */
    private $user3;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->list = static::$entityManager->getRepository('ListsIOListBundle:LIOLIst')
            ->find(1);
        $this->recommender = new Recommender(static::$entityManager);
        $this->user1 = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(1);
        $this->user2 = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(2);
        $this->user3 = static::$entityManager->getRepository('ListsIOUserBundle:User')
            ->find(3);
    }

    public function testPreferencesByList() {
        $listPrefs = $this->recommender->preferencesByList($this->list);
        $user1Expected = Recommender::PREF_SCORE_AUTHOR;
        $user2Expected = Recommender::PREF_SCORE_VIEW + Recommender::PREF_SCORE_LIKE;
        $user3Expected = Recommender::PREF_SCORE_VIEW + Recommender::PREF_SCORE_LIKE;
        $this->assertEquals($listPrefs[1], $user1Expected);
        $this->assertEquals($listPrefs[2], $user2Expected);
        $this->assertEquals($listPrefs[3], $user3Expected);
    }

    public function testPreferencesByUser() {
        $user1Prefs = $this->recommender->preferencesByUser($this->user1);
        $user2Prefs = $this->recommender->preferencesByUser($this->user2);
        $user3Prefs = $this->recommender->preferencesByUser($this->user3);
        $this->assertEquals($user1Prefs[1], Recommender::PREF_SCORE_AUTHOR);
        $this->assertEquals($user2Prefs[1], Recommender::PREF_SCORE_LIKE + Recommender::PREF_SCORE_VIEW);
        $this->assertEquals($user3Prefs[1], Recommender::PREF_SCORE_LIKE + Recommender::PREF_SCORE_VIEW);
    }

    public function testSimilarityPearson()
    {
        $prefs = array();
        $prefs[1] = $this->recommender->preferencesByUser($this->user1);
        $prefs[2] = $this->recommender->preferencesByUser($this->user2);
        $prefs[3] = $this->recommender->preferencesByUser($this->user3);
        // User 1 and 2 should have some similarity.
        $this->assertGreaterThan(-1, $this->recommender->similarityPearson($prefs, $this->user1, $this->user2));
        // User 2 and 3 should have perfect similarity.
        $this->assertGreaterThanOrEqual(0, $this->recommender->similarityPearson($prefs, $this->user2, $this->user3));
    }
}
 
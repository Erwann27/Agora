<?php

namespace App\Tests\Game\Myrmes\Integration\Service;

use App\Entity\Game\Myrmes\GameMYR;
use App\Entity\Game\Myrmes\MainBoardMYR;
use App\Entity\Game\Myrmes\MyrmesParameters;
use App\Entity\Game\Myrmes\NurseMYR;
use App\Entity\Game\Myrmes\PersonalBoardMYR;
use App\Entity\Game\Myrmes\PlayerMYR;
use App\Entity\Game\Myrmes\SeasonMYR;
use App\Service\Game\Myrmes\EventMYRService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventMYRServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private EventMYRService $eventMYRService;

    protected function setUp() : void
    {
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->eventMYRService = static::getContainer()->get(EventMYRService::class);
    }

    public function testChooseBonusShouldWorkWithPositive() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoardMYR();
        $personalBoard->setBonus(1);
        $personalBoard->setLarvaCount(2);
        $this->entityManager->persist($personalBoard);
        $this->entityManager->flush();
        $bonusWanted = 2;
        //WHEN
        $this->eventMYRService->upBonus($player);

        //THEN
        $this->assertSame($bonusWanted, $personalBoard->getBonus());
    }

    public function testChooseBonusShouldWorkWithNegative() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoardMYR();
        $personalBoard->setLarvaCount(4);
        $personalBoard->setBonus(5);
        $this->entityManager->persist($personalBoard);
        $this->entityManager->flush();
        $bonusWanted = 4;
        //WHEN
        $this->eventMYRService->lowerBonus($player, $bonusWanted);

        //THEN
        $this->assertSame($bonusWanted, $personalBoard->getBonus());
    }

    public function testChooseBonusShouldFailBecauseBonusDoesntExist() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoardMYR();
        $personalBoard->setBonus(MyrmesParameters::BONUS_WORKER);
        $this->entityManager->persist($personalBoard);
        $this->entityManager->flush();
        //THEN
        $this->expectException(\Exception::class);
        //WHEN
        $this->eventMYRService->upBonus($player);
    }

    public function testChooseBonusShouldFailBecauseNotEnoughLarvae() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoardMYR();
        $personalBoard->setLarvaCount(0);
        $personalBoard->setBonus(1);
        $this->entityManager->persist($personalBoard);
        $this->entityManager->flush();
        //THEN
        $this->expectException(\Exception::class);
        //WHEN
        $this->eventMYRService->lowerBonus($player);
    }

    public function testChooseBonusShouldFailBecauseNotEventPhase() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $player = $game->getPlayers()->first();
        $player->setPhase(MyrmesParameters::PHASE_BIRTH);
        $personalBoard = $player->getPersonalBoardMYR();
        $personalBoard->setLarvaCount(2);
        $this->entityManager->persist($personalBoard);
        $this->entityManager->persist($player);
        $this->entityManager->flush();
        $bonusWanted = 1;
        //THEN
        $this->expectException(\Exception::class);
        //WHEN
        $this->eventMYRService->upBonus($player, $bonusWanted);
    }

    public function testUpBonusTwice() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoardMYR();
        $personalBoard->setBonus(1);
        $personalBoard->setLarvaCount(2);
        $this->entityManager->persist($personalBoard);
        $this->entityManager->flush();
        $bonusWanted = 3;
        //WHEN
        $this->eventMYRService->upBonus($player);
        $this->eventMYRService->upBonus($player);

        //THEN
        $this->assertSame($bonusWanted, $personalBoard->getBonus());
    }

    public function testUpBonusThenLowerBonus() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoardMYR();
        $personalBoard->setBonus(1);
        $personalBoard->setLarvaCount(2);
        $this->entityManager->persist($personalBoard);
        $this->entityManager->flush();
        $bonusWanted = 1;
        $selectedLarvaeExpected = 0;
        //WHEN
        $this->eventMYRService->upBonus($player);
        $this->eventMYRService->lowerBonus($player);

        //THEN
        $this->assertSame($bonusWanted, $personalBoard->getBonus());
        $this->assertSame($selectedLarvaeExpected, $personalBoard->getSelectedEventLarvaeAmount());

    }

    private function createGame(int $numberOfPlayers) : GameMYR
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        if($numberOfPlayers < MyrmesParameters::MIN_NUMBER_OF_PLAYER ||
            $numberOfPlayers > MyrmesParameters::MAX_NUMBER_OF_PLAYER) {
            throw new \Exception("TOO MUCH PLAYERS ON CREATE GAME");
        }
        $game = new GameMYR();
        $mainBoard = new MainBoardMYR();
        $mainBoard->setYearNum(0);
        $mainBoard->setGame($game);
        $season = new SeasonMYR();
        $season->setName("Spring");
        $season->setDiceResult(1);
        $season->setMainBoard($mainBoard);
        $season->setActualSeason(true);
        $mainBoard->addSeason($season);
        $entityManager->persist($season);
        $entityManager->persist($season);
        $game->setMainBoardMYR($mainBoard);
        $game->setGameName("test");
        $game->setLaunched(true);
        $game->setGamePhase(MyrmesParameters::PHASE_INVALID);
        $entityManager->persist($mainBoard);
        $entityManager->persist($game);
        for ($i = 0; $i < $numberOfPlayers; $i += 1) {
            $player = new PlayerMYR('test', $game);
            $game->addPlayer($player);
            $player->setGameMyr($game);
            $player->setColor("");
            $player->setPhase(MyrmesParameters::PHASE_EVENT);
            $personalBoard = new PersonalBoardMYR();
            $personalBoard->setLarvaCount(0);
            $personalBoard->setAnthillLevel(0);
            $personalBoard->setWarriorsCount(0);
            $personalBoard->setSelectedEventLarvaeAmount(0);
            $personalBoard->setBonus(5);
            $player->setPersonalBoardMYR($personalBoard);
            $player->setScore(0);
            $player->setGoalLevel(0);
            $player->setRemainingHarvestingBonus(0);
            for($j = 0; $j < MyrmesParameters::START_NURSES_COUNT_PER_PLAYER; $j += 1) {
                $nurse = new NurseMYR();
                $nurse->setArea(MyrmesParameters::LARVAE_AREA);
                $nurse->setAvailable(true);
                $personalBoard->addNurse($nurse);
                $entityManager->persist($nurse);
            }
            $entityManager->persist($player);
            $entityManager->persist($personalBoard);
            $entityManager->flush();
        }
        $entityManager->flush();
        return $game;
    }
}
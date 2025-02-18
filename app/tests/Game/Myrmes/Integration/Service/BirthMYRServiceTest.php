<?php

namespace App\Tests\Game\Myrmes\Integration\Service;

use App\Entity\Game\Myrmes\GameMYR;
use App\Entity\Game\Myrmes\MainBoardMYR;
use App\Entity\Game\Myrmes\MyrmesParameters;
use App\Entity\Game\Myrmes\NurseMYR;
use App\Entity\Game\Myrmes\PersonalBoardMYR;
use App\Entity\Game\Myrmes\PlayerMYR;
use App\Entity\Game\Myrmes\SeasonMYR;
use App\Service\Game\Myrmes\BirthMYRService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BirthMYRServiceTest extends KernelTestCase
{
     private EntityManagerInterface $entityManager;

    protected function setUp() : void
    {
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testPlaceNurseWhenNurseIsAvailable()
    {
        // GIVEN

        $birthMYRService = static::getContainer()->get(BirthMYRService::class);
        $game = $this->createGame(2);
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoardMYR();

        // WHEN

        $birthMYRService->placeNurse($player,
            MyrmesParameters::WORKSHOP_AREA);

        $birthMYRService->placeNurse($player,
            MyrmesParameters::SOLDIERS_AREA);

        // THEN

        foreach ($personalBoard->getNurses() as $nurse)
        {
            $this->assertNotSame(MyrmesParameters::BASE_AREA
                , $nurse->getArea());
        }

    }

    public function testPlaceNurseWhenNurseIsNotAvailable()
    {
        // GIVEN

        $birthMYRService = static::getContainer()->get(BirthMYRService::class);
        $game = $this->createGame(2);
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoardMYR();

        foreach ($personalBoard->getNurses() as $nurse)
        {
            $nurse->setAvailable(false);
            $this->entityManager->persist($nurse);
            $this->entityManager->flush();
        }

        $area = MyrmesParameters::LARVAE_AREA;

        // THEN

        $this->expectException(\Exception::class);

        // THEN

        $birthMYRService->placeNurse($player, $area);
    }

    public function testPlaceNurseWhenAreaIsAlreadyFull()
    {
        // GIVEN

        $birthMYRService = static::getContainer()->get(BirthMYRService::class);
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $personalBoard = $firstPlayer->getPersonalBoardMYR();

        $area = MyrmesParameters::LARVAE_AREA;

        foreach ($personalBoard->getNurses() as $nurse) {
            $nurse->setArea($area);
            $this->entityManager->persist($nurse);
            $this->entityManager->flush();
        }

        $nurse = new NurseMYR();
        $nurse->setAvailable(true);
        $nurse->setArea(MyrmesParameters::BASE_AREA);
        $personalBoard->addNurse($nurse);

        $this->entityManager->persist($nurse);
        $this->entityManager->persist($personalBoard);
        $this->entityManager->persist($firstPlayer);
        $this->entityManager->flush();

        // THEN

        $this->expectException(\Exception::class);

        // WHEN

        $birthMYRService->placeNurse($firstPlayer, $area);
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
        $season->setMainBoard($mainBoard);
        $season->setActualSeason(true);
        $mainBoard->addSeason($season);
        $season->setDiceResult(1);
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
            $personalBoard->setSelectedEventLarvaeAmount(0);
            $personalBoard->setAnthillLevel(0);
            $personalBoard->setWarriorsCount(0);
            $personalBoard->setBonus(0);
            $player->setPersonalBoardMYR($personalBoard);
            $player->setScore(0);
            $player->setGoalLevel(0);
            $player->setRemainingHarvestingBonus(0);
            for($j = 0; $j < MyrmesParameters::START_NURSES_COUNT_PER_PLAYER; $j += 1) {
                $nurse = new NurseMYR();
                $nurse->setArea(MyrmesParameters::BASE_AREA);
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
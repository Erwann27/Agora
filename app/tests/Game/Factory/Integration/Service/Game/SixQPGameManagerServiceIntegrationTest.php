<?php

namespace App\Tests\Game\Factory\Integration\Service\Game;

use App\Entity\Game\SixQP\GameSixQP;
use App\Entity\Game\SixQP\PlayerSixQP;
use App\Entity\Game\SixQP\RowSixQP;
use App\Entity\Platform\User;
use App\Service\Game\AbstractGameManagerService;
use App\Service\Game\GameManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SixQPGameManagerServiceIntegrationTest extends KernelTestCase
{

    public function testCreateValidGame() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);

        $labelGames = array(AbstractGameManagerService::SIXQP_LABEL);

        foreach ($labelGames as $labelGame)
        {
            $game = $gameService->createGame($labelGame);
            $this->assertTrue($game > 0);
        }
    }

    public function testJoinGameWhenGameIsNull() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $gameId = -1;
        $result = $gameService->joinGame($gameId, new User());
        $this->assertEquals(AbstractGameManagerService::ERROR_INVALID_GAME, $result);
    }

    public function testJoinGameWhenGameIsLaunched() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $game = $this->createSixQPGame(3, 4);
        $user = new User();
        $user->setUsername("toto");
        $game->setLaunched(true);
        $result = $gameService->joinGame($game->getId(), $user);
        $this->assertEquals(AbstractGameManagerService::ERROR_GAME_ALREADY_LAUNCHED, $result);
    }

    public function testJoinGameWhenGameIsFull() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $game = $this->createSixQPGame(10, 4);
        $user = new User();
        $user->setUsername("toto");
        $result = $gameService->joinGame($game->getId(), $user);
        $this->assertEquals(AbstractGameManagerService::ERROR_INVALID_NUMBER_OF_PLAYER, $result);
    }

    public function testJoinGameWhenGameIsInvalid() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $user = new User();
        $user->setUsername("toto");
        $result = $gameService->joinGame(-1, $user);
        $this->assertEquals(AbstractGameManagerService::ERROR_INVALID_GAME, $result);
    }

    public function testJoinGameWhenPlayerAlreadyInGame() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $game = $this->createSixQPGame(3, 4);
        $user = new User();
        $user->setUsername("toto");
        $gameService->joinGame($game->getId(), $user);
        $result = $gameService->joinGame($game->getId(), $user);
        $this->assertEquals(AbstractGameManagerService::ERROR_ALREADY_IN_PARTY, $result);
    }

    public function testJoinGameSuccessfully() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $game = $this->createSixQPGame(3, 4);
        $user = new User();
        $user->setUsername("toto");
        $result = $gameService->joinGame($game->getId(), $user);
        $this->assertEquals(AbstractGameManagerService::SUCCESS, $result);
    }
    public function testQuitGameWhenGameIsNull()
    {
        // GIVEN
        $gameService = static::getContainer()->get(GameManagerService::class);
        $gameId = -1;
        $user = new User();
        // WHEN
        $result = $gameService->quitGame($gameId, $user);
        // THEN
        $this->assertEquals(AbstractGameManagerService::ERROR_INVALID_GAME, $result);
    }

    public function testQuitGameWhenGameIsLaunched()
    {
        // GIVEN
        $gameService = static::getContainer()->get(GameManagerService::class);
        $game = $this->createSixQPGame(3, 4);
        $gameId = $game->getId();
        $gameService->launchGame($gameId);
        $user = new User();
        // WHEN
        $result = $gameService->quitGame($gameId, $user);
        // THEN
        $this->assertEquals(AbstractGameManagerService::ERROR_GAME_ALREADY_LAUNCHED, $result);
    }

    public function testQuitGameWhenPlayerIsInvalid()
    {
        // GIVEN
        $gameService = static::getContainer()->get(GameManagerService::class);
        $game = $this->createSixQPGame(3, 4);
        $gameId = $game->getId();
        $user = new User();
        $user->setUsername("vrgyuvgryugerzygvzyegfyzefgbyezgfy");
        // WHEN
        $result = $gameService->quitGame($gameId, $user);
        // THEN
        $this->assertEquals(AbstractGameManagerService::ERROR_PLAYER_NOT_FOUND, $result);
    }
    public function testQuitGameOnSuccess()
    {
        // GIVEN
        $gameService = static::getContainer()->get(GameManagerService::class);
        $game = $this->createSixQPGame(3, 4);
        $gameId = $game->getId();
        $userName = $game->getPlayers()[0]->getUsername();
        $user = new User();
        $user->setUsername($userName);
        // WHEN
        $result = $gameService->quitGame($gameId, $user);
        // THEN
        $this->assertEquals(AbstractGameManagerService::SUCCESS, $result);
    }

    public function testDeleteGameWhenGameIsInvalid() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);

        $this->assertEquals(AbstractGameManagerService::ERROR_INVALID_GAME, $gameService->deleteGame(-1));
    }

    public function testDeleteGameWhenGameIsValid() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $invalidGame = new GameSixQP();
        $entityManager->persist($invalidGame);
        $entityManager->flush();
        $this->assertEquals(AbstractGameManagerService::SUCCESS, $gameService->deleteGame($invalidGame->getId()));
    }

    public function testLaunchGame6QPFailWhenNotEnoughPlayers() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $invalidGame = new GameSixQP();
        $entityManager->persist($invalidGame);
        $entityManager->flush();
        $this->assertEquals(AbstractGameManagerService::ERROR_INVALID_NUMBER_OF_PLAYER, $gameService->launchGame($invalidGame->getId()));
    }

    public function testLaunchGame6QPFailWhenTooManyPlayers() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $invalidGame = $this->createSixQPGame(11, 4);
        $entityManager->persist($invalidGame);
        $entityManager->flush();
        $this->assertEquals(AbstractGameManagerService::ERROR_INVALID_NUMBER_OF_PLAYER, $gameService->launchGame($invalidGame->getId()));
    }
    public function testLaunchGame6QPFailWhenGameIsInvalid() : void
    {
        $gameService = static::getContainer()->get(GameManagerService::class);
        $this->assertEquals(AbstractGameManagerService::ERROR_INVALID_GAME, $gameService->launchGame(-1));
    }

    public function testLaunchGame6QPSuccessWhenGameIsValid() : void
    {
        $user1 = new User();
        $user1->setUsername("test1");
        $user2 = new User();
        $user2->setUsername("test2");
        $user3 = new User();
        $user3->setUsername("test3");
        $user4 = new User();
        $user4->setUsername("test4");

        $gameService = static::getContainer()->get(GameManagerService::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $validGame = $this->createSixQPGame(4, 4);
        $entityManager->persist($validGame);
        $entityManager->flush();
        $gameId = $validGame->getId();
        $gameService->joinGame($gameId, $user1);
        $gameService->joinGame($gameId, $user2);
        $gameService->joinGame($gameId, $user3);
        $gameService->joinGame($gameId, $user4);

        $this->assertEquals(AbstractGameManagerService::SUCCESS, $gameService->launchGame($gameId));
    }

    private function createSixQPGame(int $numberOfPlayer, int $numberOfRow): GameSixQP
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $game = new GameSixQP();
        $game->setGameName(AbstractGameManagerService::SIXQP_LABEL);
        for ($i = 0; $i < $numberOfRow; $i++) {
            $row = new RowSixQP();
            $row->setPosition($i);
            $game->addRowSixQP($row);
            $entityManager->persist($row);
        }
        $entityManager->persist($game);
        for ($i = 0; $i < $numberOfPlayer; $i++) {
            $player = new PlayerSixQP('test', $game);
            $player->setScore(0);
            $game->addPlayer($player);
            $entityManager->persist($player);
            $entityManager->flush();
        }
        $entityManager->flush();
        return $game;
    }
}

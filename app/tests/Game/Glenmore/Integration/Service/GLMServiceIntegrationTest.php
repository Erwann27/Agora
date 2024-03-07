<?php

namespace App\Tests\Game\Glenmore\Integration\Service;

use App\Entity\Game\Glenmore\BoardTileGLM;
use App\Entity\Game\Glenmore\CardGLM;
use App\Entity\Game\Glenmore\DrawTilesGLM;
use App\Entity\Game\Glenmore\GameGLM;
use App\Entity\Game\Glenmore\GlenmoreParameters;
use App\Entity\Game\Glenmore\MainBoardGLM;
use App\Entity\Game\Glenmore\PawnGLM;
use App\Entity\Game\Glenmore\PersonalBoardGLM;
use App\Entity\Game\Glenmore\PlayerCardGLM;
use App\Entity\Game\Glenmore\PlayerGLM;
use App\Entity\Game\Glenmore\PlayerTileGLM;
use App\Entity\Game\Glenmore\PlayerTileResourceGLM;
use App\Entity\Game\Glenmore\ResourceGLM;
use App\Entity\Game\Glenmore\WarehouseGLM;
use App\Repository\Game\Glenmore\CardGLMRepository;
use App\Repository\Game\Glenmore\ResourceGLMRepository;
use App\Repository\Game\Glenmore\TileGLMRepository;
use App\Service\Game\AbstractGameManagerService;
use App\Service\Game\Glenmore\GLMService;
use App\Service\Game\Glenmore\TileGLMService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GLMServiceIntegrationTest extends KernelTestCase
{
    public function testDoNotSkipPlayerTurnWhenPlayerIsStillTheLastInChain()
    {
        // GIVEN
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $GLMService = static::getContainer()->get(GLMService::class);
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $secondPlayer = $game->getPlayers()->last();
        $startTurnPosition = $firstPlayer->getPawn()->getPosition();
        $firstPlayer->getPawn()->setPosition(($startTurnPosition + 1) %
            GlenmoreParameters::$NUMBER_OF_BOXES_ON_BOARD);
        $secondPlayer->getPawn()->setPosition(($startTurnPosition + 3) %
            GlenmoreParameters::$NUMBER_OF_BOXES_ON_BOARD);
        $entityManager->persist($firstPlayer);
        $entityManager->persist($secondPlayer);
        $entityManager->persist($firstPlayer->getPawn());
        $entityManager->persist($secondPlayer->getPawn());
        $entityManager->flush();
        // WHEN
        $GLMService->endRoundOfPlayer($game, $firstPlayer, $startTurnPosition);
        // THEN
        $this->assertTrue($firstPlayer->isTurnOfPlayer());
    }

    public function testSkipTurnOfPlayerWhenPlayerIsNoLongerTheLastInChain()
    {
        // GIVEN
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $GLMService = static::getContainer()->get(GLMService::class);
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $secondPlayer = $game->getPlayers()->last();
        $startTurnPosition = $firstPlayer->getPawn()->getPosition();
        $firstPlayer->getPawn()->setPosition(($startTurnPosition + 3) %
            GlenmoreParameters::$NUMBER_OF_BOXES_ON_BOARD);
        $entityManager->persist($firstPlayer);
        $entityManager->persist($firstPlayer->getPawn());
        $entityManager->flush();
        // WHEN
        $GLMService->endRoundOfPlayer($game, $firstPlayer, $startTurnPosition);
        // THEN
        $this->assertFalse($firstPlayer->isTurnOfPlayer());
        $this->assertTrue($secondPlayer->isTurnOfPlayer());
    }

    public function testIsGameEndedShouldReturnTrue() : void
    {
        //GIVEN
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $GLMService = static::getContainer()->get(GLMService::class);
        $game = $this->createGame(4);
        $drawTiles = $game->getMainBoard()->getDrawTiles();
        foreach ($drawTiles as $drawTile) {
            $drawTile->getTiles()->clear();
            $entityManager->persist($drawTile);
        }
        $entityManager->persist($game->getMainBoard());
        $entityManager->persist($game);
        $entityManager->flush();
        //WHEN
        $result = $GLMService->isGameEnded($game);
        //THEN
        $this->assertTrue($result);
    }

    public function testIsGameEndedShouldReturnFalse() : void
    {
        //GIVEN
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $GLMService = static::getContainer()->get(GLMService::class);
        $game = $this->createGame(4);
        $drawTiles = $game->getMainBoard()->getDrawTiles();
        foreach ($drawTiles as $drawTile) {
            if ($drawTile->getLevel() == GlenmoreParameters::$TILE_LEVEL_THREE) {
                break;
            }
            $drawTile->getTiles()->clear();
            $entityManager->persist($drawTile);
        }
        $drawLevelThree = $drawTiles->get(GlenmoreParameters::$TILE_LEVEL_THREE);
        $tiles = $drawLevelThree->getTiles();
        for ($i = 0; $i < $tiles->count() - 1; ++$i) {
            $drawLevelThree->removeTile($tiles->get($i));
            $entityManager->persist($drawLevelThree);
        }
        $entityManager->persist($game->getMainBoard());
        $entityManager->persist($game);
        $entityManager->flush();
        //WHEN
        $result = $GLMService->isGameEnded($game);
        //THEN
        $this->assertFalse($result);
    }

    public function testCalculatePointsAtEndOfLevelWithWhiskyDifference() : void
    {
        //GIVEN
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $GLMService = static::getContainer()->get(GLMService::class);
        $resourceRepository = static::getContainer()->get(ResourceGLMRepository::class);
        $tileRepository = static::getContainer()->get(TileGLMRepository::class);
        $game = $this->createGame(5);
        $players = $game->getPlayers();
        for ($i = 0; $i < 5; ++$i) {
            $personalBoard = $players[$i]->getPersonalBoard();
            $tile = new PlayerTileGLM();
            $tile->setActivated(false);
            $tile->setCoordY(0);
            $tile->setCoordX(0);
            $startTile = $tileRepository->findOneBy(["id" => 68]);
            $tile->setTile($startTile);
            $tileResource = new PlayerTileResourceGLM();
            $resource = $resourceRepository->findOneBy(["id" => 6]);
            $tileResource->setResource($resource);
            $tileResource->setQuantity($i);
            $tileResource->setPlayerTileGLM($tile);
            $entityManager->persist($tileResource);
            $tile->addPlayerTileResource($tileResource);
            $tile->setPersonalBoard($personalBoard);
            $entityManager->persist($tile);
            $personalBoard->addPlayerTile($tile);
            $entityManager->persist($personalBoard);
        }
        $entityManager->flush();
        $expectedResult = [0, 1, 2, 3, 5];
        //WHEN
        $GLMService->calculatePointsAtEndOfLevel($game);
        //THEN
        $result = [$players[0]->getPoints(), $players[1]->getPoints(), $players[2]->getPoints(),
            $players[3]->getPoints(), $players[4]->getPoints()];
        $this->assertSame($expectedResult, $result);
    }

    public function testCalculatePointsAtEndOfLevelWithCastleOfMey() : void
    {
        //GIVEN
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $GLMService = static::getContainer()->get(GLMService::class);
        $resourceRepository = static::getContainer()->get(ResourceGLMRepository::class);
        $tileRepository = static::getContainer()->get(TileGLMRepository::class);
        $cardRepository = static::getContainer()->get(CardGLMRepository::class);
        $game = $this->createGame(2);
        $players = $game->getPlayers();
        $personalBoard = $players[1]->getPersonalBoard();
        $startTile = $tileRepository->findOneBy(["id" => 68]);

        $tile = new PlayerTileGLM();
        $tile->setActivated(false);
        $tile->setCoordY(0);
        $tile->setCoordX(0);
        $tile->setTile($startTile);
        $tileResource = new PlayerTileResourceGLM();
        $resource = $resourceRepository->findOneBy(["id" => 6]);
        $tileResource->setResource($resource);
        $tileResource->setQuantity(0);
        $tileResource->setPlayerTileGLM($tile);
        $entityManager->persist($tileResource);

        $tile->addPlayerTileResource($tileResource);
        $tile->setPersonalBoard($personalBoard);
        $entityManager->persist($tile);

        $personalBoard->addPlayerTile($tile);
        $entityManager->persist($personalBoard);

        $tile = new PlayerTileGLM();
        $tile->setActivated(false);
        $tile->setCoordY(0);
        $tile->setCoordX(0);
        $tile->setTile($startTile);
        $tileResource = new PlayerTileResourceGLM();
        $resource = $resourceRepository->findOneBy(["id" => 7]);
        $tileResource->setResource($resource);
        $tileResource->setQuantity(1);
        $tileResource->setPlayerTileGLM($tile);
        $entityManager->persist($tileResource);
        $tile->addPlayerTileResource($tileResource);
        $entityManager->persist($tile);
        $personalBoard->addPlayerTile($tile);
        $personalBoard->setLeaderCount(2);

        $card = $cardRepository->findOneBy(["id" => 1]);
        $card->setName(GlenmoreParameters::$CARD_CASTLE_OF_MEY);
        $playerCard = new PlayerCardGLM($personalBoard, $card);
        $entityManager->persist($playerCard);
        $personalBoard->addPlayerCardGLM($playerCard);
        $entityManager->persist($personalBoard);
        $tile->setPersonalBoard($personalBoard);
        $entityManager->persist($tile);
        $expectedResult = [0, 9];
        //WHEN
        $GLMService->calculatePointsAtEndOfLevel($game);
        //THEN
        $result = [$players[0]->getPoints(), $players[1]->getPoints()];
        $this->assertSame($expectedResult, $result);
    }

    private function createGame(int $nbOfPlayers) : GameGLM
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $tileGLMRepository = static::getContainer()->get(TileGLMRepository::class);
        $resourceGLMRepository = static::getContainer()->get(ResourceGLMRepository::class);
        $game = new GameGLM();
        $game->setGameName(AbstractGameManagerService::$GLM_LABEL);
        $mainBoard = new MainBoardGLM();
        $mainBoard->setGameGLM($game);
        $tilesLevelZero = $tileGLMRepository->findBy(['level' => GlenmoreParameters::$TILE_LEVEL_ZERO]);
        $tilesLevelOne = $tileGLMRepository->findBy(['level' => GlenmoreParameters::$TILE_LEVEL_ONE]);
        $tilesLevelTwo = $tileGLMRepository->findBy(['level' => GlenmoreParameters::$TILE_LEVEL_TWO]);
        $tilesLevelThree = $tileGLMRepository->findBy(['level' => GlenmoreParameters::$TILE_LEVEL_THREE]);
        $drawArray = [$tilesLevelZero, $tilesLevelOne, $tilesLevelTwo, $tilesLevelThree];
        for ($i = GlenmoreParameters::$TILE_LEVEL_ZERO; $i <= GlenmoreParameters::$TILE_LEVEL_THREE; ++$i) {
            $draw = new DrawTilesGLM();
            $draw->setLevel($i);
            $draw->setMainBoardGLM($mainBoard);
            $tiles = $drawArray[$i];
            foreach ($tiles as $tile) {
                $draw->addTile($tile);
            }
            $entityManager->persist($draw);
            $mainBoard->addDrawTile($draw);
            $entityManager->persist($mainBoard);
            $warehouse = new WarehouseGLM();
            $warehouse->setMainBoardGLM($mainBoard);
            $entityManager->persist($warehouse);
            $entityManager->persist($mainBoard);
        }

        for ($i = 0; $i < $nbOfPlayers; $i++) {
            $player = new PlayerGLM('test', $game);
            $player->setGameGLM($game);
            $player->setTurnOfPlayer(false);
            $player->setPoints(0);
            $game->addPlayer($player);
            $personalBoard = new PersonalBoardGLM();
            $player->setPersonalBoard($personalBoard);
            $personalBoard->setPlayerGLM($player);
            $personalBoard->setLeaderCount(0);
            $personalBoard->setMoney(GlenmoreParameters::$START_MONEY);
            $pawn = new PawnGLM();
            $pawn->setColor(GlenmoreParameters::$COLOR_FROM_POSITION[$i]);
            $pawn->setPosition($i);
            $pawn->setMainBoardGLM($mainBoard);
            $player->setPawn($pawn);
            $entityManager->persist($pawn);
            $entityManager->persist($player);
            $startVillages = $tileGLMRepository->findBy(['name' => GlenmoreParameters::$TILE_NAME_START_VILLAGE]);
            $villager = $resourceGLMRepository->findOneBy(['type' => GlenmoreParameters::$VILLAGER_RESOURCE]);
            $playerTile = new PlayerTileGLM();
            $playerTile->setActivated(false);
            $playerTile->setCoordX(0);
            $playerTile->setCoordY(0);
            $playerTile->setTile($startVillages[$i]);
            $playerTile->setCoordX(0);
            $playerTile->setCoordY(0);
            $playerTile->setPersonalBoard($personalBoard);
            $entityManager->persist($playerTile);
            $playerTileResource = new PlayerTileResourceGLM();
            $playerTileResource->setPlayerTileGLM($playerTile);
            $playerTileResource->setResource($villager);
            $playerTileResource->setQuantity(1);
            $entityManager->persist($playerTileResource);
            $entityManager->persist($personalBoard);
            $entityManager->persist($player);
        }

        for ($i = $nbOfPlayers; $i < GlenmoreParameters::$NUMBER_OF_BOXES_ON_BOARD; ++$i) {
            $drawTiles = $mainBoard->getDrawTiles();
            $level = 0;
            for ($j = GlenmoreParameters::$TILE_LEVEL_ZERO; $j <= GlenmoreParameters::$TILE_LEVEL_THREE; ++$j) {
                if ($drawTiles->get($j)->getTiles()->isEmpty()) {
                    ++$level;
                } else {
                    break;
                }
            }
            $draw = $drawTiles->get($level);
            $tile = $draw->getTiles()->first();
            $mainBoardTile = new BoardTileGLM();
            $mainBoardTile->setTile($tile);
            $mainBoardTile->setMainBoardGLM($mainBoard);
            $mainBoardTile->setPosition($i);
            $entityManager->persist($mainBoardTile);
            $draw->removeTile($tile);
            $entityManager->persist($draw);
        }
        $firstPlayer = $game->getPlayers()->first();
        $firstPlayer->setTurnOfPlayer(true);
        $entityManager->persist($firstPlayer);
        $entityManager->flush();
        return $game;
    }
}
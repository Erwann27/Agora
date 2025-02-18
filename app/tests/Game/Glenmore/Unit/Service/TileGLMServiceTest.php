<?php

namespace App\Tests\Game\Glenmore\Unit\Service;

use App\Entity\Game\Glenmore\BoardTileGLM;
use App\Entity\Game\Glenmore\BuyingTileGLM;
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
use App\Entity\Game\Glenmore\SelectedResourceGLM;
use App\Entity\Game\Glenmore\TileActivationBonusGLM;
use App\Entity\Game\Glenmore\TileActivationCostGLM;
use App\Entity\Game\Glenmore\TileBuyBonusGLM;
use App\Entity\Game\Glenmore\TileGLM;
use App\Entity\Game\Glenmore\WarehouseGLM;
use App\Entity\Game\Glenmore\WarehouseLineGLM;
use App\Repository\Game\Glenmore\BoardTileGLMRepository;
use App\Repository\Game\Glenmore\DrawTilesGLMRepository;
use App\Repository\Game\Glenmore\PlayerGLMRepository;
use App\Repository\Game\Glenmore\PlayerTileGLMRepository;
use App\Repository\Game\Glenmore\PlayerTileResourceGLMRepository;
use App\Repository\Game\Glenmore\ResourceGLMRepository;
use App\Repository\Game\Glenmore\SelectedResourceGLMRepository;
use App\Repository\Game\Glenmore\TileGLMRepository;
use App\Service\Game\AbstractGameManagerService;
use App\Service\Game\Glenmore\CardGLMService;
use App\Service\Game\Glenmore\GLMService;
use App\Service\Game\Glenmore\TileGLMService;
use App\Service\Game\LogService;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TileGLMServiceTest extends TestCase
{

    private TileGLMService $tileGLMService;

    private GLMService $GLMService;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $resourceGLMRepository = $this->createMock(ResourceGLMRepository::class);
        $playerGLMRepository = $this->createMock(PlayerGLMRepository::class);
        $playerTileGLMRepository = $this->createMock(PlayerTileGLMRepository::class);
        $playerTileResourceGLMRepository = $this->createMock(PlayerTileResourceGLMRepository::class);
        $tileGlMRepository = $this->createMock(TileGLMRepository::class);
        $boardTileRepo = $this->createMock(BoardTileGLMRepository::class);
        $selectedResourceRepo = $this->createMock(SelectedResourceGLMRepository::class);
        $drawTilesRepo = $this->createMock(DrawTilesGLMRepository::class);
        $cardGLMService = new CardGLMService($entityManager, $resourceGLMRepository);
        $logService = new LogService($entityManager);
        $this->tileGLMService = new TileGLMService($entityManager
            , $resourceGLMRepository, $playerGLMRepository, $playerTileResourceGLMRepository, $playerTileGLMRepository,
            $cardGLMService, $selectedResourceRepo);
        $cardGLMService = new CardGLMService($entityManager, $resourceGLMRepository);
        $this->GLMService = new GLMService($entityManager, $tileGlMRepository, $this->tileGLMService,
           $logService, $drawTilesRepo, $resourceGLMRepository, $playerGLMRepository, $boardTileRepo, $cardGLMService);
    }

    public function testGiveBuyBonusWithSimpleProductionTile() : void
    {
        //GIVEN
        $game = $this->createGame(4);
        $firstPlayer = $game->getPlayers()->first();
        $personalBoard = $firstPlayer->getPersonalBoard();
        $tile = new TileGLM();
        $tile->setName(GlenmoreParameters::TILE_NAME_FOREST);
        $tile->setType(GlenmoreParameters::TILE_TYPE_GREEN);
        $buyBonus = new TileBuyBonusGLM();
        $resource = new ResourceGLM();
        $resource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $resource->setColor(GlenmoreParameters::COLOR_GREEN);
        $buyBonus->setResource($resource);
        $buyBonus->setAmount(1);
        $tile->addBuyBonus($buyBonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerTile->setPersonalBoard($personalBoard);
        $personalBoard->addPlayerTile($playerTile);
        $expectedAmount = 1;
        $expectedType = GlenmoreParameters::PRODUCTION_RESOURCE;
        $expectedColor = GlenmoreParameters::COLOR_GREEN;
        //WHEN
        $this->tileGLMService->giveBuyBonus($playerTile);
        //THEN
        $amount = 0;
        $type = "";
        $color = "";
        foreach ($personalBoard->getPlayerTiles() as $tile) {
            if ($tile === $playerTile) {
                $playerTileResources = $tile->getPlayerTileResource();
                foreach ($playerTileResources as $playerTileResource) {
                    $amount = $playerTileResource->getQuantity();
                    $type = $playerTileResource->getResource()->getType();
                    $color = $playerTileResource->getResource()->getColor();
                }
            }
        }
        $this->assertEquals($expectedAmount, $amount);
        $this->assertSame($expectedType, $type);
        $this->assertSame($expectedColor, $color);
    }

    public function testGiveBuyBonusWithSimpleProductionTileWhenNoBuyBonus() : void
    {
        //GIVEN
        $game = $this->createGame(4);
        $firstPlayer = $game->getPlayers()->first();
        $personalBoard = $firstPlayer->getPersonalBoard();
        $tile = new TileGLM();
        $tile->setName(GlenmoreParameters::TILE_NAME_FOREST);
        $tile->setType(GlenmoreParameters::TILE_TYPE_GREEN);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerTile->setPersonalBoard($personalBoard);
        $personalBoard->addPlayerTile($playerTile);
        $expectedAmount = 0;
        //WHEN
        $this->tileGLMService->giveBuyBonus($playerTile);
        //THEN
        $amount = 0;
        $type = null;
        $color = null;
        foreach ($personalBoard->getPlayerTiles() as $tile) {
            if ($tile === $playerTile) {
                $playerTileResources = $tile->getPlayerTileResource();
                foreach ($playerTileResources as $playerTileResource) {
                    $amount = $playerTileResource->getQuantity();
                    $type = $playerTileResource->getResource()->getType();
                    $color = $playerTileResource->getResource()->getColor();
                }
            }
        }
        $this->assertEquals($expectedAmount, $amount);
        $this->assertNull($type);
        $this->assertNull($color);
    }

    public function testGiveBuyBonusWithCard() : void
    {
        //GIVEN
        $game = $this->createGame(4);
        $firstPlayer = $game->getPlayers()->first();
        $personalBoard = $firstPlayer->getPersonalBoard();
        $tile = new TileGLM();
        $tile->setName(GlenmoreParameters::CARD_CAWDOR_CASTLE);
        $tile->setType(GlenmoreParameters::TILE_TYPE_CASTLE);
        $buyBonus = new TileBuyBonusGLM();
        $resource = new ResourceGLM();
        $resource->setType(GlenmoreParameters::VILLAGER_RESOURCE);
        $resource->setColor(GlenmoreParameters::COLOR_BLACK);
        $buyBonus->setResource($resource);
        $buyBonus->setAmount(1);
        $tile->addBuyBonus($buyBonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerTile->setPersonalBoard($personalBoard);
        $personalBoard->addPlayerTile($playerTile);
        $card = new CardGLM();
        $card->setName(GlenmoreParameters::CARD_CAWDOR_CASTLE);
        $card->setValue(0);
        $cardBonus = new TileBuyBonusGLM();
        $resource = new ResourceGLM();
        $resource->setType(GlenmoreParameters::HAT_RESOURCE);
        $resource->setColor(GlenmoreParameters::COLOR_BROWN);
        $cardBonus->setResource($resource);
        $cardBonus->setAmount(3);
        $card->setBonus($cardBonus);
        $tile->setCard($card);
        $playerCard = new PlayerCardGLM($personalBoard, $card);
        $personalBoard->addPlayerCardGLM($playerCard);
        $expectedAmountVillager = 1;
        $expectedAmountHat = 3;
        $expectedTypeVillager = GlenmoreParameters::VILLAGER_RESOURCE;
        $expectedTypeHat = GlenmoreParameters::HAT_RESOURCE;
        //WHEN
        $this->tileGLMService->giveBuyBonus($playerTile);
        //THEN
        $amountVillager = 0;
        $amountHat = 0;
        $typeVillager = null;
        $typeHat = null;
        foreach ($personalBoard->getPlayerTiles() as $newTile) {
            if ($newTile === $playerTile) {
                $playerTileResources = $newTile->getPlayerTileResource();
                foreach ($playerTileResources as $playerTileResource) {
                    $typeVillager = $playerTileResource->getResource()->getType();
                    if ($typeVillager === $expectedTypeVillager) {
                        $amountVillager = $playerTileResource->getQuantity();
                        break;
                    }
                }
            }
        }
        foreach ($personalBoard->getPlayerCardGLM() as $playerCard) {
            $actualCard = $playerCard->getCard();
            if ($tile->getCard() === $actualCard) {
                $typeHat = $actualCard->getBonus()->getResource()->getType();
                if ($typeHat === $expectedTypeHat) {
                    $amountHat = $actualCard->getBonus()->getAmount();
                    break;
                }
            }
        }
        $this->assertEquals($expectedAmountVillager, $amountVillager);
        $this->assertSame($expectedTypeVillager, $typeVillager);
        $this->assertEquals($expectedAmountHat, $amountHat);
        $this->assertSame($expectedTypeHat, $typeHat);
    }

    public function testPlaceNewTileOnMainBoard() : void
    {
        // GIVEN

        $nbOfPlayers = 4;
        $game = $this->createGame($nbOfPlayers);
        $mainBoard = $game->getMainBoard();
        $firstPlayer = $game->getPlayers()->first();
        $secondPlayer = $game->getPlayers()->get(2);
        $boardTile = $mainBoard->getBoardTiles()->last();
        $buyingTile = new BuyingTileGLM();
        $buyingTile->setBoardTile($boardTile);
        $firstPlayerTile = new PlayerTileGLM();
        $firstPlayerTile->setTile($boardTile->getTile());
        $mainBoard->removeBoardTile($boardTile);

        $firstPlayer->getPersonalBoard()->setBuyingTile($buyingTile);
        $firstPlayer->getPersonalBoard()->addPlayerTile($firstPlayerTile);
        $lastPosition = $mainBoard->getLastPosition();
        $lastPosition -= 1;
        if ($lastPosition < 0) {
            $lastPosition += GlenmoreParameters::NUMBER_OF_BOXES_ON_BOARD;
        }
        // WHEN

        $this->tileGLMService->placeNewTile($secondPlayer,
            $mainBoard->getDrawTiles()->get(GlenmoreParameters::TILE_LEVEL_ONE));

        // THEN

        $this->assertNotContains($boardTile, $mainBoard->getBoardTiles());
        $this->assertEquals($lastPosition, $mainBoard->getBoardTiles()->last()->getPosition());
    }

    public function testSuccessTileAllocation() : void
    {
        // GIVEN

        $nbOfPlayers = 4;
        $game = $this->createGame($nbOfPlayers);
        $mainBoard = $game->getMainBoard();
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoard();
        $boardTile = $mainBoard->getBoardTiles()->last();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $resourceGLMRepository = $this->createMock(ResourceGLMRepository::class);
        $playerGLMRepository = $this->createMock(PlayerGLMRepository::class);
        $playerTileGLMRepository = $this->createMock(PlayerTileGLMRepository::class);
        $playerTileResourceGLMRepository = $this->createMock(PlayerTileResourceGLMRepository::class);
        $cardGLMService = new CardGLMService($entityManager, $resourceGLMRepository);
        $selectedResGLMRepo = $this->createMock(SelectedResourceGLMRepository::class);
        $mock = $this->getMockBuilder(TileGLMService::class)
            ->setConstructorArgs(array($entityManager, $resourceGLMRepository,
                $playerGLMRepository, $playerTileResourceGLMRepository,
                $playerTileGLMRepository, $cardGLMService, $selectedResGLMRepo))
            ->onlyMethods(['canPlaceTile'])
            ->getMock();
        $mock->method('canPlaceTile')->willReturn(true);
        // WHEN

        $mock->assignTileToPlayer($boardTile, $player);

        // THEN

        $this->assertSame($boardTile, $personalBoard->getBuyingTile()->getBoardTile());
        $this->assertContains($boardTile, $mainBoard->getBoardTiles());
    }

    /*
    public function testTileAllocationWhenPlayerHaveNotAllRequiredResources() : void
    {
        // GIVEN
        $nbOfPlayers = 4;
        $game = $this->createGame($nbOfPlayers);
        $player = $game->getPlayers()->first();
        $mainBoard = $game->getMainBoard();
        $personalBoard = $player->getPersonalBoard();
        $boardTiles = $mainBoard->getBoardTiles();

        $level = 2;
        for ($i = $nbOfPlayers; $i < GlenmoreParameters::NUMBER_OF_TILES_ON_BOARD; ++$i)
        {
            $drawTiles = $mainBoard->getDrawTiles();
            $draw = $drawTiles->get($level);

            if ($draw->getTiles()->count() == 0) {
                $draw = $drawTiles->get($level + 1);
            }

            $tile = $draw->getTiles()->last();
            $mainBoardTile = new BoardTileGLM();
            $mainBoardTile->setTile($tile);
            $mainBoardTile->setMainBoardGLM($mainBoard);
            $mainBoardTile->setPosition($i);
            $mainBoard->addBoardTile($mainBoardTile);
            $draw->removeTile($tile);
        }

        $boardTile = null;
        $resource = null;
        foreach ($boardTiles as $tile)
        {
            $buy = $tile->getTile()->getBuyPrice();
            if ($buy->count() != 0)
            {
                $r = $buy->first();
                $boardTile = $tile;
                $resource = new ResourceGLM();
                $resource->setColor($r->getResource()->getColor());
                $resource->setType($r->getResource()->getType());
            }
            if ($resource != null && $boardTile != null)
            {
                break;
            }
        }

        $personalBoard->setMoney(0);

        // WHEN

        $this->expectException(\Exception::class);

        // THEN

        $this->TileGLMService->assignTileToPlayer($player, $boardTile);
    }


    public function testTileAllocationWhenPlayerCanNotPlaceTileInPersonalBoard() : void
    {
        // GIVEN
        $nbOfPlayers = 4;
        $game = $this->createGame($nbOfPlayers);
        $player = $game->getPlayers()->first();
        $mainBoard = $game->getMainBoard();
        $personalBoard = $player->getPersonalBoard();

        $boardTiles = $mainBoard->getBoardTiles();

        // TODO provoquer l'erreur de placement

        // WHEN

        $this->expectException(\Exception::class);

        // THEN

        $this->TileGLMService->assignTileToPlayer($player, $boardTile);
    }
    */

    public function testSetPlaceTileWhenTileNotYetSelected() : void
    {

        // GIVEN

        $nbOfPlayer = 4;
        $game = $this->createGame($nbOfPlayer);
        $player = $game->getPlayers()->first();
        //$player->setTurnOfPlayer(true);

        // WHEN

        $this->expectException(Exception::class);

        // THEN

        $this->tileGLMService->setPlaceTileAlreadySelected($player, 0, 1);
    }

    public function testSetPlaceTileWhenTileIsSelectedAndCanPlaceTile() : void
    {
        // GIVEN

        $nbPlayers = 4;
        $game = $this->createGame($nbPlayers);
        $player = $game->getPlayers()->first();
        $personalBoard = $player->getPersonalBoard();
        $mainBoard = $game->getMainBoard();

        $tileSelected = $mainBoard->getBoardTiles()->first();
        $buyingTile = new BuyingTileGLM();
        $buyingTile->setBoardTile($tileSelected);
        $personalBoard->setBuyingTile($buyingTile);
        $lastPosition = $player->getPawn()->getPosition();
        $playerTileAlreadyInPersonalBoard = $personalBoard->getPlayerTiles()->first();
        $x = $playerTileAlreadyInPersonalBoard->getCoordX();
        $y = $playerTileAlreadyInPersonalBoard->getCoordY();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $tileGLMRepository = $this->createMock(TileGLMRepository::class);
        $drawTilesGLMRepository = $this->createMock(DrawTilesGLMRepository::class);
        $resourceGLMRepository = $this->createMock(ResourceGLMRepository::class);
        $playerGLMRepository = $this->createMock(PlayerGLMRepository::class);
        $playerTileGLMRepository = $this->createMock(PlayerTileGLMRepository::class);
        $playerTileResourceGLMRepository = $this->createMock(PlayerTileResourceGLMRepository::class);
        $cardGLMService = new CardGLMService($entityManager, $resourceGLMRepository);
        $selectedResGLMRepo = $this->createMock(SelectedResourceGLMRepository::class);

        $mock = $this->getMockBuilder(TileGLMService::class)
            ->setConstructorArgs(array($entityManager, $resourceGLMRepository,
                $playerGLMRepository, $playerTileResourceGLMRepository,
                $playerTileGLMRepository, $cardGLMService, $selectedResGLMRepo))
            ->onlyMethods(['canPlaceTile'])
            ->getMock();
        $mock->method('canPlaceTile')->willReturn(true);

        // WHEN

        $mock->setPlaceTileAlreadySelected($player, $x + 1, $y + 1);

        // THEN

        $this->assertEquals($tileSelected->getTile(),
            $personalBoard->getPlayerTiles()->last()->getTile());
        $this->assertNotContains($tileSelected, $mainBoard->getBoardTiles());
        $this->assertNotEquals($player->getPawn()->getPosition(), $lastPosition);
    }

    public function testGetAmountOfTileToReplaceWhenChainIsNotBroken()
    {
        // GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $playerGLMRepository = $this->createMock(PlayerGLMRepository::class);
        $resourceGLMRepository = $this->createMock(ResourceGLMRepository::class);
        $playerTileGLMRepository = $this->createMock(PlayerTileGLMRepository::class);
        $cardGLMService = $this->createMock(CardGLMService::class);
        $playerTileResourceGLMRepository = $this->createMock(PlayerTileResourceGLMRepository::class);
        $selectedResGLMRepo = $this->createMock(SelectedResourceGLMRepository::class);

        $mock = $this->getMockBuilder(TileGLMService::class)
            ->setConstructorArgs(array($entityManager, $resourceGLMRepository,
                $playerGLMRepository, $playerTileResourceGLMRepository,
                $playerTileGLMRepository, $cardGLMService, $selectedResGLMRepo))
            ->onlyMethods(['getActivePlayer'])
            ->getMock();
        $mock->method('getActivePlayer')->willReturn($firstPlayer);

        $boardTiles = $game->getMainBoard()->getBoardTiles();
        foreach ($boardTiles as $boardTile){
            if($boardTile->getPosition() == 12){
                $game->getMainBoard()->removeBoardTile($boardTile);
            }
        }
        $expectedResult = 1;
        // WHEN
        $result = $mock->getAmountOfTileToReplace($game->getMainBoard());
        // THEN
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetAmountOfTileToReplaceWhenChainIsBroken()
    {
        // GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $playerGLMRepository = $this->createMock(PlayerGLMRepository::class);
        $resourceGLMRepository = $this->createMock(ResourceGLMRepository::class);
        $playerTileGLMRepository = $this->createMock(PlayerTileGLMRepository::class);
        $cardGLMService = $this->createMock(CardGLMService::class);
        $selectedResGLMRepo = $this->createMock(SelectedResourceGLMRepository::class);
        $playerTileResourceGLMRepository = $this->createMock(PlayerTileResourceGLMRepository::class);
        $mock = $this->getMockBuilder(TileGLMService::class)
            ->setConstructorArgs(array($entityManager, $resourceGLMRepository,
                $playerGLMRepository, $playerTileResourceGLMRepository,
                $playerTileGLMRepository, $cardGLMService, $selectedResGLMRepo))
            ->onlyMethods(['getActivePlayer'])
            ->getMock();
        $mock->method('getActivePlayer')->willReturn($firstPlayer);
        $boardTiles = $game->getMainBoard()->getBoardTiles();
        foreach ($boardTiles as $boardTile){
            if($boardTile->getPosition() == 10){
                $boardTile->setPosition(13);
            }
        }
        $expectedResult = 3;
        // WHEN
        $result = $mock->getAmountOfTileToReplace($game->getMainBoard());
        // THEN
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetActiveDrawTileAtTheBeginningShouldBeLevelOne() : void
    {
        //GIVEN
        $game = $this->createGame(5);
        $expectedLevel = 1;
        //WHEN
        $drawTile = $this->tileGLMService->getActiveDrawTile($game);
        //THEN
        $this->assertSame($expectedLevel, $drawTile->getLevel());
    }

    public function testGetActiveDrawTileShouldBeLevelTwo() : void
    {
        //GIVEN
        $game = $this->createGame(5);
        $expectedLevel = 2;
        $drawLevelOne = $game->getMainBoard()->getDrawTiles()->get(1);
        $drawLevelOne->getTiles()->clear();
        //WHEN
        $drawTile = $this->tileGLMService->getActiveDrawTile($game);
        //THEN
        $this->assertSame($expectedLevel, $drawTile->getLevel());
    }

    public function testGetActiveDrawWhenLevelTwoIsEmptyButNotLevelOne() : void
    {
        //GIVEN
        $game = $this->createGame(5);
        $expectedLevel = 1;
        $drawLevelTwo = $game->getMainBoard()->getDrawTiles()->get(2);
        $drawLevelTwo->getTiles()->clear();
        //WHEN
        $drawTile = $this->tileGLMService->getActiveDrawTile($game);
        //THEN
        $this->assertSame($expectedLevel, $drawTile->getLevel());
    }

    public function testGetActiveDrawShouldBeThree() : void
    {
        //GIVEN
        $game = $this->createGame(5);
        $expectedLevel = 3;
        $drawLevelOne = $game->getMainBoard()->getDrawTiles()->get(1);
        $drawLevelOne->getTiles()->clear();
        $drawLevelTwo = $game->getMainBoard()->getDrawTiles()->get(2);
        $drawLevelTwo->getTiles()->clear();
        //WHEN
        $drawTile = $this->tileGLMService->getActiveDrawTile($game);
        //THEN
        $this->assertSame($expectedLevel, $drawTile->getLevel());
    }

    public function testGetActiveDrawShouldBeNull() : void
    {
        //GIVEN
        $game = $this->createGame(5);
        $drawLevelOne = $game->getMainBoard()->getDrawTiles()->get(1);
        $drawLevelOne->getTiles()->clear();
        $drawLevelTwo = $game->getMainBoard()->getDrawTiles()->get(2);
        $drawLevelTwo->getTiles()->clear();
        $drawLevelThree = $game->getMainBoard()->getDrawTiles()->get(3);
        $drawLevelThree->getTiles()->clear();
        //WHEN
        $drawTile = $this->tileGLMService->getActiveDrawTile($game);
        //THEN
        $this->assertNull($drawTile);
    }

    public function testGetActivableTilesWithTwoAdjacentTilesNotActivatedWithBonus() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $bonus = new TileActivationBonusGLM();
        $resource = new ResourceGLM();
        $bonus->setResource($resource);
        $tile->addActivationBonus($bonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerTile->setActivated(false);
        $playerTile2 = new PlayerTileGLM();
        $playerTile2->setTile($tile);
        $playerTile2->setActivated(false);
        $playerTile2->addAdjacentTile($playerTile, 0);
        $playerTile->addAdjacentTile($playerTile2, 0);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile2);
        $expectedResult = new ArrayCollection([$playerTile, $playerTile2]);
        //WHEN
        $result = $this->tileGLMService->getActivableTiles($playerTile2);
        //THEN
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetActivableTilesWithTwoAdjacentTilesFirstActivatedWithBonus() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $bonus = new TileActivationBonusGLM();
        $resource = new ResourceGLM();
        $bonus->setResource($resource);
        $tile->addActivationBonus($bonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerTile->setActivated(true);
        $playerTile2 = new PlayerTileGLM();
        $playerTile2->setTile($tile);
        $playerTile2->setActivated(false);
        $playerTile2->addAdjacentTile($playerTile, 0);
        $playerTile->addAdjacentTile($playerTile2, 0);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile2);
        $expectedResult = new ArrayCollection([$playerTile2]);
        //WHEN
        $result = $this->tileGLMService->getActivableTiles($playerTile2);
        //THEN
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetActivableTilesWithTwoAdjacentTilesNotActivatedNewHasNoBonus() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $bonus = new TileActivationBonusGLM();
        $resource = new ResourceGLM();
        $bonus->setResource($resource);
        $tile->addActivationBonus($bonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerTile->setActivated(false);
        $playerTile2 = new PlayerTileGLM();
        $tile2 = new TileGLM();
        $playerTile2->setTile($tile2);
        $playerTile2->setActivated(false);
        $playerTile2->addAdjacentTile($playerTile, 0);
        $playerTile->addAdjacentTile($playerTile2, 0);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile2);
        $expectedResult = new ArrayCollection([$playerTile]);
        //WHEN
        $result = $this->tileGLMService->getActivableTiles($playerTile2);
        //THEN
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetActivableTilesWithTilesNotAdjacentNotActivatedWithBonus() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $bonus = new TileActivationBonusGLM();
        $resource = new ResourceGLM();
        $bonus->setResource($resource);
        $tile->addActivationBonus($bonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerTile->setActivated(false);
        $playerTile2 = new PlayerTileGLM();
        $playerTile2->setTile($tile);
        $playerTile2->setActivated(false);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile2);
        $expectedResult = new ArrayCollection([$playerTile2]);
        //WHEN
        $result = $this->tileGLMService->getActivableTiles($playerTile2);
        //THEN
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetActivableTilesWithTilesAdjacentActivatedWithBonus() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $bonus = new TileActivationBonusGLM();
        $resource = new ResourceGLM();
        $bonus->setResource($resource);
        $tile->addActivationBonus($bonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerTile->setActivated(true);
        $playerTile2 = new PlayerTileGLM();
        $playerTile2->setTile($tile);
        $playerTile2->setActivated(true);
        $playerTile2->addAdjacentTile($playerTile, 0);
        $playerTile->addAdjacentTile($playerTile2, 0);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile2);
        //WHEN
        $result = $this->tileGLMService->getActivableTiles($playerTile2);
        //THEN
        $this->assertEmpty($result);
    }

    public function testGetMovementPointsWithTwoTilesOfOne() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        for ($i = 0; $i < 2; ++$i) {
            $playerTile = new PlayerTileGLM();
            $playerTile->setTile($tile);
            $resource = new ResourceGLM();
            $resource->setType(GlenmoreParameters::MOVEMENT_RESOURCE);
            $playerTileResource = new PlayerTileResourceGLM();
            $playerTileResource->setResource($resource);
            $playerTileResource->setPlayerTileGLM($playerTile);
            $playerTileResource->setQuantity(1);
            $playerTile->addPlayerTileResource($playerTileResource);
            $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
            $this->GLMService->endRoundOfPlayer($game, $firstPlayer, 0);
        }
        $expectedResult = 2;
        //WHEN
        $result = $this->tileGLMService->getMovementPoints($firstPlayer);
        //THEN
        $this->assertSame($expectedResult, $result);
    }

    public function testGetMovementPointsWithTwoTilesOfDifferent() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        for ($i = 1; $i <= 2; ++$i) {
            $tile = new TileGLM();
            $playerTile = new PlayerTileGLM();
            $playerTile->setTile($tile);
            $resource = new ResourceGLM();
            $resource->setType(GlenmoreParameters::MOVEMENT_RESOURCE);
            $playerTileResource = new PlayerTileResourceGLM();
            $playerTileResource->setResource($resource);
            $playerTileResource->setPlayerTileGLM($playerTile);
            $playerTileResource->setQuantity($i);
            $playerTile->addPlayerTileResource($playerTileResource);
            $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
            $this->GLMService->endRoundOfPlayer($game, $firstPlayer, 0);
        }
        $expectedResult = 3;
        //WHEN
        $result = $this->tileGLMService->getMovementPoints($firstPlayer);
        //THEN
        $this->assertSame($expectedResult, $result);
    }

    public function testGetMovementPointsWithTwoTilesOfDifferentAndTwoOthersEmpty() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        for ($i = 0; $i <= 2; ++$i) {
            $tile = new TileGLM();
            $playerTile = new PlayerTileGLM();
            $playerTile->setTile($tile);
            $resource = new ResourceGLM();
            $resource->setType(GlenmoreParameters::MOVEMENT_RESOURCE);
            $playerTileResource = new PlayerTileResourceGLM();
            $playerTileResource->setResource($resource);
            $playerTileResource->setPlayerTileGLM($playerTile);
            $playerTileResource->setQuantity($i);
            $playerTile->addPlayerTileResource($playerTileResource);
            $resource = new ResourceGLM();
            $resource->setType(GlenmoreParameters::WHISKY_RESOURCE);
            $playerTileResource = new PlayerTileResourceGLM();
            $playerTileResource->setResource($resource);
            $playerTileResource->setPlayerTileGLM($playerTile);
            $playerTileResource->setQuantity($i);
            $playerTile->addPlayerTileResource($playerTileResource);
            $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
            $this->GLMService->endRoundOfPlayer($game, $firstPlayer, 0);
        }
        $tile = new TileGLM();
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
        $expectedResult = 3;
        //WHEN
        $result = $this->tileGLMService->getMovementPoints($firstPlayer);
        //THEN
        $this->assertSame($expectedResult, $result);
    }

    public function testGetMovementPointsWithTwoTilesAdjacent() : void
    {
        //GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        for ($i = 1; $i <= 2; ++$i) {
            $tile = new TileGLM();
            $playerTile = new PlayerTileGLM();
            $playerTile->setTile($tile);
            $resource = new ResourceGLM();
            $resource->setType(GlenmoreParameters::MOVEMENT_RESOURCE);
            $playerTileResource = new PlayerTileResourceGLM();
            $playerTileResource->setResource($resource);
            $playerTileResource->setPlayerTileGLM($playerTile);
            $playerTileResource->setQuantity($i);
            $playerTile->addPlayerTileResource($playerTileResource);
            $resource = new ResourceGLM();
            $resource->setType(GlenmoreParameters::WHISKY_RESOURCE);
            $playerTileResource = new PlayerTileResourceGLM();
            $playerTileResource->setResource($resource);
            $playerTileResource->setPlayerTileGLM($playerTile);
            $playerTileResource->setQuantity($i);
            $playerTile->addPlayerTileResource($playerTileResource);
            if ($i == 2) {
                $playerTile->addAdjacentTile($firstPlayer->getPersonalBoard()->getPlayerTiles()->get(1), 0);
                $firstPlayer->getPersonalBoard()->getPlayerTiles()->get(1)->addAdjacentTile($playerTile, 4);
            }
            $firstPlayer->getPersonalBoard()->addPlayerTile($playerTile);
            $this->GLMService->endRoundOfPlayer($game, $firstPlayer, 0);
        }
        $expectedResult = 3;
        //WHEN
        $result = $this->tileGLMService->getMovementPoints($firstPlayer);
        //THEN
        $this->assertSame($expectedResult, $result);
    }

    public function testActivateTileWhenNotEnoughResources() : void
    {
        // GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $activationPrice = new TileActivationCostGLM();
        $resourcePrice = new ResourceGLM();
        $resourcePrice->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $resourcePrice->setColor(GlenmoreParameters::COLOR_GREEN);
        $activationPrice->setResource($resourcePrice);
        $activationPrice->setPrice(2);
        $tile->addActivationPrice($activationPrice);
        // THEN
        $this->expectException("Exception");
        // WHEN
        $collection = new ArrayCollection((array)$this->tileGLMService->getActivableTiles($firstPlayer->getPersonalBoard()->getPlayerTiles()->last()));
        $this->tileGLMService->activateBonus($playerTile, $firstPlayer, $collection);
    }

    public function testActivateTileWhenNotEnoughResourcesOfGoodType()
    {
        // GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $activationPrice = new TileActivationCostGLM();
        $resourcePrice = new ResourceGLM();
        $resourcePrice->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $resourcePrice->setColor(GlenmoreParameters::COLOR_GREEN);
        $activationPrice->setResource($resourcePrice);
        $activationPrice->setPrice(2);
        $tile->addActivationPrice($activationPrice);
        $playerResource = new ResourceGLM();
        $playerResource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $playerResource->setColor(GlenmoreParameters::COLOR_GREEN);
        $selectedResource = new SelectedResourceGLM();
        $selectedResource->setResource($playerResource);
        $selectedResource->setQuantity(1);
        $firstPlayer->getPersonalBoard()->addSelectedResource($selectedResource);
        // THEN
        $this->expectException("Exception");
        // WHEN
        $collection = new ArrayCollection((array)$this->tileGLMService->getActivableTiles($firstPlayer->getPersonalBoard()->getPlayerTiles()->last()));
        $this->tileGLMService->activateBonus($playerTile, $firstPlayer, $collection);
    }

    public function testActivateTileWhenEnoughResourcesButWrongType()
    {
        // GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $activationPrice = new TileActivationCostGLM();
        $resourcePrice = new ResourceGLM();
        $resourcePrice->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $resourcePrice->setColor(GlenmoreParameters::COLOR_GREEN);
        $activationPrice->setResource($resourcePrice);
        $activationPrice->setPrice(2);
        $tile->addActivationPrice($activationPrice);
        $playerResource = new ResourceGLM();
        $playerResource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $playerResource->setColor(GlenmoreParameters::COLOR_BROWN);
        $selectedResource = new SelectedResourceGLM();
        $selectedResource->setResource($playerResource);
        $selectedResource->setQuantity(3);
        $firstPlayer->getPersonalBoard()->addSelectedResource($selectedResource);
        // THEN
        $this->expectException("Exception");
        // WHEN
        $collection = new ArrayCollection((array)$this->tileGLMService->getActivableTiles($firstPlayer->getPersonalBoard()->getPlayerTiles()->last()));
        $this->tileGLMService->activateBonus($playerTile, $firstPlayer, $collection);
    }

    public function testActivateTileWhenTooMuchResourcesOnTile()
    {
        // GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $playerTile = new PlayerTileGLM();
        $resource = new ResourceGLM();
        $resource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $playerTileResource = new PlayerTileResourceGLM();
        $playerTileResource->setResource($resource);
        $playerTileResource->setQuantity(GlenmoreParameters::MAX_RESOURCES_PER_TILE + 1);
        $playerTile->addPlayerTileResource($playerTileResource);
        $playerTile->setTile($tile);
        $activationPrice = new TileActivationCostGLM();
        $resourcePrice = new ResourceGLM();
        $resourcePrice->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $resourcePrice->setColor(GlenmoreParameters::COLOR_GREEN);
        $activationPrice->setResource($resourcePrice);
        $activationPrice->setPrice(2);
        $tile->addActivationPrice($activationPrice);
        $playerResource = new ResourceGLM();
        $playerResource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $playerResource->setColor(GlenmoreParameters::COLOR_GREEN);
        $selectedResource = new SelectedResourceGLM();
        $selectedResource->setResource($playerResource);
        $selectedResource->setQuantity(3);
        $firstPlayer->getPersonalBoard()->addSelectedResource($selectedResource);
        // THEN
        $this->expectException("Exception");
        // WHEN
        $collection = new ArrayCollection((array)$this->tileGLMService->getActivableTiles($firstPlayer->getPersonalBoard()->getPlayerTiles()->last()));
        $this->tileGLMService->activateBonus($playerTile, $firstPlayer, $collection);
    }

   /* public function testActivateTileWhenTileNotOfTypeBrownAndNeedNoResources()
    {
        // GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $tile->setType(GlenmoreParameters::TILE_TYPE_GREEN);
        $tileBonus = new TileActivationBonusGLM();
        $bonusResource = new ResourceGLM();
        $bonusResource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $bonusResource->setColor(GlenmoreParameters::COLOR_GREEN);
        $tileBonus->setResource($bonusResource);
        $tileBonus->setAmount(1);
        $tile->addActivationBonus($tileBonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerResource = new ResourceGLM();
        $playerResource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $playerResource->setColor(GlenmoreParameters::COLOR_BROWN);
        $selectedResource = new SelectedResourceGLM();
        $selectedResource->setResource($playerResource);
        $selectedResource->setQuantity(3);
        $firstPlayer->getPersonalBoard()->addSelectedResource($selectedResource);
        $resourceToTest = new PlayerTileResourceGLM();
        $resourceToTest->setResource($bonusResource);
        // WHEN
        $collection = new ArrayCollection((array)$this->tileGLMService->getActivableTiles($firstPlayer->getPersonalBoard()->getPlayerTiles()->last()));
        $this->tileGLMService->activateBonus($playerTile, $firstPlayer, $collection);
        // THEN
        $resourceArray = new ArrayCollection();
        foreach ($playerTile->getPlayerTileResource() as $resource){
            $resourceArray->add($resource->getResource());
        }
        $this->assertContainsEquals($bonusResource ,$resourceArray);
        $this->assertTrue($playerTile->isActivated());
    }

    public function testActivateTileWhenTileNotOfTypeBrownAndNeedResources()
    {
        // GIVEN
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $tile->setType(GlenmoreParameters::TILE_TYPE_GREEN);
        $activationPrice = new TileActivationCostGLM();
        $resourcePrice = new ResourceGLM();
        $resourcePrice->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $resourcePrice->setColor(GlenmoreParameters::COLOR_GREEN);
        $activationPrice->setResource($resourcePrice);
        $activationPrice->setPrice(2);
        $tile->addActivationPrice($activationPrice);
        $tileBonus = new TileActivationBonusGLM();
        $bonusResource = new ResourceGLM();
        $bonusResource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $bonusResource->setColor(GlenmoreParameters::COLOR_GREEN);
        $tileBonus->setResource($bonusResource);
        $tileBonus->setAmount(1);
        $tile->addActivationBonus($tileBonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerResource = new ResourceGLM();
        $playerResource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $playerResource->setColor(GlenmoreParameters::COLOR_GREEN);
        $selectedResource = new SelectedResourceGLM();
        $selectedResource->setResource($playerResource);
        $selectedResource->setQuantity(3);
        $firstPlayer->getPersonalBoard()->addSelectedResource($selectedResource);
        $resourceToTest = new PlayerTileResourceGLM();
        $resourceToTest->setResource($bonusResource);
        $expectedSelectedResourceCount = $selectedResource->getQuantity() - $activationPrice->getPrice();
        // WHEN
        $collection = new ArrayCollection((array)$this->tileGLMService->getActivableTiles($firstPlayer->getPersonalBoard()->getPlayerTiles()->last()));
        $this->tileGLMService->activateBonus($playerTile, $firstPlayer, $collection);
        // THEN
        $resourceArray = new ArrayCollection();
        foreach ($playerTile->getPlayerTileResource() as $resource){
            $resourceArray->add($resource->getResource());
        }
        $this->assertContainsEquals($bonusResource ,$resourceArray);
        $this->assertEquals($expectedSelectedResourceCount, $selectedResource->getQuantity());
        $this->assertTrue($playerTile->isActivated());
    }

    public function testActivateTileWhenTileOfTypeBrownWithOneCase()
    {
        // GIVEN
        $pointsToGive = 5;
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $tile->setType(GlenmoreParameters::TILE_TYPE_BROWN);
        $activationPrice = new TileActivationCostGLM();
        $resourcePrice = new ResourceGLM();
        $resourcePrice->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $resourcePrice->setColor(GlenmoreParameters::COLOR_GREEN);
        $activationPrice->setResource($resourcePrice);
        $activationPrice->setPrice(2);
        $tile->addActivationPrice($activationPrice);
        $tileBonus = new TileActivationBonusGLM();
        $bonusResource = new ResourceGLM();
        $bonusResource->setType(GlenmoreParameters::POINT_RESOURCE);
        $bonusResource->setColor(GlenmoreParameters::COLOR_GREEN);
        $tileBonus->setResource($bonusResource);
        $tileBonus->setAmount($pointsToGive);
        $tile->addActivationBonus($tileBonus);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerResource = new ResourceGLM();
        $playerResource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $playerResource->setColor(GlenmoreParameters::COLOR_GREEN);
        $selectedResource = new SelectedResourceGLM();
        $selectedResource->setResource($playerResource);
        $selectedResource->setQuantity(3);
        $firstPlayer->getPersonalBoard()->addSelectedResource($selectedResource);
        $resourceToTest = new PlayerTileResourceGLM();
        $resourceToTest->setResource($bonusResource);
        $expectedPoints = $pointsToGive;
        // WHEN
        $collection = new ArrayCollection((array)$this->tileGLMService->getActivableTiles($firstPlayer->getPersonalBoard()->getPlayerTiles()->last()));
        $this->tileGLMService->activateBonus($playerTile, $firstPlayer, $collection);
        // THEN
        $this->assertEquals($expectedPoints, $firstPlayer->getScore());
        $this->assertTrue($playerTile->isActivated());
    }

    public function testActivateTileWhenTileOfTypeBrownWithMultipleCase()
    {
        // GIVEN
        $pointsToGive1 = 3;
        $pointsToGive2 = 5;
        $game = $this->createGame(2);
        $firstPlayer = $game->getPlayers()->first();
        $tile = new TileGLM();
        $tile->setType(GlenmoreParameters::TILE_TYPE_BROWN);
        $activationPrice1 = new TileActivationCostGLM();
        $resourcePrice1 = new ResourceGLM();
        $resourcePrice1->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $resourcePrice1->setColor(GlenmoreParameters::COLOR_GREEN);
        $activationPrice1->setResource($resourcePrice1);
        $activationPrice1->setPrice(0);
        $tile->addActivationPrice($activationPrice1);
        $activationPrice2 = new TileActivationCostGLM();
        $resourcePrice2 = new ResourceGLM();
        $resourcePrice2->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $resourcePrice2->setColor(GlenmoreParameters::COLOR_GREEN);
        $activationPrice2->setResource($resourcePrice2);
        $activationPrice2->setPrice(0);
        $tile->addActivationPrice($activationPrice2);
        $tileBonus = new TileActivationBonusGLM();
        $bonusResource = new ResourceGLM();
        $bonusResource->setType(GlenmoreParameters::POINT_RESOURCE);
        $bonusResource->setColor(GlenmoreParameters::COLOR_GREEN);
        $tileBonus->setResource($bonusResource);
        $tileBonus->setAmount($pointsToGive1);
        $tile->addActivationBonus($tileBonus);
        $tileBonus2 = new TileActivationBonusGLM();
        $bonusResource2 = new ResourceGLM();
        $bonusResource2->setType(GlenmoreParameters::POINT_RESOURCE);
        $bonusResource2->setColor(GlenmoreParameters::COLOR_GREEN);
        $tileBonus2->setResource($bonusResource2);
        $tileBonus2->setAmount($pointsToGive2);
        $tile->addActivationBonus($tileBonus2);
        $playerTile = new PlayerTileGLM();
        $playerTile->setTile($tile);
        $playerResource = new ResourceGLM();
        $playerResource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $playerResource->setColor(GlenmoreParameters::COLOR_GREEN);
        $selectedResource = new SelectedResourceGLM();
        $selectedResource->setResource($playerResource);
        $selectedResource->setQuantity(1);
        $firstPlayer->getPersonalBoard()->addSelectedResource($selectedResource);
        $playerResource2 = new ResourceGLM();
        $playerResource2->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
        $playerResource2->setColor(GlenmoreParameters::COLOR_BROWN);
        $selectedResource2 = new SelectedResourceGLM();
        $selectedResource2->setResource($playerResource2);
        $selectedResource2->setQuantity(1);
        $firstPlayer->getPersonalBoard()->addSelectedResource($selectedResource2);
        $expectedPoints = $pointsToGive2;
        // WHEN
        $collection = new ArrayCollection((array)$this->tileGLMService->getActivableTiles($firstPlayer->getPersonalBoard()->getPlayerTiles()->last()));
        $this->tileGLMService->activateBonus($playerTile, $firstPlayer, $collection);
        // THEN
        $this->assertEquals($expectedPoints, $firstPlayer->getScore());
        $this->assertTrue($playerTile->isActivated());
    }*/

    private function createGame(int $nbOfPlayers): GameGLM
    {
        $game = new GameGLM();
        $game->setGameName(AbstractGameManagerService::GLM_LABEL);
        $mainBoard = new MainBoardGLM();
        $mainBoard->setGameGLM($game);

        for ($i = GlenmoreParameters::TILE_LEVEL_ZERO; $i <= GlenmoreParameters::TILE_LEVEL_THREE; ++$i) {
            $draw = new DrawTilesGLM();
            $draw->setLevel($i);
            $draw->setMainBoardGLM($mainBoard);
            for ($j = 1; $j <= 8; ++$j) {
                $tile = new TileGLM();
                $tile->setLevel($j);
                if ($j % 5 == 0) {
                    $tile->setType(GlenmoreParameters::TILE_TYPE_GREEN);
                    $tile->setName(GlenmoreParameters::TILE_NAME_FOREST);
                } else if ($j % 5 == 1) {
                    $tile->setType(GlenmoreParameters::TILE_TYPE_YELLOW);
                    $tile->setName(GlenmoreParameters::TILE_NAME_FIELD);
                } else if ($j % 5 == 2) {
                    $tile->setType(GlenmoreParameters::TILE_TYPE_BROWN);
                    $tile->setName(GlenmoreParameters::TILE_NAME_CATTLE);
                } else if ($j % 5 == 3) {
                    $tile->setType(GlenmoreParameters::TILE_TYPE_GREEN);
                    $tile->setName(GlenmoreParameters::TILE_NAME_PASTURE);
                } else if ($j % 5 == 4) {
                    $tile->setType(GlenmoreParameters::TILE_TYPE_VILLAGE);
                    $tile->setName(GlenmoreParameters::TILE_NAME_VILLAGE);
                }
                $draw->addTile($tile);
            }
            $mainBoard->addDrawTile($draw);
            $warehouse = new WarehouseGLM();
            $array = [GlenmoreParameters::COLOR_BROWN, GlenmoreParameters::COLOR_GREEN, GlenmoreParameters::COLOR_WHITE,
                GlenmoreParameters::COLOR_YELLOW, GlenmoreParameters::COLOR_GREY];
            for ($j = 0; $j < 5; ++$j) {
                $warehouseLine = new WarehouseLineGLM();
                $resource = new ResourceGLM();
                $resource->setColor($array[$j]);
                $resource->setType(GlenmoreParameters::PRODUCTION_RESOURCE);
                $warehouseLine->setResource($resource);
                $warehouseLine->setQuantity(0);
                $warehouse->addWarehouseLine($warehouseLine);
            }
            $warehouse->setMainBoardGLM($mainBoard);
        }
        for ($i = 0; $i < $nbOfPlayers; $i++) {
            $player = new PlayerGLM('test', $game);
            $player->setGameGLM($game);
            $player->setTurnOfPlayer(false);
            $player->setScore(0);
            $game->addPlayer($player);
            $personalBoard = new PersonalBoardGLM();
            $player->setPersonalBoard($personalBoard);
            $personalBoard->setPlayerGLM($player);
            $personalBoard->setLeaderCount(0);
            $personalBoard->setMoney(GlenmoreParameters::START_MONEY);
            $pawn = new PawnGLM();
            $pawn->setColor(GlenmoreParameters::COLOR_FROM_POSITION[$i]);
            $pawn->setPosition($i);
            $pawn->setMainBoardGLM($mainBoard);
            $player->setPawn($pawn);
            $playerTile = new PlayerTileGLM();
            $startTile = new TileGLM();
            $startTile->setName(GlenmoreParameters::TILE_NAME_START_VILLAGE);
            $startTile->setType(GlenmoreParameters::TILE_TYPE_VILLAGE);
            $startTile->setContainingRiver(true);
            $startTile->setContainingRoad(true);
            $playerTile->setTile($startTile);
            $playerTile->setPersonalBoard($personalBoard);
            $playerTile->setCoordX(0);
            $playerTile->setCoordY(0);
            $playerTileResource = new PlayerTileResourceGLM();
            $playerTileResource->setPlayerTileGLM($playerTile);
            $playerTileResource->setPlayer($player);
            $villager = new ResourceGLM();
            $villager->setType(GlenmoreParameters::VILLAGER_RESOURCE);
            $playerTileResource->setResource($villager);
            $playerTileResource->setQuantity(1);
            $playerTile->addPlayerTileResource($playerTileResource);
            $personalBoard->addPlayerTile($playerTile);
        }

        for ($i = $nbOfPlayers; $i < GlenmoreParameters::NUMBER_OF_BOXES_ON_BOARD; ++$i) {
            $drawTiles = $mainBoard->getDrawTiles();
            $level = 0;
            for ($j = GlenmoreParameters::TILE_LEVEL_ZERO; $j <= GlenmoreParameters::TILE_LEVEL_THREE; ++$j) {
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
            $mainBoard->addBoardTile($mainBoardTile);
            $draw->removeTile($tile);
        }
        $firstPlayer = $game->getPlayers()->first();
        $firstPlayer->setTurnOfPlayer(true);
        return $game;
    }
}
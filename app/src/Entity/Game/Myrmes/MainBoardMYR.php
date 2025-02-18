<?php

namespace App\Entity\Game\Myrmes;

use App\Entity\Game\DTO\Component;
use App\Repository\Game\Myrmes\MainBoardMYRRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MainBoardMYRRepository::class)]
class MainBoardMYR extends Component
{
    #[ORM\Column]
    private ?int $yearNum = null;

    #[ORM\OneToOne(inversedBy: 'mainBoardMYR', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?GameMYR $game = null;

    #[ORM\OneToMany(targetEntity: GardenWorkerMYR::class, mappedBy: 'mainBoardMYR', orphanRemoval: true)]
    private Collection $gardenWorkers;

    #[ORM\OneToMany(targetEntity: PreyMYR::class, mappedBy: 'mainBoardMYR')]
    private Collection $preys;

    #[ORM\ManyToMany(targetEntity: TileMYR::class)]
    private Collection $tiles;

    #[ORM\OneToMany(targetEntity: AnthillHoleMYR::class, mappedBy: 'mainBoardMYR', orphanRemoval: true)]
    private Collection $anthillHoles;

    #[ORM\OneToMany(targetEntity: SeasonMYR::class, mappedBy: 'mainBoard', orphanRemoval: true)]
    private Collection $seasons;

    #[ORM\OneToMany(targetEntity: GameGoalMYR::class, mappedBy: 'mainBoardLevelOne')]
    private Collection $gameGoalsLevelOne;

    #[ORM\OneToMany(targetEntity: GameGoalMYR::class, mappedBy: 'mainBoardLevelTwo')]
    private Collection $gameGoalsLevelTwo;

    #[ORM\OneToMany(targetEntity: GameGoalMYR::class, mappedBy: 'mainBoardLevelThree')]
    private Collection $gameGoalsLevelThree;

    public function __construct()
    {
        $this->gardenWorkers = new ArrayCollection();
        $this->preys = new ArrayCollection();
        $this->tiles = new ArrayCollection();
        $this->anthillHoles = new ArrayCollection();
        $this->seasons = new ArrayCollection();
        $this->gameGoalsLevelOne = new ArrayCollection();
        $this->gameGoalsLevelTwo = new ArrayCollection();
        $this->gameGoalsLevelThree = new ArrayCollection();
    }

    public function getYearNum(): ?int
    {
        return $this->yearNum;
    }

    public function setYearNum(int $yearNum): static
    {
        $this->yearNum = $yearNum;

        return $this;
    }

    public function getGame(): ?GameMYR
    {
        return $this->game;
    }

    public function setGame(GameMYR $game): static
    {
        $this->game = $game;

        return $this;
    }

    /**
     * @return Collection<int, GardenWorkerMYR>
     */
    public function getGardenWorkers(): Collection
    {
        return $this->gardenWorkers;
    }

    public function addGardenWorker(GardenWorkerMYR $gardenWorker): static
    {
        if (!$this->gardenWorkers->contains($gardenWorker)) {
            $this->gardenWorkers->add($gardenWorker);
            $gardenWorker->setMainBoardMYR($this);
        }

        return $this;
    }

    public function removeGardenWorker(GardenWorkerMYR $gardenWorker): static
    {
        if ($this->gardenWorkers->removeElement($gardenWorker)
            && $gardenWorker->getMainBoardMYR() === $this) {
            $gardenWorker->setMainBoardMYR(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, PreyMYR>
     */
    public function getPreys(): Collection
    {
        return $this->preys;
    }

    public function addPrey(PreyMYR $prey): static
    {
        if (!$this->preys->contains($prey)) {
            $this->preys->add($prey);
            $prey->setMainBoardMYR($this);
        }

        return $this;
    }

    public function removePrey(PreyMYR $prey): static
    {
        if ($this->preys->removeElement($prey) && $prey->getMainBoardMYR() === $this) {
            $prey->setMainBoardMYR(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, TileMYR>
     */
    public function getTiles(): Collection
    {
        return $this->tiles;
    }

    public function addTile(TileMYR $tile): static
    {
        if (!$this->tiles->contains($tile)) {
            $this->tiles->add($tile);
        }

        return $this;
    }

    public function removeTile(TileMYR $tile): static
    {
        $this->tiles->removeElement($tile);

        return $this;
    }

    /**
     * @return Collection<int, AnthillHoleMYR>
     */
    public function getAnthillHoles(): Collection
    {
        return $this->anthillHoles;
    }

    public function addAnthillHole(AnthillHoleMYR $anthillHole): static
    {
        if (!$this->anthillHoles->contains($anthillHole)) {
            $this->anthillHoles->add($anthillHole);
            $anthillHole->setMainBoardMYR($this);
        }

        return $this;
    }

    public function removeAnthillHole(AnthillHoleMYR $anthillHole): static
    {
        if ($this->anthillHoles->removeElement($anthillHole) && $anthillHole->getMainBoardMYR() === $this) {
            $anthillHole->setMainBoardMYR(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, SeasonMYR>
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    public function addSeason(SeasonMYR $season): static
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons->add($season);
            $season->setMainBoard($this);
        }

        return $this;
    }

    public function removeSeason(SeasonMYR $season): static
    {
        if ($this->seasons->removeElement($season) && $season->getMainBoard() === $this) {
            $season->setMainBoard(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, GameGoalMYR>
     */
    public function getGameGoalsLevelOne(): Collection
    {
        return $this->gameGoalsLevelOne;
    }

    public function addGameGoalsLevelOne(GameGoalMYR $gameGoalsLevelOne): static
    {
        if (!$this->gameGoalsLevelOne->contains($gameGoalsLevelOne)) {
            $this->gameGoalsLevelOne->add($gameGoalsLevelOne);
            $gameGoalsLevelOne->setMainBoardLevelOne($this);
        }

        return $this;
    }

    public function removeGameGoalsLevelOne(GameGoalMYR $gameGoalsLevelOne): static
    {
        if ($this->gameGoalsLevelOne->removeElement($gameGoalsLevelOne)
            && $gameGoalsLevelOne->getMainBoardLevelOne() === $this) {
            $gameGoalsLevelOne->setMainBoardLevelOne(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, GameGoalMYR>
     */
    public function getGameGoalsLevelTwo(): Collection
    {
        return $this->gameGoalsLevelTwo;
    }

    public function addGameGoalsLevelTwo(GameGoalMYR $gameGoalsLevelTwo): static
    {
        if (!$this->gameGoalsLevelTwo->contains($gameGoalsLevelTwo)) {
            $this->gameGoalsLevelTwo->add($gameGoalsLevelTwo);
            $gameGoalsLevelTwo->setMainBoardLevelTwo($this);
        }

        return $this;
    }

    public function removeGameGoalsLevelTwo(GameGoalMYR $gameGoalsLevelTwo): static
    {
        if ($this->gameGoalsLevelTwo->removeElement($gameGoalsLevelTwo)
            && $gameGoalsLevelTwo->getMainBoardLevelTwo() === $this) {
            $gameGoalsLevelTwo->setMainBoardLevelTwo(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, GameGoalMYR>
     */
    public function getGameGoalsLevelThree(): Collection
    {
        return $this->gameGoalsLevelThree;
    }

    public function addGameGoalsLevelThree(GameGoalMYR $gameGoalsLevelThree): static
    {
        if (!$this->gameGoalsLevelThree->contains($gameGoalsLevelThree)) {
            $this->gameGoalsLevelThree->add($gameGoalsLevelThree);
            $gameGoalsLevelThree->setMainBoardLevelThree($this);
        }

        return $this;
    }

    public function removeGameGoalsLevelThree(GameGoalMYR $gameGoalsLevelThree): static
    {
        if ($this->gameGoalsLevelThree->removeElement($gameGoalsLevelThree)
            && $gameGoalsLevelThree->getMainBoardLevelThree() === $this) {
            $gameGoalsLevelThree->setMainBoardLevelThree(null);
        }

        return $this;
    }
}

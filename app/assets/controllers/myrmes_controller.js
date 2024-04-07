import { Controller } from '@hotwired/stimulus';

export default class extends Controller  {

    //place nurse

    async placeNurseOnLarvaeTrack(position) {
        let url = position.params.url;
        await fetch(url);
    }

    async placeNurseOnSoldiersTrack(position) {
        let url = position.params.url;
        await fetch(url);
    }

    async placeNurseOnWorkersTrack(position) {
        let url = position.params.url;
        await fetch(url);
    }

    async placeNurseOnWorkshop(position) {
        let url = position.params.url;
        await fetch(url);
    }

    async confirmNursesPlacement(confirm) {
        let url = confirm.params.url;
        await fetch(url);
    }

    async cancelNursesPlacement(confirm) {
        let url = confirm.params.url;
        await fetch(url);
    }

    //move on event track

    async moveToLeftOnEventTrack(event) {
        let url = event.params.url;
        await fetch(url);
    }

    async moveToRightOnEventTrack(event) {
        let url = event.params.url;
        await fetch(url);
    }

    async confirmBonus(event) {
        let url = event.params.url;
        await fetch(url);
    }

    //throw resources from storage

    async selectPlayerResourceToThrow(playerResource) {
        let url = playerResource.params.url;
        const response = await fetch(url);
        let tree = document.getElementById("index_myrmes");
        let placeholder = document.createElement("div");
        placeholder.innerHTML = await response.text();
        const node = placeholder.firstElementChild;
        tree.appendChild(node);
    }

    async throwResourceFromWarehouse(resource) {
        let url = resource.params.url;
        await fetch(url);
    }

    //place worker on colony level track

    async placeWorkerOnColonyLevelTrack(level) {
        let url = level.params.url;
        await fetch(url);
    }

    //harvest a resource

    async harvestResource(resource){
        let url = resource.params.url;
        await fetch(url)
    }

    async endHarvestPhase(endingPhase) {
        let url = endingPhase.params.url;
        await fetch(url)
    }

    // workshop actions

    async choseAnthillHolePlacement(placement) {
        let url = placement.params.url;
        await fetch(url);
    }

    async cancelAnthillHolePlacement(placement) {
        alert("Ouvrir menu de l'atelier");
    }

    // dynamic display

    async showPersonalBoard(main)  {
        let url = main.params.url;
        const response = await fetch(url);
        let tree = document.getElementById("index_myrmes");
        let placeholder = document.createElement("div");
        placeholder.innerHTML = await response.text();
        const node = placeholder.firstElementChild;
        tree.appendChild(node);
    }

    async displayBoxActions(boardBox) {
        closeSelectedBoxWindow();
        let url = boardBox.params.url
        const response = await fetch(url);
        let tree = document.getElementById("index_myrmes");
        let placeholder = document.createElement("div");
        placeholder.innerHTML = await response.text();
        const node = placeholder.firstElementChild;
        tree.appendChild(node);
    }

    async displayPlayerPersonalBoard(board) {
        let url = board.params.url;
        let open = board.params.open;
        if (open) {
            const response = await fetch(url);
            document.getElementById('playerPersonalBoard').innerHTML = await response.text();
        }

        await this.togglePlayerPersonalBoard(open);
    }

    async togglePlayerPersonalBoard(open) {
        const openedPlayerPersonalBoard = document.getElementById("openedPlayerPersonalBoard");
        console.log(openedPlayerPersonalBoard)
        const Timing = {
            duration: 750,
            iterations: 1,
        }
        if (open) {
            const hidden = document.createAttribute("hidden");
            openedPlayerPersonalBoard.removeAttribute("hidden");
            const openingSliding = [
                {transform: "translateY(60rem)"},
                {transform: "translateY(0rem)"}
            ]
            openedPlayerPersonalBoard.animate(openingSliding, Timing);

            let personalBoard = document.getElementById('persoBoard');
            personalBoard.scrollTop = personalBoard.scrollHeight;
        } else {
            const hidden = document.createAttribute("hidden");
            openedPlayerPersonalBoard.animate(
                [
                    {transform: "translateY(0rem)"},
                    {transform: "translateY(60rem)"},
                ],
                {
                    duration: Timing.duration,
                    fill: "forwards",
                }
            ).addEventListener("finish",
                () => openedPlayerPersonalBoard.setAttributeNode(hidden));
        }
    }
}
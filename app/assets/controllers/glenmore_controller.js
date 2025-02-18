import {Controller} from '@hotwired/stimulus';
import PersonalBoard from "../scripts/Glenmore/PersonalBoard.js";

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {

	togglePersonalBoard(isOpening) {
		let open = isOpening.params.open;
		PersonalBoard.togglePersonalBoard(open);
	}

	async displayPropertyCards(board) {
		let url = board.params.url;
		const response = await fetch(url);
		let tree = document.getElementById("index_glenmore");
		let placeholder = document.createElement("div");
		placeholder.innerHTML = await response.text();
		const node = placeholder.firstElementChild;
		tree.appendChild(node);
	}

	async selectTile(tile) {
		let url = tile.params.url;
		const response = await fetch(url);
		let tree = document.getElementById("index_glenmore");
		let placeholder = document.createElement("div");
		placeholder.innerHTML = await response.text();
		const node = placeholder.firstElementChild;
		tree.appendChild(node);
	}

	async selectResource(resource) {
		let url = resource.params.url;
		await fetch(url);
	}

	async activateTile(tile) {
		let url = tile.params.url;
		document.getElementById("returnArrow").click();
		await fetch(url);
	}

	async removeVillager(tile) {
		await this.fetchUrlAndCloseMenu(tile.params.url)
	}

	async moveVillager(tile) {
		await this.fetchUrlAndCloseMenu(tile.params.url)
	}

	async validateNewResourcesAcquisition(tile) {
		let url = tile.params.url;
		await fetch(url);
	}

	async cancelNewResourcesAcquisition(tile) {
		let url = tile.params.url;
		await fetch(url);
	}

	async validateResourcesSelection(tile) {
		let url = tile.params.url;
		await fetch(url);
	}

	async cancelResourcesSelection(tile) {
		let url = tile.params.url;
		await fetch(url);
	}

	async cancelBuyingTile(tile) {
		let url = tile.params.url;
		await fetch(url);
	}

	async cancelActivatingTile(tile) {
		let url = tile.params.url;
		await fetch(url);
	}

	async endRoundPlayer(player) {
		let url = player.params.url;
		await fetch(url);
	}

	async endActivationTilesPhase(player) {
		let url = player.params.url;
		await fetch(url);
	}

    async buyTile(tile) {
        let url = tile.params.url;
        const response = await fetch(url);
        if (response.status === 200) {
            closeWindow();
        }
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

	async putTileInPersonalBoard(tile) {
		let url = tile.params.url;
		await fetch(url);
	}

	async togglePlayerPersonalBoard(open) {
		const openedPlayerPersonalBoard = document.getElementById("openedPlayerPersonalBoard");
		const Timing = {
			duration: 750,
			iterations: 1,
		}
		if (open) {
			openedPlayerPersonalBoard.removeAttribute("hidden");
			const openingSliding = [
				{transform: "translateY(60rem)"},
				{transform: "translateY(0rem)"}
			]
			openedPlayerPersonalBoard.animate(openingSliding, Timing);

			let personalBoard = document.getElementById('tileBoard');
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

    async selectResourceWarehouseProductionOnMainBoard(resourceLine) {
        let url = resourceLine.params.url;
		await this.fetchUrlAndInsertResponseInIndex(url)
    }

    async selectMoneyWarehouseProductionOnMainBoard(resourceLine) {
        let url = resourceLine.params.url;
		await this.fetchUrlAndInsertResponseInIndex(url)
    }

    async buyResourceFromWarehouse(resourceLine) {
        let url = resourceLine.params.url;
        await this.fetchUrlAndCloseWarehouseMenu(url)
    }

    async sellResourceFromWarehouse(resourceLine) {
        let url = resourceLine.params.url;
		await this.fetchUrlAndCloseWarehouseMenu(url)
    }

	async showMainBoard(main)  {
		let url = main.params.url;
		const response = await fetch(url);
		let tree = document.getElementById("index_glenmore");
		let placeholder = document.createElement("div");
		placeholder.innerHTML = await response.text();
		const node = placeholder.firstElementChild;
		tree.appendChild(node);
	}

	async selectNewResourceAcquisition(resource) {
		let url = resource.params.url;
		await fetch(url);
	}

	async fetchUrlAndCloseMenu(url) {
		const response = await fetch(url);
		if (response.status === 200) {
			closeSelectedTileWindow();
		}
	}

	async fetchUrlAndInsertResponseInIndex(url) {
		const response = await fetch(url);
		let tree = document.getElementById("index_glenmore");
		let placeholder = document.createElement("div");
		placeholder.innerHTML = await response.text();
		const node = placeholder.firstElementChild;
		tree.appendChild(node);
	}

	async fetchUrlAndCloseWarehouseMenu(url) {
		const response = await fetch(url);
		if (response.status === 200) {
			closeSelectedWarehouseResource();
		}
	}
}
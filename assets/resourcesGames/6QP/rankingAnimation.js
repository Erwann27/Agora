/*
    function updateElapsedTime() {
        const currentTime = Math.floor(Date.now() / 1000);
        const elapsedTime = currentTime - {{ createdAt }};

        const hours = Math.floor(elapsedTime / 3600);
        const minutes = Math.floor((elapsedTime % 3600) / 60);
        const seconds = elapsedTime % 60;

        document.getElementById('elapsedTime').innerText = hours.toString().padStart(2, '0') + ':' +
            minutes.toString().padStart(2, '0') + ':' +
            seconds.toString().padStart(2, '0');
    }

    const clockID = setInterval(updateElapsedTime, 1000);
*/
function updateUserScore(player) {
    let scoreElement = document.getElementById(player[0]);
    if (scoreElement) {
        let landscapeScore = document.getElementById('l_' + player[0] + '_points');
        landscapeScore.dataset.score = player[1];
        if (landscapeScore) {
            landscapeScore.getElementsByTagName('p').item(0).innerText = player[1];
            if (player[1] > 1) {
                landscapeScore.getElementsByTagName('p').item(1).innerText = "points";
            }
        }


        let portraitScore = document.getElementById('p_' + player[0] + '_points');
        if (portraitScore) {
            portraitScore.getElementsByTagName('p').item(0).innerText = player[1];
            if (player[1] > 1) {
                portraitScore.getElementsByTagName('p').item(1).innerText = 'points';
            }
        }
    }
    updateLeaderboard();
}

function updateLeaderboard() {
    let leaderboardContainer = document.getElementById('leaderboard');
    if (leaderboardContainer) {
        let leaderboardItems = Array.from(leaderboardContainer.children);
        let positionMap = new Map();
        let newPositionMap = new Map();

        leaderboardItems.forEach((item) =>
            positionMap.set(item.dataset.origin, item.getBoundingClientRect()));

        leaderboardItems.sort(function (a, b) {
            let scoreA = parseInt(document.getElementById('l_' + a.id + '_points').dataset.score);
            let scoreB = parseInt(document.getElementById('l_' + b.id + '_points').dataset.score);
            return scoreA - scoreB;
        });


        leaderboardItems.forEach((item, index) => {
            newPositionMap.set(item, index);
            item.dataset.origin = index.toString();
        });
        applyScoresStyle(leaderboardItems);
        animateLeaderboard(leaderboardItems, positionMap, newPositionMap)



    }
}

function animateLeaderboard(leaderboardItems, positionMap, newPositionMap) {

    leaderboardItems.forEach((item) => {
        let initialPosition = item.getBoundingClientRect();
        let finalPosition = positionMap.get(newPositionMap.get(item).toString());

        let dy = finalPosition.y - initialPosition.y + parseInt(item.dataset.origingap);
        item.dataset.origingap = dy.toString()

        // Applique du CSS pour translater l'élément sur l'axe Y
        item.style.transition = 'transform 1s ease-out';
        item.style.transform = `translateY(${dy}px)`;

        positionMap.set(item, finalPosition);
    });
}

function applyScoresStyle(leaderboardItems) {
    let first = document.getElementById(
        'l_' + leaderboardItems[0].id + '_points').dataset.score;
    let last = document.getElementById('l_' +
        leaderboardItems[leaderboardItems.length - 1].id + '_points').dataset.score;

    leaderboardItems.forEach((item) => {
        if (document.getElementById('l_' + item.id + '_points').dataset.score === last) {
            item.classList.remove('score-white', 'score-gold');
            item.classList.add('score-red');
        } else if (document.getElementById('l_' + item.id + '_points').dataset.score === first) {
            item.classList.remove('score-red', 'score-white');
            item.classList.add('score-gold');
        } else {
            item.classList.remove('score-red', 'score-gold');
            item.classList.add('score-white');
        }

    });
}

function resetRankingOrder() {
    let leaderboardContainer = document.getElementById('leaderboard');
    if (leaderboardContainer) {
        let leaderboardItems = Array.from(leaderboardContainer.children);
        leaderboardItems.sort(function (a, b) {
            let scoreA = parseInt(document.getElementById('l_' + a.id + '_points').dataset.score);
            let scoreB = parseInt(document.getElementById('l_' + b.id + '_points').dataset.score);
            return scoreA - scoreB;
        });


        leaderboardItems.forEach((item, index) => {
            leaderboardContainer.removeChild(item);
            item.style.transform = `translateY(0px)`;
            item.dataset.origingap = "0";
            item.dataset.origin = index.toString();

        });
        leaderboardItems.forEach(item => leaderboardContainer.appendChild(item));

    }
}



let waitingAnimationList = [];

function actionAnimation(anim) {
    return new Promise(resolve => {
        if(anim) {
            waitingAnimationList.push(anim);
        }
        else {
            waitingAnimationList.shift();
        }
        resolve();
    })
}



async function placeChosenCardsAnim(cards) {
    animationContainer.classList.remove('hidden');
    console.log(cards);
    for (const card of cards.split(' ')) {
        console.log(card);
        await moveChosenCard(card.id);
    }
    animationContainer.classList.add('hidden');
}

function moveChosenCard(cardId) {
    return new Promise(resolve => {
        let cardFinalPositionElement = document.getElementById('image_' + cardId);
        let cardElementInChosenCard = document.getElementById(cardId).firstElementChild;

        let chosenCardShape = cardElementInChosenCard.getBoundingClientRect();
        let cardFinalPositionShape = cardFinalPositionElement.getBoundingClientRect();
        console.log(chosenCardShape)
        console.log(cardFinalPositionShape)

        let movingCardElement = cardElementInChosenCard.cloneNode(true);
        movingCardElement.id = 'movingcard_' + cardId;

        movingCardElement.classList.add('absolute');
        animationContainer.appendChild(movingCardElement);
        movingCardElement.height = chosenCardShape.height;
        movingCardElement.width = chosenCardShape.width;
        movingCardElement.animate(
            [
                {
                    transform: "translate(" + chosenCardShape.x + "px, " + chosenCardShape.y + "px)",
                    width: chosenCardShape.width + "px",
                    height: chosenCardShape.height + "px",
                },
                {width: chosenCardShape.width * 1.5 + "px", height: chosenCardShape.height * 1.5 + "px"},
                {
                    transform: "translate(" + cardFinalPositionShape.x + "px, " + cardFinalPositionShape.y + "px)",
                    width: cardFinalPositionShape.width + "px",
                    height: cardFinalPositionShape.height + "px"
                },
            ],
            {
                duration: 1700,
                iterations: 1,
                fill: "forwards" // Reste a la positon final
            }
        ).addEventListener("finish", () => {
            movingCardElement.remove();
            resolve();
        });
    })
}

let animationContainer;
window.addEventListener('load', function () {
    animationContainer = document.getElementById('animationContainer');
    let leaderboardContainer = document.getElementById('leaderboard');
    if (leaderboardContainer) {
        applyScoresStyle(Array.from(leaderboardContainer.children));
    }
});

let timerForReset;
window.onresize = function(){
    clearTimeout(timerForReset);
    timerForReset = setTimeout(resetRankingOrder, 100);
};
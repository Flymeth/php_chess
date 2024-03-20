const cases = document.querySelectorAll("td");
const message = document.getElementById("message");
const loader = document.getElementById("loader");
const session = new URLSearchParams(location.search).get("session");

let selected= undefined;
let currentExpectingPlayer = null;

// Oui cette méthode est shlag mais j'en ai rien à foutre
async function checkNextPlayer() {
    const nowPlaying = await fetch(`/expected_player.php?session=${session}`).then(a => a.text());
    if(currentExpectingPlayer && nowPlaying !== currentExpectingPlayer) return document.location.reload();
    currentExpectingPlayer= nowPlaying
    setTimeout(checkNextPlayer, 1500)
}
checkNextPlayer();

function selectPiece(piece) {
    if(selected) unselectPiece();
    selected = piece
    piece.classList.add("selected")
    document.body.classList.add("has-selected")
}
function unselectPiece() {
    if(!selected) return;
    selected.classList.remove("selected")
    document.body.classList.remove("has-selected")
    selected = undefined
}

document.querySelector("main").addEventListener('click', () => unselectPiece());

cases.forEach((element) => {
    const piece = element.getAttribute("data-piece")
    const caseID = element.getAttribute("data-case")
    const color = element.getAttribute("data-color")

    element.addEventListener("click", async (e) => {
        e.stopPropagation();
        if(selected) {
            if(selected.getAttribute("data-color") === color) unselectPiece()
            else {
                loader.classList.add("active")

                const isValid = await fetch(`/move.php?data=${JSON.stringify({
                    from: selected.getAttribute("data-case"),
                    to: caseID
                })}&session=${session}`).then(r => r.text());
                if(isValid === "true") return document.location.reload()
                else {
                    message.innerText = "Mouvement impossible: " + isValid
                    unselectPiece()
                }
                loader.classList.remove("active")
                return
            }
        }
        
        if(piece && color === currentExpectingPlayer) selectPiece(element)
    })
})

document.getElementById("invite").addEventListener("click", () => {
    navigator.clipboard.writeText(document.location.href);
    alert("Le lien vient d'être copié!");
})

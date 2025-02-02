function randomDelay() {
  return 0;
}
window.addEventListener("load", function () {
  setTimeout(function () {
    document.querySelector(".content").style.display = "block";
    document.getElementById("skeleton-placeholder").style.display = "none";
  }, randomDelay());
});

function showTutorial() {
  if (!localStorage.getItem("filterTutorialSeen")) {
    document.getElementById("tutorialModal").classList.remove("hidden");
  }
}

function closeTutorial() {
  document.getElementById("tutorialModal").classList.add("hidden");
  if (document.getElementById("dontShowAgain").checked) {
    localStorage.setItem("filterTutorialSeen", "true");
  }
}

// ...existing JavaScript code...
